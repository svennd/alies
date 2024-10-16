<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: Breeds
class Breeds extends Vet_Controller
{
	// initialize
	public $breeds, $pets, $logs, $ion_auth;

	// ci specific
	public $input;

    const FREQ_BREED_COUNT = 5;
    const UNKNOWN_BREED = 1;
    const NO_SELECTION = -1;

	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Breeds_model', 'breeds');
		$this->load->model('Pets_model', 'pets');
	}

    /*
    * function: index
    * list all breeds
    */
    public function index($id = false)
	{
		if ($id) {
			$data = array(
                "stats" => $this->breeds->get_breed_stats($id),
                "breed" => $this->breeds->with_pets('fields:*count*')->get($id),
				"pets" => $this->pets
									->fields('id, name, death')
									->with_owners('fields:id, last_name, street, city')
									->where(array("breed" => (int)$id, "death" => 0, "lost" => 0))
									->get_all(),
			);
			$this->_render_page('breeds/detail', $data);
		} else {			
			$data = array(
							// don't add dead / lost (it will filter the breeds)
							"breeds" => $this->breeds->with_pets('fields:*count*')->get_all(),
						);

			$this->_render_page('breeds/index', $data);
		}
	}

    /*
    * function: add
    * add a new breed
    */
    public function add()
    {
        if ($this->input->post('submit')) 
        {
            $result = $this->breeds->insert(array(
                    "name" => $this->input->post('name'),
                    "type" => $this->input->post('type'),
                    "freq" => 0,
                    "male_min_weight" => $this->input->post('male_min_weight'),
                    "male_max_weight" => $this->input->post('male_max_weight'),
                    "female_min_weight" => $this->input->post('female_min_weight'),
                    "female_max_weight" => $this->input->post('female_max_weight')
                ));
             
			$this->logs->logger(INFO, "add_breed", $this->input->post('name'));
            if($result)
            {
                redirect('breeds');
            }
        }

        $this->_render_page('breeds/add', array());

    }

    /*
    * function: edit
    * edit a breed
    */
    public function edit(int $id)
    {
		# only admins have access here
		if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }

        $update = false;
        if ($this->input->post('submit')) 
        {
            # log
            $this->logs->logger(DEBUG, "edit_breed", $this->input->post('name'));

            $update = $this->breeds->update(array(
                                            "name" => $this->input->post('name'),
                                            "type" => $this->input->post('type'),
                                            // "freq" => $this->input->post('freq'),
                                            "male_min_weight" => $this->input->post('male_min_weight'),
                                            "male_max_weight" => $this->input->post('male_max_weight'),
                                            "female_min_weight" => $this->input->post('female_min_weight'),
                                            "female_max_weight" => $this->input->post('female_max_weight'),
                                            ), $id);
        }

        $current_breed = $this->breeds
                                    ->with_pets('fields:*count*', 'where:`death`=\'0\' and `lost`=\'0\'')
                                    ->get($id);
        $data = array(
                    'breed'     => $current_breed,
                    'id'        => $id,
                    'breeds'    => $this->get_breeds($current_breed['type'], true),
                    'update'    => $update
                );
        

        $this->_render_page('breeds/edit', $data);
    }

    /*
    * function: search_breed
    * search for breed type (ajax)
    *
    * see also : add_pet and edit_pet
    */
    public function search_breed(string $query = "")
    {
        $where_type = (null !== ($this->input->get('type'))) ? array('type' => (int) $this->input->get('type') ) : array('1' => '1');

        $obj = $this->breeds
                    ->fields('id, name');

        if (!empty($query))
        {
            $obj = $this->breeds
                    ->fields('id, name')
                    ->where('name', 'like', $query, true);
        }

        $results = $obj
            ->where($where_type)
            ->limit(50)
            ->order_by('freq', 'DESC')
            ->get_all();

        if (!$results) { return 0; }
        
        $return = array();
        foreach ($results as $r) {
        	$return[] = array(
        				"id"    => $r['id'],
        				"text"  => $r['name']
        				);
        }
        echo json_encode(array("results" => $return));
    }

    /*
    * function: rebuild_frequenty
    * frequenty is used to sort breeds, this might change over years
    */
    public function rebuild_frequenty()
    {
        $pets = $this->pets
            ->fields('birth, type, breed')
            ->where('birth', '>', (date("Y")-5) . '-01-01')
            ->where(array('lost' => 0, 'death' => 0))
            ->with_breeds('fields:name')
            ->get_all();
        
        $count = array();
        $count_int = array();
        foreach ($pets as $pet)
        {
            $type = $pet['type'];
            $breed_int = $pet['breed'];
            if (!isset($pet['breeds'])) { continue; }
            $breed = $pet['breeds']['name'];
            if (isset($count[$type]) && isset($count[$type][$breed]))
            {
                $count[$type][$breed] += 1;
            }
            else
            {
                $count[$type][$breed] = 1;
            }

            # ints for rebuild
            if (isset($count_int[$breed_int]))
            {
                $count_int[$breed_int] += 1;
            }
            else
            {
                $count_int[$breed_int] = 1;
            }
        }

        # reset freq
        $this->breeds->update(array('freq' => 0));

        foreach ($count_int as $breed => $cnt) 
        {
            # cuttoff is 5 && don't set unknown (1)
            if ($cnt > self::FREQ_BREED_COUNT && $breed != self::UNKNOWN_BREED)
            {
               $this->breeds->where(array('id' => $breed))->update(array('freq' => $cnt));
            }
        }

        $data = array('count' => $count);

        $this->logs->logger(INFO, "rebuild_freq", '');
		$this->_render_page('breeds/rebuild_freq', $data);
    }

    /*
    * function: merge
    * merge two breeds
    */
    public function merge(int $old_breed)
    {
		# only admins have access here
		if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }

        # sanity check
        $new_merged_breed = (int) $this->input->post('new_breed');
        if($old_breed == $new_merged_breed) {
            echo "You are trying to merge the same breed ...";
            return false;
        }

        # update all pets with this breed to new breed
        $this->pets->update(
            array(
                                "breed" => $new_merged_breed
                            ),
            array(
                                "breed" => $old_breed
                            )
        );

        # delete this breed
        $this->breeds->delete($old_breed);

        # log
        $this->logs->logger(INFO, "merge_breeds", "new breed : " . $new_merged_breed . " - old breed :" . $old_breed);
        redirect('breeds');
    }

    /*
    * function: get_breeds
    * get breeds based on type, ajax
    */
    private function get_breeds(int $type = self::NO_SELECTION, bool $array = false)
    {
        # no type selected
        if ($type == self::NO_SELECTION) 
        {
            $breeds = $this->breeds->get_all();
        }
        else
        {
            $breeds = $this->breeds->where(array('type' => $type))->get_all();
        }

        $result = array();
        foreach ($breeds as $breed)
        {
            $result[] = array($breed['id'], $breed['name'], $breed['type']);
        }

        if ($array)
        {
            return $breeds;
        }
        echo json_encode(array('data' => $result));
    }
}