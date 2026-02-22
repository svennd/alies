<?php

// application/controllers/api/Lab.php
class Lab extends API_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('LabResultService');
    }

    public function import($device)
    {
        $adapter = $this->adapterFactory($device);

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

    private function adapterFactory($device)
    {
        switch ($device) {
            case 'ms4s2':
                require_once APPPATH.'third_party/api/devices/Ms4s2.php';
                return new Ms4s2();
            case 'ikems':
                require_once APPPATH.'third_party/api/devices/Ikems.php';
                return new Ikems();
            case 'lmscan':
                require_once APPPATH.'third_party/api/devices/Lmscan.php';
                return new Lmscan();
            case 'medilab':
                require_once APPPATH.'third_party/api/devices/Medilab.php';
                return new Medilab();
        }

        return null;
    }
}