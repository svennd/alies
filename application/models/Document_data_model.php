<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Document_data_model extends CI_Model
{
    public const REMINDER_BATCH_LIMIT = 1000;

    public function config_value(string $key, string $default = ''): string
    {
        $row = $this->db->where('name', $key)->get('config')->row_array();
        return $row ? (string) base64_decode($row['value']) : $default;
    }

    public function invoice(int $id): array
    {
        $this->load->model('Bills_model', 'document_bills');
        $this->load->model('Owners_model', 'document_owners');
        $this->load->model('Pets_model', 'pets');
        $this->load->model('Events_model', 'events');
        $this->load->model('Liquidate_model', 'document_liquidate');
        $bill = $this->document_bills->with_location('fields:name')->get($id);
        if (!$bill) { throw new RuntimeException('not_found'); }
        $owner = $this->document_owners->get($bill['owner_id']);
        if (!$owner) { throw new RuntimeException('not_found'); }
        if ((int) $bill['status'] === BILL_DRAFT || $bill['total_net'] === null || $bill['total_brut'] === null) { throw new RuntimeException('unprepared'); }
        $lines = [];
        $writeOff = $this->document_liquidate->get_bill_rows($id);
        if ($writeOff) {
            foreach ($writeOff as $line) {
                $description = $line['product_name'];
                if ($line['lotnr']) { $description .= ' (lot ' . $line['lotnr'] . ')'; }
                if ($line['eol']) { $description .= ' EOL ' . self::date($line['eol']); }
                $lines[] = ['group' => $this->label('write_off'), 'description' => $description, 'quantity' => self::money($line['volume']) . ' ' . $line['unit_sell'], 'unit_price' => '0,00', 'tax' => '0%', 'total' => '0,00'];
            }
        } else {
            foreach ($this->document_bills->get_details($id, $bill['owner_id']) as $event) {
                foreach (array_merge($event['procedures'], $event['products']) as $line) {
                    $lines[] = ['group' => $event['pet']['name'], 'description' => $line['name'], 'quantity' => self::money($line['volume']) . ' ' . ($line['unit_sell'] ?? ''), 'unit_price' => self::money($line['unit_price']), 'tax' => $line['btw'] . '%', 'total' => self::money($line['price_net'])];
                }
            }
        }
        if (!$lines && abs((float) $bill['total_brut']) > 0.001) { throw new RuntimeException('unprepared'); }
        $tax = [];
        foreach ([0, 6, 21] as $rate) {
            $base = (float) ($bill['BTW_' . $rate] ?? 0);
            $tax[] = [$rate . '%', self::money($base), self::money($base * $rate / 100)];
        }
        $date = $bill['invoice_id'] ? $bill['invoice_date'] : $bill['created_at'];
        if (!$date) { throw new RuntimeException('unprepared'); }
        $number = $bill['invoice_id'] ? get_invoice_id((int) $bill['invoice_id'], $date, base64_encode($this->config_value('invoice_prefix'))) : get_bill_id($id);
        $reference = generate_struct_message((int) $owner['id'], $id, (int) $this->config_value('struct_config', (string) CLIENT_BILL));
        $paid = (int) $bill['status'] === BILL_PAID && ((float) $bill['transfer'] == 0 || (int) $bill['transfer_verified'] === 1);
        return [
            'title' => $this->label($bill['invoice_id'] ? 'invoice' : 'bill'), 'number' => $number,
            'date' => self::date($date), 'due' => $paid ? $this->label('paid') : self::date(date('Y-m-d', strtotime($date . ' +' . (int) $this->config_value('due_date', '30') . ' days'))),
            'recipient' => trim($owner['last_name'] . ' ' . $owner['first_name']),
            'address' => $owner['invoice_addr'] ? trim(($owner['invoice_contact'] ?? '') . "\n" . $owner['invoice_addr']) : self::address($owner),
            'client_id' => (string) $owner['id'], 'vat_number' => (string) ($owner['btw_nr'] ?? ''),
            'location' => (string) ($bill['location']['name'] ?? ''), 'message' => (string) ($bill['msg_invoice'] ?? ''),
            'lines' => $lines, 'tax_rows' => $tax, 'net' => self::money($bill['total_net']),
            'tax_total' => self::money((float) $bill['total_brut'] - (float) $bill['total_net']), 'total' => self::money($bill['total_brut']),
            'cash' => self::money($bill['cash']), 'card' => self::money($bill['card']), 'transfer' => self::money($bill['transfer']),
            'payment_status' => $paid ? $this->label('paid') : $this->label('unpaid'),
            'reference' => $reference, 'iban' => $this->config_value('iban'), 'bic' => $this->config_value('bic'), 'bank_name' => $this->config_value('nameiban'),
            'qr_amount' => $paid ? 0 : max(0, (float) $bill['total_brut'] - (float) $bill['cash'] - (float) $bill['card'] - ((int) $bill['transfer_verified'] === 1 ? (float) $bill['transfer'] : 0)),
        ];
    }

    public function overview(int $id): array
    {
        $this->load->model('Pets_model', 'document_pets');
        $this->load->model('Owners_model', 'document_owners');
        $this->load->model('Vaccine_model', 'document_vaccines');
        $pet = $this->document_pets->with_breeds('fields:name')->get($id);
        if (!$pet) { throw new RuntimeException('not_found'); }
        $owner = $this->document_owners->get($pet['owner']);
        if (!$owner) { throw new RuntimeException('not_found'); }
        $rows = $this->document_vaccines->with_vet('fields:first_name,last_name')->with_product('fields:name')->with_location('fields:name')->where('pet', $id)->order_by('created_at', 'ASC')->get_all() ?: [];
        $vaccines = [];
        foreach ($rows as $row) {
            if ((int) $row['event_id'] === 0) { continue; }
            $vaccines[] = [
                'vaccine' => (string) ($row['product']['name'] ?? $row['product'] ?? ''), 'date' => self::date($row['created_at']),
                'vet' => trim(($row['vet']['first_name'] ?? '') . ' ' . ($row['vet']['last_name'] ?? '')),
                'location' => (string) ($row['location']['name'] ?? ''), 'due' => $row['no_rappel'] ? '-' : self::date($row['redo']),
            ];
        }
        return ['recipient' => trim($owner['last_name'] . ' ' . $owner['first_name']), 'address' => self::address($owner),
            'pet' => $pet['name'], 'pet_id' => (string) $pet['id'], 'type' => strip_tags(get_name($pet['type'])),
            'birth' => self::date($pet['birth']), 'gender' => strip_tags(get_gender($pet['gender'])), 'breed' => (string) ($pet['breeds']['name'] ?? ''),
            'chip' => (string) ($pet['chip'] ?? ''), 'weight' => (string) ($pet['last_weight'] ?? ''), 'vaccines' => $vaccines];
    }

    public function reminder_rows(int $month, array $excluded = []): array
    {
        $date = self::month($month);
        $query = $this->db->select('v.id, v.pet as pet_id, v.redo, v.created_at, o.id as owner_id, o.first_name, o.last_name, o.street, o.nr, o.zip, o.city, p.name as pet_name, pr.id as product_id, pr.name as vaccine, pr.vaccin_disease as disease')
            ->from('vaccine_pet v')->join('products pr', 'pr.id = v.product_id')->join('pets p', 'p.id = v.pet')
            ->join('owners o', 'o.id = CASE WHEN p.companion IS NOT NULL AND p.companion != 0 THEN p.companion ELSE p.owner END', 'inner', false)
            ->join('users u', 'u.id = v.vet')->where('v.redo >=', $date->format('Y-m-01'))->where('v.redo <=', $date->format('Y-m-t'))
            ->where(['p.death' => 0, 'p.lost' => 0, 'p.transfered' => 0, 'v.no_rappel' => 0, 'o.disabled' => 0]);
        if ($excluded) { $query->where_not_in('pr.id', $excluded); }
        $rows = $query->order_by('v.redo', 'ASC')->order_by('v.id', 'ASC')->get()->result_array();
        foreach ($rows as &$row) {
            $row['recipient'] = trim($row['last_name'] . ' ' . $row['first_name']);
            $row['address'] = self::address($row);
            $row['pet'] = $row['pet_name'];
            $row['due_date'] = self::date($row['redo']);
            $row['disease'] = $row['disease'] ?: $row['vaccine'];
        }
        return $rows;
    }

    public static function ids($input, bool $selection = false): array
    {
        if (!is_array($input) || ($selection && (count($input) < 1 || count($input) > self::REMINDER_BATCH_LIMIT)) || count($input) > 1000) { throw new InvalidArgumentException('selection'); }
        $ids = [];
        foreach ($input as $id) {
            if ((!is_string($id) && !is_int($id)) || !preg_match('/^[1-9][0-9]{0,9}$/D', (string) $id)) { throw new InvalidArgumentException('selection'); }
            $ids[] = (int) $id;
        }
        if (count(array_unique($ids)) !== count($ids)) { throw new InvalidArgumentException('selection'); }
        return $ids;
    }

    public function reminders(int $month, array $excluded, array $ids): array
    {
        $ids = self::ids($ids, true);
        $rows = array_values(array_filter($this->reminder_rows($month, $excluded), fn($r) => in_array((int) $r['id'], $ids, true)));
        if (count($rows) !== count($ids)) { throw new RuntimeException('stale'); }
        return ['letters' => $rows];
    }

    public static function month(int $offset): DateTimeImmutable
    {
        if (abs($offset) > 1200) { throw new InvalidArgumentException('month'); }
        return (new DateTimeImmutable('first day of this month'))->modify($offset . ' months');
    }

    public function label(string $key): string
    {
        return (string) ($this->lang->line('doc_' . $key) ?: $key);
    }

    public static function address(array $owner): string
    {
        return trim($owner['street'] . ' ' . $owner['nr']) . "\n" . trim($owner['zip'] . ' ' . $owner['city']);
    }

    public static function money($number): string { return number_format((float) $number, 2, ',', '.'); }
    public static function date($date): string { return $date && strtotime($date) !== false ? date('d-m-Y', strtotime($date)) : '-'; }
}
