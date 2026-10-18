<?php

/**
 * T345 - GET routes that are safe to crawl as admin: they render a page, change nothing and call nothing external.
 * Ids are the seeded ones (tests/Support/Fixtures.php). The CI4 port reuses this list as it is.
 *
 * Deliberately NOT in the list:
 *  - pricing/man (writes buy_price from POST, blank on GET);
 *  - GET routes that change data: events/new_event, events/del, events/lock, events/link_lab, pets/delete, pets/del_weight,
 *    pets/remove_avatar, pets/change_owner_complete, invoice/bill/{owner} and invoice/get_bill/{open bill} (both open and
 *    recalculate a bill), invoice/verify, sticky/delete, stock/delete_stock, stock/write_off*, stock/clear_error,
 *    vaccine/remove, vamreg lock/unlock/remove/restore/reset/refresh/post_all*, wholesale accept/ignore/unignore,
 *    admin/*_rm, products/delete_product, products/set_backorder, pricing/delete_proc, pricing/rm_prod_price,
 *    breeds/rebuild_frequenty, events_report/* (history, type, update), files/delete_file, lab/delete, tooth/store|update;
 *  - downloads: export/* (pdf, csv, owners, kluwer), reports csv variants, files/get_file|preview, pets/avatar_file|export,
 *    vaccine/export*, lab/print, vet/avatar;
 *  - AJAX only endpoints (empty or JSON body): search_*, get_*, ajax_*, check_chip, owners/phone|addr;
 *  - command line only: cli/*, upgrade/*; external services: tests/*, vamreg pages that call the API.
 *
 * @return list<string>
 */
return [
    // home, search, help
    '',
    'search?search_query=janssens',
    'help/admin',
    'help/vet',
    'vet/profile',
    'vet/pub/2',

    // owners and pets
    'owners/add',
    'owners/edit/1001',
    'owners/detail/1001',
    'owners/invoices/1001',
    'owners/products/1001',
    'pets',
    'pets/add/1001',
    'pets/edit/2001',
    'pets/fiche/2001',
    'pets/fiche/2004',
    'pets/history_weight/2001',
    'breeds',
    'breeds/add',
    'breeds/edit/2',
    'sticky',
    'rx/list/2001',
    'tooth/fiche/2001',
    'tooth/fiche/2002',
    'tooth/history/2001',
    'vaccine',
    'vaccine/fiche/2001',
    'vaccine/detail/11001',

    // events, billing
    'events/event/6001',
    'events/event/6002',
    'events/event/6003',
    'invoice',
    'invoice/get_bill/7001',
    'admin_invoice/edit_bill/7001',

    // lab
    'lab',
    'lab/pending',
    'lab/detail/10001',
    'lab/list_lab/2001',

    // products, stock, pricing
    'products',
    'products/profile/4001',
    'products/new',
    'products/product_list/1',
    'products/product/4001',
    'pricing/prod',
    'pricing/prod/4001',
    'pricing/proc',
    'pricing/proc_edit/3001',
    'stock/stock_detail/4001',
    'stock/stock_detail/4002',
    'stock/add_stock',
    'stock/expired_stock',
    'stock/verify_stock',
    'stock/stock_check',
    'stock/move',
    'limits/order',
    'limits/global',
    'limits/local/1',
    'wholesale',
    'wholesale/pricing',
    'wholesale/unlinked',
    'wholesale/delivery_overview',

    // reports, statistics, logs, accounting
    'report',
    'reports/accounting',
    'reports/stock_list',
    'reports/clients_list',
    'reports/usage/4001',
    'reports/products',
    'reports/register_in',
    'reports/register_out',
    'reports/revenue',
    'stats',
    'accounting/dashboard',
    'logs/write_off',
    'logs/write_off_invoice',
    'logs/product/4001',
    'logs/nlog',
    'logs/delivery',
    'logs/software_version',
    'export',

    // admin
    'admin/settings',
    'admin/locations',
    'admin/booking',
    'admin/product_types',
    'admin/product_labels',
    'member',
    'member/create_user',
    'member/edit_user/2',
    'restore/booking',
    'restore/locations',
    'restore/procedures',
    'tools',
    'tools/duplicate_clients',
    'tools/duplicate_phones',
    'tools/duplicate_chips',
    'tools/show_api_keys',
    'vamreg',
];
