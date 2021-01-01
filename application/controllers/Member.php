<?php defined('BASEPATH') or exit('No direct script access allowed');

class Member extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();

		# libraries
		$this->load->library('ion_auth');
		
		# models
		$this->load->model('Users_model', 'users');
	}

	# generate list of users
	public function index()
	{
		$users = $this->users->get_all();
		
		foreach ($users as $k => $user) {
			$users[$k]['groups'] = $this->ion_auth->get_users_groups($user['id'])->result();
		}

		$data = array(
						"users" => $users
					);
		$this->_render_page('member/index', $data);
	}
	
	# create a user
	public function create_user()
	{
		$warning = false;
		$registered = false;
		
		if ($this->input->post('submit') == "create_user") {
			// var_dump($this->input->post());
			$email 		= (!empty($this->input->post('email'))) ? $this->input->post('email') : false;
			$password 	= (
				!empty($this->input->post('password'))
							&&
							$this->input->post('password') == $this->input->post('password_confirm')
			) ? $this->input->post('password') : false;
			
			# not fine
			if (!$email || !$password) {
				$warning = "email or password not correct;";
			}
			# fine
			else {
				$username = $this->input->post('first_name'). " " . $this->input->post('last_name');
				$additional_data = array(
					'first_name' => $this->input->post('first_name'),
					'last_name'  => $this->input->post('last_name'),
					'phone'      => $this->input->post('phone')
				);
				$registration_result = $this->ion_auth->register($username, $password, $email, $additional_data);
				
				// var_dump($registration_result);
				if ($registration_result) {
					# add user to groups
					$groupData = $this->input->post('groups');

					if (isset($groupData) && !empty($groupData)) {
						# remove all groups
						$this->ion_auth->remove_from_group('', $registration_result);

						# add selected groups
						foreach ($groupData as $grp) {
							$this->ion_auth->add_to_group($grp, $registration_result);
						}
					}
					$registered = true;
				} else {
					$warning = "registration failed" . $registration_result;
				}
			}
		}
		
		$data = array(
						"groups" => $this->ion_auth->groups()->result_array(),
						"warning" => $warning,
						"registered" => $registered,
					);
		$this->_render_page('member/create_user', $data);
	}
	
	# edit user
	public function edit_user($id)
	{
		$warning = false;
		$update = false;
		
		$user_edit = $this->ion_auth->user($id)->row();
		$groups_edit = $this->ion_auth->groups()->result_array();
		$currentGroups_edit = $this->ion_auth->get_users_groups($id)->result();
		
		if ($this->input->post('submit') == "edit_user" && $user_edit) {
		
			$password 	= (
				!empty($this->input->post('password'))
							&&
							$this->input->post('password') == $this->input->post('password_confirm')
			) ? $this->input->post('password') : false;
			

			$additional_data = array(
				'username'	 => $this->input->post('first_name'). " " . $this->input->post('last_name'),
				'first_name' => $this->input->post('first_name'),
				'last_name'  => $this->input->post('last_name'),
				'phone'      => $this->input->post('phone')
			);
			
			/*
				only change password when its filled in
			*/
			if ($password)
			{
				$additional_data['password'] = $this->input->post('password');
			}
			
			/*
				redo groups
			*/
			$groupData = $this->input->post('groups');
			if (isset($groupData) && !empty($groupData)) {
				$this->ion_auth->remove_from_group('', $id);

				foreach ($groupData as $grp) {
					$this->ion_auth->add_to_group($grp, $id);
				}
			}
			
			$this->ion_auth->update($user_edit->id, $additional_data);
			$update = true;
		}
		
		$user_edit = $this->ion_auth->user($id)->row();
		$currentGroups_edit = $this->ion_auth->get_users_groups($id)->result();
		
		# in case of error id
		if(!$user_edit) {redirect( base_url(). 'member');}
		
		$data = array(
						"warning" => $warning,
						"update" => $update,
						"user_edit" => $user_edit,
						"groups_edit" => $groups_edit,
						"current_groups_edit" => $currentGroups_edit,
					);
		$this->_render_page('member/edit_user', $data);
	}
	
}
