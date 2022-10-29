<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Breeds extends Vet_Controller
{
	# constructor
	public function __construct()
	{
		parent::__construct();

		# models
		$this->load->model('Breeds_model', 'breeds');
		$this->load->model('Pets_model', 'pets');
	}

    # search for types
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

    # frequenty is used to sort breeds, this might change over years
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
            if ($cnt > 5 && $breed != 1)
            {
               $this->breeds->where(array('id' => $breed))->update(array('freq' => $cnt));
            }
        }

        $data = array('count' => $count);
		$this->_render_page('breeds/rebuild_freq', $data);
    }

    # debug
    # fix import error
    public function run_re_import()
    {
		$this->load->model('Pets_model', 'pets');
        $all_breeds = $this->breeds->get_all();
        $name_array = array();
        foreach ($all_breeds as $breed)
        {
            $name_array[strtolower($breed['name'])] = $breed['id'];
        }

        # loop through pets
        $all_pets = $this->pets->fields('id, type, breed, note')->where('note', '!=', '')->get_all();

        $name_type = array();
        $not_found = array();
        $count_found = 0;
        foreach ($all_pets as $pet)
        {
            $breed_name = strtolower(trim(substr($pet['note'], 14)));
            if(!isset($name_array[$breed_name])) { 
                $not_found[$breed_name] = (isset($not_found[$breed_name])) ? $not_found[$breed_name]+1: 1;
                continue; 
            }

            $this->pets->where(array('id' => $pet['id']))->update(array('breed' => $name_array[$breed_name], 'note' => ''));
            $count_found++;
            $name_type[$breed_name] = $pet['type'];
        }

        # roughly detect the type based on the last pet type
        foreach ($name_type as $name => $type)
        {
            $this->breeds->where(array('name' => $name))->update(array('type' => $type));
        }
        echo "done found : " . $count_found;
 
    }

    # debug 
    # try to guess the type based on the database
    public function run_guess_breed_type()
    {
        $breeds = $this->breeds->fields('id')->get_all();

        $found = 0;
        foreach ($breeds as $breed)
        {
            $pet = $this->pets->where(array('breed' => $breed['id']))->limit(1)->get();
            # if there is a pet
            if ($pet)
            {
               $this->breeds->where(array('id' => (int) $breed['id']))->update(array('type' => (int) $pet['type']));
               $found++;
            }
        }
        echo "guessed $found types of breeds, of the total ". count($breeds) . " in the database.";
    }
}