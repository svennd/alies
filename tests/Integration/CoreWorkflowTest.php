<?php

declare(strict_types=1);

final class CoreWorkflowTest extends CodeIgniterDatabaseTestCase
{
    public function testOwnerCanBeCreatedAndFoundByPhoneAndName(): void
    {
        $owners = $this->model('Owners_model', 'owners_test');

        $firstName = $this->uniqueString('OWNERFN');
        $lastName = $this->uniqueString('OWNERLN');
        $phone = '0479' . random_int(1000000, 9999999);

        $ownerId = $owners->insert([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'telephone' => $phone,
            'mobile' => null,
            'phone2' => null,
            'phone3' => null,
            'street' => 'Regression Street',
            'nr' => '12A',
            'city' => 'Ghent',
            'main_city' => 'Ghent',
            'province' => 'East Flanders',
            'zip' => '9000',
            'mail' => null,
            'msg' => '',
            'btw_nr' => null,
            'invoice_addr' => null,
            'invoice_contact' => null,
            'invoice_tel' => null,
            'debts' => 0,
            'disabled' => 0,
            'low_budget' => 0,
            'language' => 0,
            'contact' => 1,
            'last_bill' => date('Y-m-d'),
            'initial_vet' => $this->existingId('users'),
            'initial_loc' => $this->existingId('stock_location'),
        ]);

        $this->assertIsInt($ownerId);

        $phoneMatches = $owners->search_by_phone_ex($phone, 5);
        $nameMatches = $owners->search_by_name($firstName);

        $this->assertContains($ownerId, array_map('intval', array_column($phoneMatches, 'id')));
        $this->assertContains($ownerId, array_map('intval', array_column($nameMatches, 'id')));
    }

    public function testPetCanBeCreatedAndFoundByNameAndChip(): void
    {
        $owners = $this->model('Owners_model', 'owners_test');
        $pets = $this->model('Pets_model', 'pets_test');

        $ownerId = $owners->insert([
            'first_name' => $this->uniqueString('PETOWNERFN'),
            'last_name' => $this->uniqueString('PETOWNERLN'),
            'telephone' => null,
            'mobile' => null,
            'phone2' => null,
            'phone3' => null,
            'street' => 'Pet Street',
            'nr' => '7',
            'city' => 'Bruges',
            'main_city' => 'Bruges',
            'province' => 'West Flanders',
            'zip' => '8000',
            'mail' => null,
            'msg' => '',
            'btw_nr' => null,
            'invoice_addr' => null,
            'invoice_contact' => null,
            'invoice_tel' => null,
            'debts' => 0,
            'disabled' => 0,
            'low_budget' => 0,
            'language' => 0,
            'contact' => 1,
            'last_bill' => date('Y-m-d'),
            'initial_vet' => $this->existingId('users'),
            'initial_loc' => $this->existingId('stock_location'),
        ]);

        $petName = $this->uniqueString('PET');
        $chip = '9000000' . random_int(1000000, 9999999);

        $petId = $pets->insert([
            'type' => DOG,
            'name' => $petName,
            'birth' => '2022-01-01',
            'death' => 0,
            'death_date' => null,
            'breed' => null,
            'breed2' => null,
            'gender' => MALE,
            'color' => 'black',
            'last_weight' => '12.50',
            'lost' => 0,
            'chip' => $chip,
            'nr_vac_book' => null,
            'hairtype' => 'short',
            'companion' => null,
            'note' => null,
            'nutritional_advice' => null,
            'medication' => null,
            'owner' => $ownerId,
            'location' => $this->existingId('stock_location'),
            'init_vet' => $this->existingId('users'),
            'transfered' => 0,
        ]);

        $this->assertIsInt($petId);

        $nameMatches = $pets->search_by_name($petName, 10);
        $chipMatches = $pets->search_by_chip_ex($chip);

        $this->assertContains($petId, array_map('intval', array_column($nameMatches, 'pet_id')));
        $this->assertContains($petName, array_column($chipMatches, 'name'));
    }

