<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pet_avatar_manager
{
	private $processor;
	private $pets;
	private $logs;

	public function __construct(array $config = array())
	{
		$ci =& get_instance();
		$this->processor = isset($config['processor']) ? $config['processor'] : $ci->pet_avatar;
		$this->pets = isset($config['pets']) ? $config['pets'] : $ci->pets;
		$this->logs = isset($config['logs']) ? $config['logs'] : $ci->logs;
	}

	public function save(int $pet_id, array $source, string $crop_data): array
	{
		$pet = $this->pets->fields('id, avatar')->get($pet_id);
		if (!$pet) {
			return array('status' => 'unknown');
		}

		$stored = $this->processor->store($source, $crop_data);
		if (!$stored['success']) {
			return array('status' => 'invalid', 'error' => $stored['error']);
		}

		$association = $this->pets->replace_avatar($pet_id, $stored['filename']);
		if ($association === false) {
			$this->processor->delete($stored['filename']);
			return array('status' => 'storage');
		}

		$previous = $association['previous'];
		if (!empty($previous) && $previous !== $stored['filename'] && $this->pets->avatar_reference_count($previous) === 0) {
			$this->processor->delete($previous);
		}

		$replaced = !empty($previous);
		$this->logs->logger(
			INFO,
			$replaced ? 'pet_avatar_replace' : 'pet_avatar_upload',
			'Pet avatar changed for pet #' . $pet_id
		);

		return array(
			'status' => 'success',
			'message_key' => $replaced ? 'pet_avatar_replaced' : 'pet_avatar_uploaded',
			'filename' => $stored['filename'],
		);
	}

	public function remove(int $pet_id): array
	{
		$pet = $this->pets->fields('id, avatar')->get($pet_id);
		if (!$pet) {
			return array('status' => 'unknown');
		}

		$association = $this->pets->replace_avatar($pet_id, null);
		if ($association === false) {
			return array('status' => 'storage');
		}

		$previous = $association['previous'];
		if (!empty($previous) && $this->pets->avatar_reference_count($previous) === 0) {
			$this->processor->delete($previous);
		}

		if (!empty($previous)) {
			$this->logs->logger(INFO, 'pet_avatar_remove', 'Pet avatar removed for pet #' . $pet_id);
		}

		return array('status' => 'success', 'message_key' => 'pet_avatar_removed');
	}
}
