<?php

// application/controllers/api/Lab.php
class Lab extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('LabResultService');
		$this->load->library('lab_device_adapter_factory');
    }

    public function import($device)
    {
        $adapter = $this->lab_device_adapter_factory->create($device);

        if (!$adapter) show_404();

        $payload = json_decode($this->input->raw_input_stream, true);

        
        if (!is_array($payload)) {
            $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'error',
                'message' => 'Invalid JSON payload'
            ]));
        return;
        }

        $data = $this->labresultservice->ingest($adapter, $payload);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));

    }
}
