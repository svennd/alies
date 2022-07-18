<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Files extends Vet_Controller
{
	private $upload_dir = "./data/";
	private $accepted_mime_type;

	# constructor
	public function __construct()
	{
		parent::__construct();

		# load librarys
		$this->load->helper('url');
		$this->load->helper('download');
		$this->load->helper('base64_to_img_helper');

		# models
		$this->load->model('Events_model', 'events');
		$this->load->model('Events_upload_model', 'events_upload');

		# config
		$upload_mimetype = array(
											// common image format
											'png', 'jpg', 'jpeg', 'tiff', 'tif', 'gif', 'bmp',
											'svg',

											// office files
											'doc', 'docx', 'ods', 'odt',
											'xls', 'xlsx',
											'pdf',
											'text', 'txt', 'rtf',

											// video files
											'mp4', '3g2', 'avi', 'mpeg', 'mov'

											);

		# get a single list of all allowed mimetypes
		$all_mimes = get_mimes(); # codeigniter function
		$accepted_mime_type = array();

		foreach ($upload_mimetype as $type) {
			if (is_array($all_mimes[$type])) {
				foreach ($all_mimes[$type] as $t) {
					$accepted_mime_type[] = $t;
				}
			} else {
				$accepted_mime_type[] = $all_mimes[$type];
			}
		}
		$this->accepted_mime_type = array_unique($accepted_mime_type);
	}

	/*
		store the temp file in our data structure by appending
		I'm not 100% sure this won't break some file
		as this is called async
	*/
	public function new_file_event($event_id)
	{
		$content = file_get_contents($_FILES['data']['tmp_name']);
		return file_put_contents("./data/e" . $event_id . "_" . $this->input->post('file_name'), $content, FILE_APPEND | LOCK_EX);
	}


	/*
		This is definitly insecure need to verify input more securely
		right now I check only mimetype
	*/
	public function new_file_event_complete($event_id)
	{
		$file_name 			= $this->input->post('file_name');
		$current_file 	= $this->upload_dir . "/e" . $event_id . "_" . $file_name;
		$mimetype				= $this->get_mime_type($current_file);

		# sanity check
		if (!file_exists($current_file)) {
			echo json_encode(array('success' => false, 'error' => 'no file'));
			return false;
		}

		# check against allowed_mimetype
		if (!in_array($mimetype, $this->accepted_mime_type)) {
			# remove it
			unlink($current_file);
			echo json_encode(array('success' => false, 'error' => 'wrong mime type'));
			return false;
		}

		// move it from staging to finished
		rename(
			$current_file,
			$this->upload_dir . "/stored/e" . $event_id . "_" . $file_name
		);

		$this->events_upload->insert(array(
					"event" 			=> $event_id,
					"filename" 		=> $this->input->post('file_name'),
					"size"	 			=> $this->input->post('file_size'),
					"user"	 			=> $this->user->id,
					"mime"	 			=> $mimetype,
					"location"	 	=> $this->user->current_location,
				));
		echo json_encode(array('success' => true));
	}

	/*
		paint in event
		filename : should not contain e_$event_id in db
	*/
	public function drawing(int $event_id, $auto = false, $final = false)
	{
		// check if post happened
		if($this->input->post('drawing') === null) { return false; }

		// make sure its unique
		$timestamp = date('Hmsdmy');

		// in case its auto storing data, remove older drawings
		if ($auto)
		{
			array_map('unlink', glob($this->upload_dir . "stored/e" . $event_id . "_*_draw.jpeg"));
		}

		// store
		$image = base64_to_image($this->input->post('drawing'), $this->upload_dir . "stored/", "e" . $event_id . "_" . $timestamp . '_'.  (($final) ? "fin" : "draw"));

		list($name, $type, $size) = $image;

		if (!(bool)$auto)
		{
			$id = $this->events_upload->insert(array(
					"event" 			=> $event_id,
					"filename" 			=> ($final) ? $timestamp . '_'. "fin.jpeg" : $timestamp . '_'. "draw.jpeg",
					"size"	 			=> $size,
					"user"	 			=> $this->user->id,
					"mime"	 			=> $type,
					"location"	 		=> $this->user->current_location,
			));
		}
		else
		{
			$this->events_upload->update(array(
				"event" 			=> $event_id,
				"filename" 			=> ($final) ? $timestamp . '_'. "fin.jpeg" : $timestamp . '_'. "draw.jpeg",
				"size"	 			=> $size,
				"user"	 			=> $this->user->id,
				"mime"	 			=> $type,
				"location"	 		=> $this->user->current_location,
			),
			array("id" => (int) $auto));
			$id = (int) $auto;
		}
		echo json_encode(array('success' => (bool) $auto, 'auto' => $id));
	}

	public function get_file($id)
	{
		$file_info = $this->events_upload->get($id);

		force_download(
				$file_info['filename'],
				file_get_contents($this->upload_dir . "stored/e" . $file_info['event'] . "_" . $file_info['filename']),
				$file_info['mime']
		);
	}

	public function delete_file($id)
	{
		$event_info = $this->events_upload->get($id);
		if($event_info && file_exists($this->upload_dir . "stored/e" . $event_info['event'] . "_" . $event_info['filename']))
		{
			unlink($this->upload_dir . "stored/e" . $event_info['event'] . "_" . $event_info['filename']);
			$this->events_upload->where(array('user' => $this->user->id))->delete($id);
		}
		# report it
		else
		{
			$this->logs->logger($this->user->id, WARN, "broken delete", "file deletion, on a non-existing file or event : " . $id ." (files/delete_file)");
		}
	}

	private function get_mime_type($file)
	{
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		if (!$finfo) {
			echo "Opening fileinfo database failed";
			return false;
		}
		return $finfo->file($file);
	}
}
