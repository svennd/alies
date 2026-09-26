<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DocumentReminderSelectionTest extends TestCase
{
    public function testEmptySmallAndLaterSelections(): void
    {
        require_once APPPATH . 'models/Document_data_model.php';
        $ci = get_instance();
        $ci->lang->load('documents', 'dutch');
        foreach ([0, 1, 10, 23] as $count) {
            $rows = [];
            for ($i = 1; $i <= $count; $i++) $rows[] = ['id' => $i, 'recipient' => 'Client', 'pet' => 'Pet', 'vaccine' => 'Vaccine', 'due_date' => '20-09-2026'];
            $data = ['month' => 0, 'rows' => $rows, 'products' => [], 'excluded' => [], 'selected' => null, 'document_error' => '', 'document_token' => 'test'];
            $html = $ci->load->view('documents/reminders', $data, true);
            self::assertSame(min(10, $count), preg_match_all('/class="document-row"[^>]+ checked/', $html));
            if (!$count) self::assertStringNotContainsString('id="document-generate"', $html);
            if ($count === 23) {
                $data['selected'] = [21, 22, 23];
                $html = $ci->load->view('documents/reminders', $data, true);
                self::assertSame(3, preg_match_all('/class="document-row"[^>]+ checked/', $html));
                self::assertMatchesRegularExpression('/value="23"[^>]+ checked/', $html);
            }
        }
    }
}
