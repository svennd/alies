<?php defined('BASEPATH') or exit('No direct script access allowed');

class Vet extends Vet_Controller
{
	public function __construct()
	{
		parent::__construct();

		# libraries
		$this->load->library('ion_auth');
		$this->load->library('form_validation');
		$this->load->library('form_validation');
		
		# models
		$this->load->model('Users_model', 'users');
	}

	# show profile
	public function pub($id)
	{
		$data = array( 
				'profile' => $this->users->fields('email, last_login, first_name, last_name, phone, updated_at, image, sidebar')->get( (int) $id)
		);
		
		$this->_render_page('member/public_profile', $data);
	}
	
	# change password
	public function change_password()
	{
		$this->form_validation->set_rules('old', 'old Password', 'required');
		$this->form_validation->set_rules('new', 'New Password', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', 'Confirm New Password', 'required');

		if (!$this->ion_auth->logged_in()) {
			redirect('auth/login', 'refresh');
		}

		$user = $this->ion_auth->user()->row();

		if ($this->form_validation->run() == false) {
			//display the form
			//set the flash data error message if there is one
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			$this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
			$this->data['old_password'] = array(
				'name' => 'old',
				'id'   => 'old',
				'type' => 'password',
			);
			$this->data['new_password'] = array(
				'name' => 'new',
				'id'   => 'new',
				'type' => 'password',
				'pattern' => '^.{'.$this->data['min_password_length'].'}.*$',
			);
			$this->data['new_password_confirm'] = array(
				'name' => 'new_confirm',
				'id'   => 'new_confirm',
				'type' => 'password',
				'pattern' => '^.{'.$this->data['min_password_length'].'}.*$',
			);
			$this->data['user_id'] = array(
				'name'  => 'user_id',
				'id'    => 'user_id',
				'type'  => 'hidden',
				'value' => $user->id,
			);

			//render
			$this->_render_page('auth/change_password', $this->data);
		} else {
			$identity = $this->session->userdata('identity');

			$change = $this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'));

			if ($change) {
				//if the password was successfully changed
				$this->ion_auth->logout();
				redirect('auth/login', 'refresh');
			} else {
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect('vet/change_password', 'refresh');
			}
		}
	}
	
	# change avatar to preselected one
	public function avatar($pict_id = false)
	{
		# if no valid id is given
		if ($pict_id === false) { redirect('vet/profile', 'refresh'); }
		$img_list = $this->get_pictures($this->user->id);
		
		
		# check what image the vet has selected
		$chosen_img = false;
		foreach($img_list['user'] as $img)
		{
			if ($img['id'] == $pict_id) { $chosen_img = $img['img']; break; }
		}
		if (!$chosen_img)
		{
			foreach($img_list['pre'] as $img)
			{
				if ($img['id'] == $pict_id) { $chosen_img = $img['img']; break; }
			}
		}
		
		# only if valid image is selected
		if($chosen_img)
		{
			$this->users->update(array('image' => basename($chosen_img)), $this->user->id);
		}
		redirect('vet/profile', 'refresh');
	}
	
	public function profile_change()
	{
		$this->users->update(
								array(
										'sidebar' 		=> $this->input->post('color'),
										'first_name' 	=> $this->input->post('first_name'),
										'last_name' 	=> $this->input->post('last_name'),
										'search_config' => $this->input->post('search_config'),
										'phone' 		=> $this->input->post('phone'),
									), $this->user->id);
		redirect('vet/profile', 'refresh');
	}
	
	// crop upload & images
	public function profile()
	{
		$upload_size = array(
								"width" => 250,
								"height" => 250,
								);
		
		// setup
		$data = array(
			'user' => $this->user,
			'extra_header' => '<link href="'. base_url() .'assets/css/croppie.css" rel="stylesheet">',
			'extra_footer' => '<script src="'. base_url() .'assets/js/croppie.min.js"></script>'
			);
		
		$raw_data = $this->input->post('imagebase64');
		$img_store = $this->input->post('imagestore');
		
		// process new image
		if ($raw_data) {
			// remove header : data:image/png;base64
			list($type, $raw_data) = explode(';', $raw_data);
			list(, $raw_data)      = explode(',', $raw_data);
			
			// decode
			$raw_decoded = base64_decode($raw_data);
			
			// create image from it
			$ori_img = imagecreatefromstring($raw_decoded);
			
			// unrecognized format
			if(!$ori_img) {
				redirect('vet/profile', 'refresh');
				return false;
			}
			
			// determ size for resampling & resizing
			$ori_size = getimagesizefromstring($raw_decoded);
			list($org_w, $org_h) = ($ori_size);
			
			// create new image background
			$resized_img = imagecreatetruecolor($upload_size['width'], $upload_size['height']);
			
			// resize fails 
			if(!$ori_img) {
				redirect('vet/profile', 'refresh');
				return false;
			}
			
			// resample & resize
			imagecopyresampled($resized_img, $ori_img, 0, 0, 0, 0, $upload_size['width'], $upload_size['height'], $org_w, $org_h);

			// needs buffering for reading the newly created file
			ob_start();
			
			// store it
			$time = time();
			$data['time'] = $time;
			imagepng($resized_img, 'assets/public/user_' . $this->user->id . '_'. $time .'.check.png');
			
			// read it
			imagepng($resized_img);
			$data['new_image'] = ob_get_contents();
			ob_end_clean();
			imagedestroy($resized_img);
		}
		
		// Accept the new image
		if ($img_store) {
			$submit = ($this->input->post('submit') == "Accept") ? true : false;
			$timetag = $this->input->post('timetag');
			if ($submit) {
				if (file_exists('assets/public/user_' . $this->user->id . '_'. $timetag .'.check.png')) {
					rename(
						'assets/public/user_' . $this->user->id . '_'. $timetag .'.check.png',
						'assets/public/user_' . $this->user->id . '_'. $timetag .'.png'
					);
					$this->users->update(array('image' => 'user_' . $this->user->id . '_'. $timetag . '.png'), $this->user->id);
					$data['image_updated_info'] = "Accepted new image";
					$data['uploaded_image'] = 'user_' . $this->user->id . '_'. $timetag .'.png';
				} else {
					$data['image_updated_info'] = "Failed new file";
				}
			}
		}
		
		$data['preselected'] = $this->get_pictures($this->user->id);
		$this->_render_page('member/profile', $data);
	}
	
	private function get_pictures($user_id = false)
	{
		$image_list = array();
		$i = 0;
		
		# get images user uploaded previously 
		if($user_id) {
			foreach (glob("assets/public/user_" . (int) $user_id . "_*.png") as $filename) {
				if(substr($filename, -9, 9) == "check.png") { continue; }
				$image_list['user'][] = array( 'img' => $filename, 'id' => $i );
				$i++;
			}
		}
		
		# get all premade images
		foreach (glob("assets/public/pre_*.png") as $filename) {
			$image_list['pre'][] = array( 'img' => $filename, 'id' => $i );
			$i++;
		}
		
		return $image_list;
	}
}