    public function testProductCanBeCreatedAndFoundInSearch(): void
    {
        $products = $this->model('Products_model', 'products_test');

        $productName = $this->uniqueString('PRODUCT');
        $locationId = $this->existingId('stock_location');

        $productId = $products->insert([
            'name' => $productName,
            'wholesale_name' => $productName,
            'short_name' => substr($productName, 0, 20),
            'producer' => 'Regression Labs',
            'supplier' => 'Regression Supplier',
            'posologie' => '',
            'toedieningsweg' => 'oral',
            'type' => $this->existingId('products_type'),
            'dead_volume' => '0.00',
            'buy_volume' => 1,
            'sell_volume' => '1.00',
            'buy_price' => '8.50',
            'buy_price_date' => date('Y-m-d'),
            'unit_buy' => 'pcs',
            'unit_sell' => 'pcs',
            'input_barcode' => $this->uniqueString('BARCODE'),
            'btw_buy' => 6,
            'btw_sell' => 21,
            'booking_code' => $this->existingId('booking_codes'),
            'delay' => 0,
            'comment' => '',
            'comment_admin' => null,
            'sellable' => 1,
            'discontinued' => 0,
            'backorder' => 0,
            'limit_stock' => 0,
            'vaccin' => 0,
            'vaccin_freq' => 0,
            'vaccin_disease' => null,
            'vhbcode' => '',
            'cnk' => null,
            'cti_e' => null,
            'wholesale' => $this->existingId('wholesale'),
            'usage_count' => 0,
            'is_antibiotic' => 0,
            'default_indication' => 'NONE',
            'ab_unit' => null,
            'ab_unit_volume' => null,
        ]);

        $this->assertIsInt($productId);

        $matches = $products->search_product_with_stock($productName, $locationId, 10);
        $matchedIds = array_map('intval', array_column($matches, 'id'));

        $this->assertContains($productId, $matchedIds);

        $matchedRow = $matches[array_search($productId, $matchedIds, true)];
        $this->assertSame('0.00', number_format((float) $matchedRow['local_stock'], 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $matchedRow['global_stock'], 2, '.', ''));
    }

    public function testStockCanBeIncreasedAndReducedToHistory(): void
    {
        $products = $this->model('Products_model', 'products_test');
        $stock = $this->model('Stock_model', 'stock');

        $locationId = $this->existingId('stock_location');
        $barcode = $this->uniqueString('STOCKBAR');

        $productId = $products->insert([
            'name' => $this->uniqueString('STOCKPRODUCT'),
            'wholesale_name' => 'Workflow Stock Product',
            'short_name' => $this->uniqueString('STOCKSHORT'),
            'producer' => 'Regression Labs',
            'supplier' => 'Regression Supplier',
            'posologie' => '',
            'toedieningsweg' => 'oral',
            'type' => $this->existingId('products_type'),
            'dead_volume' => '0.00',
            'buy_volume' => 1,
            'sell_volume' => '1.00',
            'buy_price' => '8.50',
            'buy_price_date' => date('Y-m-d'),
            'unit_buy' => 'pcs',
            'unit_sell' => 'pcs',
            'input_barcode' => $this->uniqueString('INPUTBAR'),
            'btw_buy' => 6,
            'btw_sell' => 21,
            'booking_code' => $this->existingId('booking_codes'),
            'delay' => 0,
            'comment' => '',
            'comment_admin' => null,
            'sellable' => 1,
            'discontinued' => 0,
            'backorder' => 0,
            'limit_stock' => 0,
            'vaccin' => 0,
            'vaccin_freq' => 0,
            'vaccin_disease' => null,
            'vhbcode' => '',
            'cnk' => null,
            'cti_e' => null,
            'wholesale' => $this->existingId('wholesale'),
            'usage_count' => 0,
            'is_antibiotic' => 0,
            'default_indication' => 'NONE',
            'ab_unit' => null,
            'ab_unit_volume' => null,
        ]);

        $stockId = $stock->insert([
            'product_id' => $productId,
            'eol' => '2030-12-31',
            'location' => $locationId,
            'in_price' => '8.50',
            'cat_price' => null,
            'lotnr' => $this->uniqueString('LOT'),
            'volume' => '10.00',
            'barcode' => $barcode,
            'supplier' => 'Regression Supplier',
            'verify' => null,
            'state' => STOCK_IN_USE,
            'info' => null,
        ]);

        $stock->increase_stock($productId, 5.0, $locationId, $barcode, false);
        $grown = $stock->get($stockId);

        $this->assertSame('15.00', number_format((float) $grown['volume'], 2, '.', ''));
        $this->assertSame(STOCK_IN_USE, (int) $grown['state']);

        $stock->reduce_stock($productId, 15.0, $locationId, $barcode);
        $depleted = $stock->get($stockId);

        $this->assertSame('0.00', number_format((float) $depleted['volume'], 2, '.', ''));
        $this->assertSame(STOCK_HISTORY, (int) $depleted['state']);
    }

