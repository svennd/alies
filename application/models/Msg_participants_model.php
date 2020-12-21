<?php
if (! defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Msg_participants_model extends MY_Model
{
	public $table = 'msg_participants';
	public $primary_key = 'id';
	
	public function __construct()
	{
		$this->has_one['msg'] = array(
					'foreign_model' => 'Msg_messages_model',
					'foreign_table' => 'msg_messages',
					'foreign_key' => 'id',
					'local_key' => 'msg_id'
				);
		$this->has_many['state'] = array(
					'foreign_model' => 'Msg_state_model',
					'foreign_table' => 'msg_state',
					'foreign_key' => 'msg_id',
					'local_key' => 'msg_id'
				);
		parent::__construct();
	}
	
	public function get_messages($user_id)
	{
		$sql = "
			SELECT 
				msg.id as msg_id,
				msg.body,
				msg.user_id as init_user,
				users.first_name,
				users.last_name,
				msg_state.*
			FROM 
				msg_participants as parti
			JOIN
				msg_messages as msg
			ON
				msg_id = id
			JOIN
				users
			ON
				users.id = msg.user_id
			JOIN
				msg_state
			ON	
				msg_state.msg_id = msg.id AND msg_state.user_id = '" . $user_id  . "'
			WHERE
				parti.user_id = '" . $user_id . "'
			ORDER BY
				parti.created_at
			ASC
		";
		$r = $this->db->query($sql)->result_array();
		
		// var_dump($r);
		return $r;
	}
}
