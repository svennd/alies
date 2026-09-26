<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Document_center extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Document_service');
    }

    public function index(string $scope = 'invoice')
    {
        if (!in_array($scope, ['branding', ...Document_settings::TYPES], true)) { show_404(); }
        try { $settings = $this->document_settings->all(); }
        catch (RuntimeException $exception) {
            show_error($this->document_service->error($exception), 503);
            return;
        }
        $error = ''; $success = false;
        if ($this->input->method() === 'post') {
            $this->document_service->verify_post();
            try {
                $action = $this->input->post('action');
                if ($action === 'restore') {
                    $this->document_settings->restore($scope);
                    $settings = $this->document_settings->all();
                    $success = true;
                } else {
                    $fields = $this->input->post('fields');
                    if (!is_array($fields)) { throw new InvalidArgumentException('fields'); }
                    $settings = $this->document_settings->validate($scope, $fields, $settings);
                    $upload = $_FILES['logo'] ?? ['error' => UPLOAD_ERR_NO_FILE];
                    $hasUpload = $scope === 'branding' && $upload['error'] !== UPLOAD_ERR_NO_FILE;
                    if ($scope === 'branding' && $this->input->post('remove_logo') === '1') { $settings['branding']['logo'] = ''; }
                    if ($action === 'preview') {
                        $type = $scope === 'branding' ? 'invoice' : $scope;
                        $id = $this->input->post('record_id');
                        if ($id !== null && $id !== '' && (!is_string($id) || !ctype_digit($id) || (int) $id < 1)) { throw new InvalidArgumentException('record_id'); }
                        if (!$id) { $content = $this->document_service->sample($type); }
                        elseif ($type === 'reminder') {
                            $month = $this->document_service->month($this->input->post('month') ?? '0');
                            $content = $this->document_data->reminders($month, [], [(int) $id]);
                        } else { $content = $this->document_data->{$type}((int) $id); }
                        $pdf = $this->document_service->pdf($type, $content, $settings, $hasUpload ? $this->document_settings->logo_data($upload) : null);
                        $this->document_service->download($pdf, 'preview-' . $type . '.pdf', true);
                        return;
                    } elseif ($action === 'save') {
                        if ($hasUpload) { $settings['branding']['logo'] = $this->document_settings->store_logo($upload); }
                        elseif ($scope === 'branding' && $this->input->post('remove_logo') === '1') { $settings['branding']['logo'] = ''; }
                        $this->document_settings->save($settings);
                        $success = true;
                    } else { throw new InvalidArgumentException('action'); }
                }
            } catch (Throwable $exception) { $error = $this->document_service->error($exception); }
        }
        $this->_render_page('documents/center', ['scope' => $scope, 'document_settings' => $settings, 'document_error' => $error, 'document_success' => $success, 'document_token' => $this->document_service->token()]);
    }

    public function logo()
    {
        $settings = $this->document_settings->all();
        $bytes = $this->document_settings->logo_bytes($settings['branding']['logo']);
        if ($bytes === null) { show_404(); }
        $this->output->set_content_type('image/png')->set_header('Cache-Control: no-store, private')->set_header('X-Content-Type-Options: nosniff')->set_output($bytes);
    }
}