    public function testBillCanBeCalculatedFromOpenEvents(): void
    {
        $owners = $this->model('Owners_model', 'owners_test');
        $pets = $this->model('Pets_model', 'pets_test');
        $bills = $this->model('Bills_model', 'bills_test');
        $events = $this->model('Events_model', 'events');

        $userId = $this->existingId('users');
        $locationId = $this->existingId('stock_location');

        $ownerId = $owners->insert([
            'first_name' => $this->uniqueString('BILLOWNERFN'),
            'last_name' => $this->uniqueString('BILLOWNERLN'),
            'telephone' => null,
            'mobile' => null,
            'phone2' => null,
            'phone3' => null,
            'street' => 'Invoice Street',
            'nr' => '5',
            'city' => 'Leuven',
            'main_city' => 'Leuven',
            'province' => 'Flemish Brabant',
            'zip' => '3000',
            'mail' => null,
            'msg' => '',
            'btw_nr' => null,
            'invoice_addr' => null,
            'invoice_contact' => null,
            'invoice_tel' => null,
            'debts' => 0,
            'disabled' => 0,
            'low_budget' => 0,
            'language' => 0,
            'contact' => 1,
            'last_bill' => date('Y-m-d'),
            'initial_vet' => $userId,
            'initial_loc' => $locationId,
        ]);

        $petId = $pets->insert([
            'type' => CAT,
            'name' => $this->uniqueString('BILLPET'),
            'birth' => '2021-01-01',
            'death' => 0,
            'death_date' => null,
            'breed' => null,
            'breed2' => null,
            'gender' => FEMALE,
            'color' => 'white',
            'last_weight' => '4.20',
            'lost' => 0,
            'chip' => null,
            'nr_vac_book' => null,
            'hairtype' => 'short',
            'companion' => null,
            'note' => null,
            'nutritional_advice' => null,
            'medication' => null,
            'owner' => $ownerId,
            'location' => $locationId,
            'init_vet' => $userId,
            'transfered' => 0,
        ]);

        $billId = $bills->insert([
            'owner_id' => $ownerId,
            'vet' => $userId,
            'location' => $locationId,
            'total_brut' => '0.00',
            'total_net' => '0.00',
            'BTW_0' => '0.00',
            'BTW_6' => '0.00',
            'BTW_21' => '0.00',
            'cash' => '0.00',
            'card' => '0.00',
            'transfer' => '0.00',
            'transfer_verified' => 0,
            'invoice_id' => null,
            'invoice_date' => null,
            'modified' => 0,
            'status' => BILL_DRAFT,
            'msg' => '',
            'msg_invoice' => '',
            'mail' => 0,
        ]);

        $eventId = $events->insert([
            'title' => 'Workflow invoice test',
            'anamnese' => '',
            'pet' => $petId,
            'type' => MEDICINE,
            'status' => STATUS_OPEN,
            'payment' => PAYMENT_OPEN,
            'location' => $locationId,
            'vet' => $userId,
            'vet_support_1' => 0,
            'vet_support_2' => 0,
            'report' => 0,
            'no_history' => 0,
        ]);

        $this->ci->db->insert('events_products', [
            'product_id' => $this->existingId('products'),
            'event_id' => $eventId,
            'volume' => '1.00',
            'price_net' => '10.00',
            'price_brut' => '12.10',
            'unit_price' => '10.00',
            'price_ori_net' => '10.00',
            'reduction_reason' => null,
            'btw' => 21,
            'booking' => $this->existingId('booking_codes'),
            'stock_id' => null,
        ]);

        $events->set_open_events_to_bills($ownerId, $billId);

        $updatedEvent = $events->get($eventId);
        $this->assertSame($billId, (int) $updatedEvent['payment']);

        [$totalNet, $totalBrut, $btwSummary] = $bills->calculate_bill($billId, BILL_DRAFT);
        $storedBill = $bills->get($billId);
        $billProducts = $events->all_bill_products($billId);

        $this->assertSame('10.00', number_format((float) $totalNet, 2, '.', ''));
        $this->assertSame('12.10', number_format((float) $totalBrut, 2, '.', ''));
        $this->assertSame('10.00', number_format((float) $storedBill['total_net'], 2, '.', ''));
        $this->assertSame('12.10', number_format((float) $storedBill['total_brut'], 2, '.', ''));
        $this->assertSame(BILL_PENDING, (int) $storedBill['status']);
        $this->assertSame('10.00', number_format((float) $btwSummary[21]['over'], 2, '.', ''));
        $this->assertSame('2.10', number_format((float) $btwSummary[21]['calculated'], 2, '.', ''));
        $this->assertCount(1, $billProducts);
        $this->assertSame($locationId, (int) $billProducts[0]['location']);
    }
}
