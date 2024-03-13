<?php
/**
 * Name:    Ion Auth
 * Author:  Ben Edmunds
 *           ben.edmunds@gmail.com
 *           @benedmunds
 *
 * Added Awesomeness: Phil Sturgeon
 *
 * Created:  10.01.2009
 *
 * Description:  Modified auth system based on redux_auth with extensive customization. This is basically what Redux Auth 2 should be.
 * Original Author name has been kept but that does not mean that the method has not been modified.
 *
 * Requirements: PHP5.6 or above
 *
 * @package    CodeIgniter-Ion-Auth
 * @author     Ben Edmunds
 * @link       http://github.com/benedmunds/CodeIgniter-Ion-Auth
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Ion_auth
 */
class Ion_auth
{
	/**
	 * account status ('not_activated', etc ...)
	 *
	 * @var string
	 **/
	protected $status;

	/**
	 * extra where
	 *
	 * @var array
	 **/
	public $_extra_where = [];

	/**
	 * extra set
	 *
	 * @var array
	 **/
	public $_extra_set = [];

	/**
	 * caching of users and their groups
	 *
	 * @var array
	 **/
	public $_cache_user_in_group;

	/**
	 * __construct
	 *
	 * @author Ben
	 */
	public function __construct()
	{
		// Check compat first
		// $this->check_compatibility();

		$this->config->load('ion_auth', TRUE);
		$this->load->library(['email']);
		$this->lang->load('ion_auth');
		$this->load->helper(['cookie', 'language','url']);

		$this->load->library('session');

		$this->load->model('ion_auth_model');

		$this->_cache_user_in_group =& $this->ion_auth_model->_cache_user_in_group;
	
		$email_config = $this->config->item('email_config', 'ion_auth');

		if ($this->config->item('use_ci_email', 'ion_auth') && isset($email_config) && is_array($email_config))
		{
			$this->email->initialize($email_config);
		}

		$this->ion_auth_model->trigger_events('library_constructor');
	}

	/**
	 * __call
	 *
	 * Acts as a simple way to call model methods without loads of stupid alias'
	 *
	 * @param string $method
	 * @param array  $arguments
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function __call($method, $arguments)
	{
		if($method == 'create_user')
		{
			return call_user_func_array([$this, 'register'], $arguments);
		}
		if($method=='update_user')
		{
			return call_user_func_array([$this, 'update'], $arguments);
		}
		if (!method_exists( $this->ion_auth_model, $method) )
		{
			throw new Exception('Undefined method Ion_auth::' . $method . '() called');
		}
		return call_user_func_array( [$this->ion_auth_model, $method], $arguments);
	}

	/**
	 * __get
	 *
	 * Enables the use of CI super-global without having to define an extra variable.
	 *
	 * I can't remember where I first saw this, so thank you if you are the original author. -Militis
	 *
	 * @param    string $var
	 *
	 * @return    mixed
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}

	/**
	 * Logout
	 *
	 * @return true
	 * @author Mathew
	 **/
	public function logout()
	{
		$this->ion_auth_model->trigger_events('logout');

		$identity = $this->config->item('identity', 'ion_auth');

		$this->session->unset_userdata([$identity, 'id', 'user_id', 'location']);

		// Clear all codes
		$this->ion_auth_model->clear_forgotten_password_code($identity);

		// Destroy the session
		$this->session->sess_destroy();

		return TRUE;
	}

	/**
	 * Auto logs-in the user if they are remembered
	 * @return bool Whether the user is logged in
	 * @author Mathew
	 **/
	public function logged_in()
	{
		$this->ion_auth_model->trigger_events('logged_in');

		$recheck = $this->ion_auth_model->recheck_session();

		return $recheck;
	}

	/**
	 * @return int|null The user's ID from the session user data or NULL if not found
	 * @author jrmadsen67
	 **/
	public function get_user_id()
	{
		$user_id = $this->session->userdata('user_id');
		if (!empty($user_id))
		{
			return $user_id;
		}
		return NULL;
	}

	/**
	 * @return int|null The user's identification from the session user data or NULL if not found
	 **/
	public function get_ident()
	{
		$identity = $this->session->userdata('identity');
		if (!empty($identity))
		{
			return $identity;
		}
		return NULL;
	}

	/**
	 * @return int The user's location from the session user data or NULL if not found
	 **/
	public function get_user_location(): int
	{
		$location = $this->session->userdata('location');
		if (!empty($location))
		{
			return (int) $location;
		}
		return 0;
	}

	/**
	 * @param int|string|bool $id
	 *
	 * @return bool Whether the user is an administrator
	 * @author Ben Edmunds
	 */
	public function is_admin($id = FALSE)
	{
		$this->ion_auth_model->trigger_events('is_admin');

		$admin_group = $this->config->item('admin_group', 'ion_auth');

		return $this->ion_auth_model->in_group($admin_group, $id);
	}

	public function deactivate($id = NULL)
	{
		$this->trigger_events('deactivate');

		if (!isset($id))
		{
			$this->set_error('deactivate_unsuccessful');
			return FALSE;
		}
		else if ($this->logged_in() && $this->user()->row()->id == $id)
		{
			$this->set_error('deactivate_current_user_unsuccessful');
			return FALSE;
		}

		return $this->ion_auth_model->deactivate($id);
	}

}