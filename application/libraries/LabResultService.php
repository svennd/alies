<?php

// application/libraries/LabResultService.php
class LabResultService
{
    protected $CI;
    public $canonical_map;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('LabResult_model');
        $this->CI->load->model('LabReport_model');
        $this->CI->load->model('LabPlots_model', 'lab_plots');
        $this->CI->load->model('LabReportPending_model');
        $this->CI->load->model('Pets_model');
		$this->CI->load->model('Owners_model');
        $this->CI->load->library('lab_device_adapter_factory');
        $this->CI->load->library('lab_legacy_pending_mapper');
		$this->CI->load->library('lab_source_identity');

        $this->CI->config->load('lab/canonical', true);
        $this->canonical_map = (array) $this->CI->config->item('canonical', 'lab/canonical');
    }

    public function ingest(DeviceAdapterInterface $adapter, array $payload)
    {
        $data = $adapter->parse($payload);
		$identity = $this->CI->lab_source_identity->derive(
			$data['device'] ?? null,
			$data['source'] ?? null,
			$data['source_id'] ?? null
		);
		$identity_candidates = $this->CI->lab_source_identity->candidates(
			$data['device'] ?? null,
			$data['source'] ?? null,
			$data['source_id'] ?? null
		);
		$lock_names = $this->acquire_identity_locks(array_column($identity_candidates, 'hash'));

		$this->CI->db->trans_begin();
        try {
			$existing = $this->CI->LabReport_model->findBySource(
				$data['device'] ?? null,
				$data['source'] ?? null,
				$data['source_id'] ?? null,
				true
			);
			if ($existing) {
				$pet_id = (int) $existing->pet_id;
				$report_id = $this->persist_report($pet_id, $data);
				$this->resolve_pending_identity($identity, $report_id, $pet_id);
				$this->commit_ingestion();
				return array('status' => 'ok');
			}

			$pet_id = $this->resolvePetId($data);
			if ($pet_id === null) {
				$this->CI->LabReportPending_model->create_or_refresh(array(
					'device' => $data['device'] ?? null,
					'source' => $data['source'] ?? null,
					'source_id' => $data['source_id'] ?? null,
					'raw_payload' => json_encode($payload),
					'identifiers' => json_encode(array(
						'pet_id' => $data['pet_id'] ?? null,
						'owner_name' => $data['owner_name'] ?? null,
						'pet_name' => $data['pet_name'] ?? null,
						'chip' => $data['chip'] ?? null,
						'phone' => $data['phone'] ?? null,
					)),
					'reason' => 'pet_not_found',
				));

				$existing = $this->CI->LabReport_model->findBySource(
					$data['device'] ?? null,
					$data['source'] ?? null,
					$data['source_id'] ?? null,
					true
				);
				if ($existing) {
					$pet_id = (int) $existing->pet_id;
					$report_id = $this->persist_report($pet_id, $data);
					$this->resolve_pending_identity($identity, $report_id, $pet_id);
					$this->commit_ingestion();
					return array('status' => 'ok');
				}

				$this->commit_ingestion();
				return array('status' => 'pending');
			}

			$report_id = $this->persist_report((int) $pet_id, $data);
			$this->resolve_pending_identity($identity, $report_id, (int) $pet_id);
            if ($this->CI->db->trans_status() === false) {
                throw new RuntimeException('Lab report persistence failed.');
            }
            $this->CI->db->trans_commit();
        } catch (Throwable $error) {
            $this->CI->db->trans_rollback();
            throw $error;
		} finally {
			$this->release_identity_locks($lock_names);
        }

        return array('status' => 'ok');
    }

    public function recover_pending(int $pending_id, int $owner_id, int $pet_id, int $user_id): array
    {
        $this->CI->db->trans_begin();
        try {
            $pending = $this->CI->LabReportPending_model->lock_active($pending_id);
            if (!$pending) {
                throw new DomainException('This unmatched result is no longer active.');
            }
			if (!$this->CI->Owners_model->is_assignable($owner_id)) {
				throw new DomainException('Select a valid owner before recovering this result.');
			}
			if (!$this->CI->Pets_model->is_assignable_to_owner($pet_id, $owner_id)) {
				throw new DomainException('Select a valid pet belonging to the selected owner.');
			}

            $payload = json_decode((string) $pending['raw_payload'], true, 512, JSON_THROW_ON_ERROR);
            if (($pending['reason'] ?? null) === 'legacy_missing_pet') {
                $data = $this->CI->lab_legacy_pending_mapper->map($payload);
            } else {
                $adapter = $this->CI->lab_device_adapter_factory->create($pending['device'] ?? null);
                if (!$adapter) {
                    throw new UnexpectedValueException('This lab device is not supported.');
                }
                $data = $adapter->parse($payload);
            }

            $data['device'] = $data['device'] ?? $pending['device'];
            $data['source'] = $data['source'] ?? $pending['source'];
            $data['source_id'] = $data['source_id'] ?? $pending['source_id'];
            $report_id = $this->persist_report($pet_id, $data);

            if (!$this->CI->LabReportPending_model->mark_resolved($pending_id, $report_id, $pet_id, $user_id)) {
                throw new RuntimeException('Could not mark the pending result as resolved.');
            }
            if ($this->CI->db->trans_status() === false) {
                throw new RuntimeException('Pending lab recovery failed.');
            }

            $this->CI->db->trans_commit();
			return array('status' => 'ok', 'report_id' => $report_id, 'owner_id' => $owner_id, 'pet_id' => $pet_id);
        } catch (DomainException $error) {
            $this->CI->db->trans_rollback();
            return array('status' => 'error', 'message' => $error->getMessage());
        } catch (Throwable $error) {
            $this->CI->db->trans_rollback();
            log_message('error', 'Pending lab recovery failed for #' . $pending_id . ': ' . $error->getMessage());
            return array('status' => 'error', 'message' => 'The lab result could not be recovered. It remains in the unmatched queue.');
        }
    }

	public function reassign_report(int $report_id, int $owner_id, int $pet_id): array
	{
		$this->CI->db->trans_begin();
		try {
			$report = $this->CI->db->query(
				'SELECT * FROM `lab_report` WHERE `id` = ? AND `deleted_at` IS NULL FOR UPDATE',
				array($report_id)
			)->row_array();
			if (!$report) {
				throw new DomainException('This lab report no longer exists.');
			}
			if (!$this->CI->Owners_model->is_assignable($owner_id)) {
				throw new DomainException('Select a valid owner before changing this report.');
			}
			if (!$this->CI->Pets_model->is_assignable_to_owner($pet_id, $owner_id)) {
				throw new DomainException('Select a valid pet belonging to the selected owner.');
			}

			$old_pet_id = (int) $report['pet_id'];
			$old_pet = $this->CI->db->select('owner')->where('id', $old_pet_id)->get('pets')->row_array();
			$old_owner_id = $old_pet ? (int) $old_pet['owner'] : 0;
			if ($old_pet_id === $pet_id && $old_owner_id === $owner_id) {
				$this->CI->db->trans_rollback();
				return array(
					'status' => 'noop',
					'message' => 'This lab report is already assigned to that patient.',
					'old_pet_id' => $old_pet_id,
					'old_owner_id' => $old_owner_id,
					'pet_id' => $pet_id,
					'owner_id' => $owner_id,
					'removed_event_links' => 0,
				);
			}

			$this->CI->db->query(
				'DELETE `events_labs` FROM `events_labs` INNER JOIN `events` ON `events`.`id` = `events_labs`.`event_id` WHERE `events_labs`.`lab_id` = ? AND `events`.`pet` = ?',
				array($report_id, $old_pet_id)
			);
			$removed_event_links = $this->CI->db->affected_rows();

			if (!$this->CI->db->where('id', $report_id)->update('lab_report', array(
				'pet_id' => $pet_id,
				'updated_at' => date('Y-m-d H:i:s'),
			))) {
				throw new RuntimeException('Could not update the lab report patient.');
			}
			if ($this->CI->db->trans_status() === false) {
				throw new RuntimeException('Lab report reassignment failed.');
			}

			$this->CI->db->trans_commit();
			return array(
				'status' => 'ok',
				'old_pet_id' => $old_pet_id,
				'old_owner_id' => $old_owner_id,
				'pet_id' => $pet_id,
				'owner_id' => $owner_id,
				'removed_event_links' => $removed_event_links,
			);
		} catch (DomainException $error) {
			$this->CI->db->trans_rollback();
			return array('status' => 'error', 'message' => $error->getMessage());
		} catch (Throwable $error) {
			$this->CI->db->trans_rollback();
			log_message('error', 'Lab report reassignment failed for #' . $report_id . ': ' . $error->getMessage());
			return array('status' => 'error', 'message' => 'The lab report assignment could not be changed.');
		}
	}

    public function soft_delete_pending(int $pending_id, int $user_id): array
    {
        $this->CI->db->trans_begin();
        try {
            if (!$this->CI->LabReportPending_model->lock_active($pending_id)) {
                throw new DomainException('This unmatched result is no longer active.');
            }
            if (!$this->CI->LabReportPending_model->soft_delete_active($pending_id, $user_id)) {
                throw new RuntimeException('Could not delete the pending result.');
            }
            if ($this->CI->db->trans_status() === false) {
                throw new RuntimeException('Pending lab deletion failed.');
            }
            $this->CI->db->trans_commit();
            return array('status' => 'ok');
        } catch (DomainException $error) {
            $this->CI->db->trans_rollback();
            return array('status' => 'error', 'message' => $error->getMessage());
        } catch (Throwable $error) {
            $this->CI->db->trans_rollback();
            log_message('error', 'Pending lab deletion failed for #' . $pending_id . ': ' . $error->getMessage());
            return array('status' => 'error', 'message' => 'The unmatched lab result could not be deleted.');
        }
    }

    private function persist_report(int $pet_id, array $data): int
    {
        if (!isset($data['results']) || !is_array($data['results'])) {
            throw new UnexpectedValueException('Lab payload has no result collection.');
        }

        $existing = $this->CI->LabReport_model->findBySource(
            $data['device'] ?? null,
            $data['source'] ?? null,
            $data['source_id'] ?? null
        );

        if ($existing) {
            if ((int) $existing->pet_id !== $pet_id) {
                throw new DomainException('A report with this source identity already belongs to another pet.');
            }
            $report_id = (int) $existing->id;
            $updated = $this->CI->db->where('id', $report_id)->update('lab_report', array(
                'sample_date' => $data['sample_date'] ?? $existing->sample_date,
                'software_version' => $data['software_version'] ?? $existing->software_version,
                'metadata' => array_key_exists('metadata', $data) ? json_encode($data['metadata']) : $existing->metadata,
                'updated_at' => date('Y-m-d H:i:s'),
            ));
            if (!$updated || !$this->CI->LabResult_model->deleteByReport($report_id)) {
                throw new RuntimeException('Could not refresh existing lab report.');
            }
        } else {
			$report_id = (int) $this->CI->LabReport_model->claimSource(array(
                'pet_id' => $pet_id,
                'device' => $data['device'] ?? null,
                'source' => $data['source'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'sample_date' => $data['sample_date'] ?? null,
                'software_version' => $data['software_version'] ?? null,
                'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
                'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s'),
            ));
            if ($report_id <= 0) {
                throw new RuntimeException('Could not create lab report.');
            }
			$claimed = $this->CI->db->where('id', $report_id)->get('lab_report')->row();
			if (!$claimed) {
				throw new RuntimeException('Could not reload claimed lab report.');
			}
			if ((int) $claimed->pet_id !== $pet_id) {
				throw new DomainException('A report with this source identity already belongs to another pet.');
			}
			$updated = $this->CI->db->where('id', $report_id)->update('lab_report', array(
				'sample_date' => $data['sample_date'] ?? $claimed->sample_date,
				'software_version' => $data['software_version'] ?? $claimed->software_version,
				'metadata' => array_key_exists('metadata', $data) ? json_encode($data['metadata']) : $claimed->metadata,
				'updated_at' => date('Y-m-d H:i:s'),
			));
			if (!$updated || !$this->CI->LabResult_model->deleteByReport($report_id)) {
				throw new RuntimeException('Could not refresh claimed lab report.');
			}
        }

        if (!$this->CI->db->where('report_id', $report_id)->delete('lab_plots')) {
            throw new RuntimeException('Could not replace lab plots.');
        }
        foreach ($data['results'] as $result) {
            $result = $this->canonical($this->normalizeResultValue($result));
            if (!$this->CI->LabResult_model->save($report_id, $result)) {
                throw new RuntimeException('Could not save a lab result.');
            }
        }
        foreach ((array) ($data['plots'] ?? array()) as $type => $values) {
            if (!$this->CI->lab_plots->insert(array(
                'report_id' => $report_id,
                'type' => $type,
                'data' => json_encode($values),
            ))) {
                throw new RuntimeException('Could not save a lab plot.');
            }
        }

        return $report_id;
    }

	private function resolve_pending_identity(?array $identity, int $report_id, int $pet_id): void
	{
		if (!$this->CI->LabReportPending_model->resolve_active_identity(
			$identity['hash'] ?? null,
			$report_id,
			$pet_id,
			null
		)) {
			throw new RuntimeException('Could not resolve the matching pending lab report.');
		}
	}

	private function commit_ingestion(): void
	{
		if ($this->CI->db->trans_status() === false) {
			throw new RuntimeException('Lab report ingestion failed.');
		}
		$this->CI->db->trans_commit();
	}

	private function acquire_identity_locks(array $identity_hashes): array
	{
		$names = array_values(array_unique(array_map(static function (string $identity_hash): string {
			return 'lab:' . substr($identity_hash, 0, 60);
		}, array_filter($identity_hashes))));
		sort($names, SORT_STRING);
		$acquired = array();
		foreach ($names as $name) {
			$row = $this->CI->db->query('SELECT GET_LOCK(?, 5) AS `acquired`', array($name))->row_array();
			if (!$row || (int) $row['acquired'] !== 1) {
				$this->release_identity_locks($acquired);
				throw new RuntimeException('Could not lock the lab report source identity.');
			}
			$acquired[] = $name;
		}
		return $acquired;
	}

	private function release_identity_locks(array $lock_names): void
	{
		foreach (array_reverse($lock_names) as $lock_name) {
			$this->CI->db->query('SELECT RELEASE_LOCK(?)', array($lock_name));
		}
	}

    private function canonical(array $result): array
    {
        if (!isset($this->canonical_map[$result['code']])) {
            log_message('error', 'mapping failed canonical for ' . $result['code']);
        }
        $result['code'] = $this->canonical_map[$result['code']] ?? $result['code'];
        return $result;
    }

    private function normalizeResultValue(array $result): array
    {
        if (!array_key_exists('code', $result) || $result['code'] === null || $result['code'] === '') {
            throw new UnexpectedValueException('Lab result has no code.');
        }
        $value = $result['value'] ?? null;
        if (is_numeric($value)) {
            $result['value_num'] = (float) $value;
            $result['value_text'] = null;
        } else {
            $result['value_num'] = null;
            $result['value_text'] = trim((string) $value);
        }
        unset($result['value']);
        $result['unit'] = $result['unit'] ?? null;
        $result['ref_min'] = $result['ref_min'] ?? null;
        $result['ref_max'] = $result['ref_max'] ?? null;
        return $result;
    }

    private function resolvePetId($data)
    {
        if (!empty($data['pet_id'])) {
            $pet = $this->CI->Pets_model->does_pet_exist((int) $data['pet_id']);
            if ($pet) return $pet;
        }
        if (!empty($data['chip'])) {
            $matches = $this->CI->Pets_model->findByChipNumber($data['chip']);
            if ($matches && count($matches) === 1) return $matches[0]['id'];
        }
        if (!empty($data['phone']) && !empty($data['owner_name'])) {
            $phone = $this->normalizePhone($data['phone']);
            $names = $this->splitOwnerPet($data['owner_name']);
            if ($phone && $names['owner']) {
                $matches = $this->CI->Pets_model->findByOwnerPhoneAndPet($phone, $names['owner'], $names['pet']);
                if (count($matches) === 1) return $matches[0]->pet_id;
            }
        }
        if (!empty($data['owner_name']) && !empty($data['pet_name'])) {
            $matches = $this->CI->Pets_model->findByOwnerAndPet($data['owner_name'], $data['pet_name']);
            if (count($matches) === 1) return $matches[0]->pet_id;
        }
        if (!empty($data['owner_name'])) {
            $names = $this->splitOwnerPet($data['owner_name']);
            if ($names['owner'] && $names['pet']) {
                $matches = $this->CI->Pets_model->findByOwnerAndPet($names['owner'], $names['pet']);
                if (count($matches) === 1) return $matches[0]->pet_id;
            }
        }
        if (!empty($data['pet_name'])) {
            $matches = $this->CI->Pets_model->findByPetName($data['pet_name']);
            if ($matches && count($matches) === 1) return $matches[0]['id'];
        }
        return null;
    }

    private function normalizePhone($phone)
    {
        return preg_replace('/\D+/', '', $phone);
    }

    private function splitOwnerPet($value)
    {
        $parts = array_map('trim', explode('/', $value, 2));
        return array('owner' => $parts[0] ?? null, 'pet' => $parts[1] ?? null);
    }
}
