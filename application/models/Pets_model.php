<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Pets_model extends MY_Model
{
	public $table = 'pets';
	public $primary_key = 'id';
	private $transfer_error = '';
	
	public function __construct()
	{
		// enable soft deletes
		$this->soft_deletes = true;

		/*
			has_one
		*/
		$this->has_one['owners'] = array(
							'foreign_model' => 'Owners_model',
							'foreign_table' => 'owners',
							'foreign_key' => 'id',
							'local_key' => 'owner'
						);
						
		$this->has_one['breeds'] = array(
							'foreign_model' => 'Breeds_model',
							'foreign_table' => 'breeds',
							'foreign_key' => 'id',
							'local_key' => 'breed'
						);
						
		$this->has_one['breeds2'] = array(
							'foreign_model' => 'Breeds_model',
							'foreign_table' => 'breeds',
							'foreign_key' => 'id',
							'local_key' => 'breed2'
						);
						
		/*
			has_many
		*/
		$this->has_many['pets_weight'] = array(
							'foreign_model' => 'Pets_weight_model',
							'foreign_table' => 'Pets_weight',
							'foreign_key' => 'pets',
							'local_key' => 'id'
						);
						
		$this->has_many['vacs'] = array(
							'foreign_model' => 'Vaccine_pet_model',
							'foreign_table' => 'vaccine_pet',
							'foreign_key' => 'pet',
							'local_key' => 'id'
						);
						
		$this->has_many['tooths'] = array(
							'foreign_model' => 'Tooth_model',
							'foreign_table' => 'tooth',
							'foreign_key' => 'pet',
							'local_key' => 'id'
						);
		parent::__construct();
	}
	
	# used in search + search pet in lab
	public function search_by_name($query, int $limit = 250, bool $dead_allowed = false)
	{
		$query = $this->db->escape_like_str($query);
		$sql = "
			SELECT 
				pets.id as pet_id, pets.name, pets.type as type, owners.*
			FROM 
				pets
			LEFT JOIN
				owners
			ON
				owners.id = pets.owner
			WHERE
				name LIKE '" . $query . "%' ESCAPE '!'
				" . ($dead_allowed ? "" : "AND pets.death = 0") . "
                AND pets.transfered = 0
			ORDER BY
				owners.last_bill
			DESC
			LIMIT " . $limit . "
		";
		
		return $this->db->query($sql)->result_array();
	}
	
	public function search_by_chip_ex($chip)
	{
		$sql = "
			SELECT 
				pets.name, pets.type as type, owners.*
			FROM 
				pets
			LEFT JOIN
				owners
			ON
				owners.id = pets.owner
			WHERE
				chip LIKE '" . $this->db->escape_like_str($chip) . "%' ESCAPE '!'
			ORDER BY
				owners.last_bill
			DESC
			LIMIT 5
		";
		
		return $this->db->query($sql)->result_array();
	}
	
	public function search_by_id(int $id)
	{
		$sql = "
			SELECT 
				pets.name, pets.type as type, owners.*
			FROM 
				pets
			LEFT JOIN
				owners
			ON
				owners.id = pets.owner
			WHERE
				pets.id = '" . $id . "'
                AND pets.transfered = 0
			ORDER BY
				owners.last_bill
			DESC
			LIMIT 5
		";
		
		return $this->db->query($sql)->result_array();
	}
		
	public function get_per_type()
	{
		$pets_sql = "
			select 
				type,
				count(id) as amount
			from 
				pets
			where
				death = 0
                AND pets.transfered = 0
			group by
				type			
		";
		return $this->db->query($pets_sql)->result_array();
	}

	// owners
	public function get_all_pets(int $owner)
	{
		return $this
                    ->with_breeds('field:name')
                    ->with_breeds2('field:name')
                    ->where(array("owner" => $owner, 'transfered' => 0))
                    ->order_by(array("birth, death"), "desc")
                    ->get_all();
	}

	/*
		generate a list of pets of the owner
		that isn't the current_pet
	*/
	public function other_pets(int $owner, int $pet_id, int $limit = 5)
	{
		return $this->where(array('owner' => $owner, 'death' => 0, 'lost' => 0, 'transfered' => 0))->where('id !=', $pet_id)->fields('id, type, name')->limit($limit)->get_all();
	}

	public function replace_avatar(int $pet_id, $avatar)
	{
		$this->db->trans_start();
		$pet = $this->db
			->query('SELECT `avatar` FROM `pets` WHERE `id` = ? FOR UPDATE', array($pet_id))
			->row_array();

		if ($pet) {
			$this->db
				->set('avatar', $avatar)
				->where('id', $pet_id)
				->update('pets');
		}

		$this->db->trans_complete();
		if (!$pet || $this->db->trans_status() === false) {
			return false;
		}

		return array('previous' => $pet['avatar']);
	}

	public function avatar_reference_count(string $avatar): int
	{
		return (int) $this->db
			->where('avatar', $avatar)
			->count_all_results('pets');
	}


	/*
		cli cron job
	*/
	public function auto_death(int $type, int $years)
	{
		$sql = "
			UPDATE pets
			SET death = 1,
				death_date = DATE_SUB(CURDATE(), INTERVAL 14 DAY), -- killed withouth a trace :)
				note = CONCAT(note, ' [auto-death]')
			WHERE 
				death = 0
			AND
				type = " . $type . "
			AND TIMESTAMPDIFF(YEAR, birth, CURDATE()) > " . $years . "
			AND id NOT IN (
				SELECT DISTINCT pet
				FROM events
				WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
			);
		";
		$this->db->query($sql);
		return $this->db->affected_rows();
	}

    /*
    * function: transfer_pet
    * transfer a pet to a new owner
    * we have to create a hidden pet in order to make the 
    */
    public function transfer_pet(int $pet_id, int $new_owner_id)
    {
        $this->transfer_error = '';

        if (!$this->db->trans_begin()) {
            return $this->transfer_failed('Could not start the pet transfer transaction.');
        }

        try {
            $pet = $this->lock_transfer_source($pet_id);
            $owner = $this->lock_transfer_owner($new_owner_id);

            if (!$pet) {
                throw new RuntimeException('The source pet is missing or has already been transferred.');
            }
            if (!$owner || (int) $owner['disabled'] === 1) {
                throw new RuntimeException('The target owner is missing or disabled.');
            }
            if ((int) $pet['owner'] === $new_owner_id) {
                throw new RuntimeException('The target owner already owns this pet.');
            }

            $new_pet_id = $this->clone_pet($pet, $new_owner_id);
            if (!$new_pet_id) {
                throw new RuntimeException('The successor pet could not be created.');
            }
            $this->after_transfer_step('successor_created');

            $this->transfer_existing_medical_records($pet_id, (int) $new_pet_id);
            $this->after_transfer_step('medical_records_transferred');

            $hidden = $this->db
                ->where('id', $pet_id)
                ->where('transfered', 0)
                ->update('pets', array(
                    'transfered' => 1,
                    'note' => '[transfer:send:' . $new_owner_id . ']',
                    'chip' => null,
                    'companion' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ));

            if (!$hidden || $this->db->affected_rows() !== 1) {
                throw new RuntimeException('The source pet could not be hidden.');
            }
            $this->after_transfer_step('source_hidden');

            if (!$this->db->trans_commit()) {
                throw new RuntimeException('The pet transfer could not be committed.');
            }

            return (int) $new_pet_id;
        } catch (Throwable $exception) {
            $this->db->trans_rollback();
            return $this->transfer_failed($exception->getMessage());
        }
    }

    public function get_transfer_error(): string
    {
        return $this->transfer_error;
    }

    public function format_transfer_history(string $report, array $products, array $procedures): string
    {
        $sections = array();
        $product_lines = $this->format_transfer_items($products);
        $procedure_lines = $this->format_transfer_items($procedures);

        if ($product_lines) {
            $sections[] = '<h4>Overgenomen producten</h4><ul>' . implode('', $product_lines) . '</ul>';
        }
        if ($procedure_lines) {
            $sections[] = '<h4>Overgenomen procedures</h4><ul>' . implode('', $procedure_lines) . '</ul>';
        }
        if (!$sections) {
            return $report;
        }

        $separator = trim($report) === '' ? '' : '<hr>';
        return $report . $separator . implode('', $sections);
    }

    public function transfer_existing_medical_records(int $source_pet_id, int $successor_pet_id): array
    {
        $this->assert_no_dental_conflicts($source_pet_id, $successor_pet_id);
        $history = $this->prepare_transfer_history($source_pet_id);

        $links = array(
            'vaccine_pet' => 'pet',
            'pets_weight' => 'pets',
            'tooth' => 'pet',
            'tooth_msg' => 'pet',
            'rx' => 'pet_id',
            'lab' => 'pet',
            'lab_report' => 'pet_id',
        );

        foreach ($links as $table => $column) {
            $updated = $this->db
                ->where($column, $source_pet_id)
                ->update($table, array($column => $successor_pet_id));
            if (!$updated) {
                throw new RuntimeException('Could not move records from ' . $table . '.');
            }
        }

        $event_map = $this->persist_transfer_history($history, $successor_pet_id);
        return array(
            'source_pet_id' => $source_pet_id,
            'successor_pet_id' => $successor_pet_id,
            'history_events' => count($event_map),
        );
    }

    public function resolve_historical_transfer_pairs()
    {
        $sources = $this->db
            ->where('transfered', 1)
            ->where('deleted_at IS NULL', null, false)
            ->where("note REGEXP '^\\\\[transfer:send:[0-9]+\\\\]$'", null, false)
            ->order_by('id', 'ASC')
            ->get('pets')
            ->result_array();
        $pairs = array();

        foreach ($sources as $source) {
            if (!preg_match('/^\[transfer:send:(\d+)\]$/', (string) $source['note'], $match)) {
                return $this->transfer_failed('Invalid transfer marker for source pet #' . $source['id'] . '.');
            }

            $target_owner_id = (int) $match[1];
            $this->db
                ->select('id')
                ->from('pets')
                ->where('id !=', (int) $source['id'])
                ->where('owner', $target_owner_id)
                ->where('name', $source['name'])
                ->where('type', $source['type'])
                ->where('deleted_at IS NULL', null, false)
                ->where('ABS(TIMESTAMPDIFF(SECOND, created_at, ' . $this->db->escape($source['updated_at']) . ')) <= 10', null, false);

            if ($source['birth'] === null) {
                $this->db->where('birth IS NULL', null, false);
            } else {
                $this->db->where('birth', $source['birth']);
            }

            $candidates = $this->db->get()->result_array();
            if (count($candidates) !== 1) {
                return $this->transfer_failed(
                    'Source pet #' . $source['id'] . ' has ' . count($candidates) . ' successor candidates.'
                );
            }

            $pair = array(
                'source_pet_id' => (int) $source['id'],
                'successor_pet_id' => (int) $candidates[0]['id'],
            );
            try {
                $this->assert_no_dental_conflicts($pair['source_pet_id'], $pair['successor_pet_id']);
            } catch (Throwable $exception) {
                return $this->transfer_failed($exception->getMessage());
            }
            $pairs[] = $pair;
        }

        return $pairs;
    }

    public function backfill_transferred_pets()
    {
        $this->transfer_error = '';
        $pairs = $this->resolve_historical_transfer_pairs();
        if ($pairs === false) {
            return false;
        }
        if (!$this->db->trans_begin()) {
            return $this->transfer_failed('Could not start the transfer backfill transaction.');
        }

        try {
            $results = array();
            foreach ($pairs as $pair) {
                $results[] = $this->transfer_existing_medical_records(
                    $pair['source_pet_id'],
                    $pair['successor_pet_id']
                );
                $this->after_transfer_step('backfill_pair_complete');
            }

            if (!$this->db->trans_commit()) {
                throw new RuntimeException('The transfer backfill could not be committed.');
            }
            return $results;
        } catch (Throwable $exception) {
            $this->db->trans_rollback();
            return $this->transfer_failed($exception->getMessage());
        }
    }


    /*
    * function: duplicate_chips
    * find duplicate chips in pets - should not happen
    */
    public function duplicate_chips()
    {
        $sql = "
            SELECT 
                GROUP_CONCAT(pets.id ORDER BY last_bill DESC) AS pet_ids,
                COUNT(*) AS cnt,
                chip,
                max(owners.last_bill) AS max_last_bill,
                pets.*,
                owners.*
            FROM pets
            LEFT JOIN owners ON owners.id = pets.owner
            WHERE
                pets.transfered = 0
                AND pets.chip IS NOT NULL
                AND pets.chip <> ''
            GROUP BY 
                chip
            HAVING cnt > 1
            ORDER BY last_bill DESC;
            ;
        ";
        $query = $this->db->query($sql);

        return $query->result_array();
    }

    /*
    * function: does_pet_exist
    * check if pet exists by id - used in lab result ingestion
    */
    public function does_pet_exist(int $pet_id)
    {
        $pet = $this->where(array('id' => $pet_id))->get();

        if ($pet) {
            return $pet['id'];
        }

        return null;
    }

    /*
    * function: findByOwnerPhoneAndPet
    * find pets by owner phone and owner name + pet name
    * used in lab result ingestion
    */
    public function findByOwnerPhoneAndPet(string $phone, string $owner, string $pet)
    {
        $this->db->select('
                        pets.id        AS pet_id,
                        owners.id      AS owner_id,
                        pets.name      AS pet_name,
                        owners.last_name
                    ');
        $this->db->join('owners', 'owners.id = pets.owner');
        $this->db->group_start();
            $this->db
                    ->or_where('owners.telephone', $phone)
                    ->or_where('owners.mobile', $phone)
                    ->or_where('owners.phone2', $phone)
                    ->or_where('owners.phone3', $phone);
        $this->db->group_end();

        $this->db->where('owners.last_name', strtoupper($owner));
        $this->db->where('pets.name', strtoupper($pet));

        return $this->db->get('pets')->result();
    }
    
    /*
    * function: findByOwnerAndPet
    * find pets by owner name + pet name
    * used in lab result ingestion
    */
    public function findByOwnerAndPet(string $owner, string $pet)
    {
        $sql = "
            SELECT 
                pets.id        AS pet_id,
                owners.id      AS owner_id,
                pets.name      AS pet_name,
                owners.last_name
            FROM 
                pets
            JOIN
                owners
            ON
                owners.id = pets.owner
            WHERE
                owners.last_name = ?
            AND
                pets.name = ?
        ";
        $query = $this->db->query($sql, array(strtoupper($owner), strtoupper($pet)));
  
        return $query->result();
    }

    /*
    * function: findByChipNumber
    * find pets by chip number
    * used in lab result ingestion
    */
    public function findByChipNumber(string $chip)
    {
        return $this->where('chip', $chip)->get_all();
    }

    public function findByPetName(string $pet_name)
    {
        # more then 1 but limit it so we don't return too much
        return $this->where('name', $pet_name)->limit(3)->get_all();
    }

    protected function clone_pet(array $pet, int $new_owner_id)
    {
        unset($pet['id']);
        $pet['owner'] = $new_owner_id;
        $pet['note'] = rtrim((string) $pet['note']) . ' [transfer:owner:' . $new_owner_id . ']';

        $new_pet_id = $this->insert($pet);
        return $new_pet_id ? (int) $new_pet_id : false;
    }

    protected function after_transfer_step(string $step): void
    {
        // Extension point for focused failure-path tests.
    }

    private function lock_transfer_source(int $pet_id)
    {
        return $this->db->query(
            'SELECT * FROM `pets` WHERE `id` = ? AND `transfered` = 0 AND `deleted_at` IS NULL FOR UPDATE',
            array($pet_id)
        )->row_array();
    }

    private function lock_transfer_owner(int $owner_id)
    {
        return $this->db->query(
            'SELECT `id`, `disabled` FROM `owners` WHERE `id` = ? AND `deleted_at` IS NULL FOR UPDATE',
            array($owner_id)
        )->row_array();
    }

    private function prepare_transfer_history(int $source_pet_id): array
    {
        $events = $this->db
            ->where('pet', $source_pet_id)
            ->where('no_history', 0)
            ->order_by('id', 'ASC')
            ->get('events')
            ->result_array();
        $prepared = array();

        foreach ($events as $event) {
            $products = $this->db
                ->select('ep.volume, COALESCE(p.name, CONCAT("Product #", ep.product_id)) AS name, p.unit_sell AS unit', false)
                ->from('events_products ep')
                ->join('products p', 'p.id = ep.product_id', 'left')
                ->where('ep.event_id', (int) $event['id'])
                ->order_by('ep.id', 'ASC')
                ->get()
                ->result_array();
            $procedures = $this->db
                ->select('epr.volume, COALESCE(pr.name, CONCAT("Procedure #", epr.procedures_id)) AS name, NULL AS unit', false)
                ->from('events_procedures epr')
                ->join('procedures pr', 'pr.id = epr.procedures_id', 'left')
                ->where('epr.event_id', (int) $event['id'])
                ->order_by('epr.id', 'ASC')
                ->get()
                ->result_array();
            $labs = $this->db
                ->select('el.lab_id')
                ->from('events_labs el')
                ->join('lab_report lr', 'lr.id = el.lab_id', 'inner')
                ->where('el.event_id', (int) $event['id'])
                ->where('lr.pet_id', $source_pet_id)
                ->get()
                ->result_array();

            $prepared[] = array(
                'event' => $event,
                'report' => $this->format_transfer_history(
                    (string) $event['anamnese'],
                    $products,
                    $procedures
                ),
                'lab_ids' => array_map('intval', array_column($labs, 'lab_id')),
            );
        }

        return $prepared;
    }

    private function persist_transfer_history(array $history, int $successor_pet_id): array
    {
        $event_map = array();

        foreach ($history as $item) {
            $event = $item['event'];
            $inserted = $this->db->insert('events', array(
                'title' => $event['title'],
                'anamnese' => $item['report'],
                'pet' => $successor_pet_id,
                'type' => $event['type'],
                'status' => STATUS_HISTORY,
                'payment' => BILL_INVALID,
                'location' => $event['location'],
                'vet' => $event['vet'],
                'vet_support_1' => $event['vet_support_1'],
                'vet_support_2' => $event['vet_support_2'],
                'report' => REPORT_DONE,
                'no_history' => 0,
                'created_at' => $event['created_at'],
                'updated_at' => $event['updated_at'],
            ));
            if (!$inserted) {
                throw new RuntimeException('Could not copy medical history event #' . $event['id'] . '.');
            }

            $new_event_id = (int) $this->db->insert_id();
            $event_map[(int) $event['id']] = $new_event_id;
            foreach ($item['lab_ids'] as $lab_id) {
                $linked = $this->db->query(
                    'INSERT IGNORE INTO `events_labs` (`event_id`, `lab_id`) VALUES (?, ?)',
                    array($new_event_id, $lab_id)
                );
                if (!$linked) {
                    throw new RuntimeException('Could not relink lab report #' . $lab_id . '.');
                }
            }
        }

        return $event_map;
    }

    private function format_transfer_items(array $items): array
    {
        $lines = array();
        foreach ($items as $item) {
            $name = htmlspecialchars((string) $item['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $quantity = $this->format_transfer_quantity($item['volume']);
            $unit = isset($item['unit']) ? trim((string) $item['unit']) : '';
            $display = $quantity . ($unit === '' ? '' : ' ' . htmlspecialchars($unit, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            $lines[] = '<li><strong>' . $name . '</strong>: ' . $display . '</li>';
        }
        return $lines;
    }

    private function format_transfer_quantity($quantity): string
    {
        $formatted = number_format((float) $quantity, 2, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');
        return $formatted === '-0' || $formatted === '' ? '0' : $formatted;
    }

    private function assert_no_dental_conflicts(int $source_pet_id, int $successor_pet_id): void
    {
        $conflict = $this->db
            ->select('source_tooth.id')
            ->from('tooth source_tooth')
            ->join(
                'tooth successor_tooth',
                'successor_tooth.pet = ' . (int) $successor_pet_id . ' AND successor_tooth.tooth = source_tooth.tooth',
                'inner',
                false
            )
            ->where('source_tooth.pet', $source_pet_id)
            ->limit(1)
            ->get()
            ->row_array();

        if ($conflict) {
            throw new RuntimeException(
                'Dental chart conflict while transferring pet #' . $source_pet_id . ' to pet #' . $successor_pet_id . '.'
            );
        }
    }

    private function transfer_failed(string $message)
    {
        $this->transfer_error = $message;
        log_message('error', 'Pet transfer failed: ' . $message);
        return false;
    }
}
