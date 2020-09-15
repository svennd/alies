<?php
Class MY_Controller extends CI_Controller {

        public $data = array();

        # constructer
        function __construct()
        {
                # pull everything from ci_controller
                parent::__construct();

                $this->data['errors'] = array();
                $this->data['site_name'] = config_item('site_name');
        }
}