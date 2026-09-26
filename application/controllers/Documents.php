<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Documents extends Vet_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Document_service');
    }

    public function invoice($id) { $this->single('invoice', $id); }
    public function overview($id) { $this->single('overview', $id); }

    private function single(string $type, $id): void
    {
        try {
            $ids = Document_data_model::ids([$id], true);
            $content = $this->document_data->{$type}($ids[0]);
            $pdf = $this->document_service->pdf($type, $content, $this->document_settings->all());
            $this->document_service->download($pdf, 'typst-' . $type . '-' . $ids[0] . '.pdf');
        } catch (Throwable $error) {
            $this->output->set_status_header($error->getMessage() === 'busy' ? 429 : 422);
            $this->_render_page('documents/error', ['document_error' => $this->document_service->error($error)]);
        }
    }

    public function reminders($month = '1')
    {
        $error = ''; $rows = []; $excluded = []; $selected = null; $products = [];
        try {
            $month = $this->document_service->month($month);
            if ($this->input->method() === 'post') {
                $this->document_service->verify_post();
                $excluded = Document_data_model::ids($this->input->post('excluded') ?? []);
                if ($this->input->post('action') === 'generate') {
                    $selected = Document_data_model::ids($this->input->post('rows') ?? [], true);
                    $content = $this->document_data->reminders($month, $excluded, $selected);
                    $pdf = $this->document_service->pdf('reminder', $content, $this->document_settings->all());
                    $this->document_service->download($pdf, 'vaccination-reminders-' . Document_data_model::month($month)->format('Y-m') . '.pdf');
                    return;
                }
            }
        } catch (Throwable $exception) { $error = $this->document_service->error($exception); }
        // Revalidate the route independently even when an invalid selection was submitted.
        try {
            $month = $this->document_service->month($month);
            $all = $this->document_data->reminder_rows($month);
            foreach ($all as $row) { $products[$row['product_id']] = $row['vaccine']; }
            $rows = $this->document_data->reminder_rows($month, $excluded);
        } catch (Throwable $exception) {
            $this->output->set_status_header(422);
            $this->_render_page('documents/error', ['document_error' => $this->document_service->error($exception)]);
            return;
        }
        $this->_render_page('documents/reminders', ['month' => $month, 'rows' => $rows, 'products' => $products, 'excluded' => $excluded, 'selected' => $selected, 'document_error' => $error, 'document_token' => $this->document_service->token()]);
    }
}
