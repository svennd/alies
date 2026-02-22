<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Class: API_Controller
// nothing special needed for the API controller
class API_Controller extends MY_Controller
{
    protected $apiKey;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('ApiKey_model');
        $this->load->model('ApiRate_model');

        $this->requireApiKey();
        $this->touchLastApiKeyUsage($this->apiKey['id']);
        $this->checkRateLimit();
    }

    protected function requireApiKey()
    {
        $key = $this->input->get_request_header('Authorization');
        if ($key && strpos($key, 'Bearer ') === 0) {
            $key = substr($key, 7);
        }

        if (!$key) {
            $key = $this->input->get_request_header('X-API-Key');
        }

        if (!$key) {
            $this->jsonError('Missing API key', 401);
        }

        $row = $this->ApiKey_model->findActive($key);
        if (!$row) {
            $this->jsonError('Invalid API key', 401);
        }

        $this->apiKey = $row;
    }

    protected function checkRateLimit($limit = 60)
    {
        $minute = date('YmdHi');
        if (!$this->ApiRate_model->hit($this->apiKey['id'], $minute, $limit)) {
            $this->jsonError('Rate limit exceeded', 429);
        }
    }

    protected function touchLastApiKeyUsage($apiKeyId)
    {
        $this->ApiKey_model->where('id', $apiKeyId)->update([
            'last_used_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    protected function json($data, $code = 200)
    {
        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode($data))
            ->_display();
        exit;
    }

    protected function jsonError($message, $code)
    {
        $this->json(['status' => 'error', 'message' => $message], $code);
    }
}
