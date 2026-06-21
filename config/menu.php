<?php

use App\Enums\User\UserRole;

$withFallbackLabel = function (array $item) use (&$withFallbackLabel): array {
    if (!array_key_exists('label', $item) && !empty($item['label_key'])) {
        $item['label'] = (string) $item['label_key'];
    }

    if (!empty($item['children']) && is_array($item['children'])) {
        $item['children'] = array_map($withFallbackLabel, $item['children']);
    }

    return $item;
};

return array_map($withFallbackLabel, [
    [
        'label_key' => 'app.menu.dashboard',
        'icon'  => 'fas fa-tachometer-alt',
        'route' => 'dashboard.index',
    ],

    [
        'label_key' => 'app.menu.discussion',
        'icon'  => 'fas fa-comments',
        'route' => 'discussion.index',
    ],

    [
        'module_name' => 'asset_management',
        'label_key'   => 'app.menu.asset_master_data',
        'icon'        => 'fas fa-list',
        'route'       => 'asset-management.index',
        'active_routes' => ['asset-management.index', 'asset-management.ac.*', 'asset-management.building-infrastructure.*', 'asset-management.electronic.*', 'asset-management.room-inventory.*', 'asset-management.vehicle.*', 'asset-management.computer.*'],
        'roles'       => [
            UserRole::IT_SUPPORT->value,
            UserRole::ASSET_MANAGER->value,
            UserRole::PEMBINA->value,
            UserRole::QC->value,
        ],
        'children'    => [
            [
                'module_name' => 'asset_management',
                'label_key'   => 'app.menu.asset_category_ac',
                'icon'        => 'fas fa-snowflake',
                'route'       => 'asset-management.ac.index',
                'active_routes' => ['asset-management.ac.*'],
            ],
            [
                'module_name' => 'asset_management',
                'label_key'   => 'app.menu.asset_category_building_infrastructure',
                'icon'        => 'fas fa-building',
                'route'       => 'asset-management.building-infrastructure.index',
                'active_routes' => ['asset-management.building-infrastructure.*'],
            ],
            [
                'module_name' => 'asset_management',
                'label_key'   => 'app.menu.asset_category_electronic',
                'icon'        => 'fas fa-desktop',
                'route'       => 'asset-management.electronic.index',
                'active_routes' => ['asset-management.electronic.*'],
            ],
            [
                'module_name' => 'asset_management',
                'label_key'   => 'app.menu.asset_category_room_inventory',
                'icon'        => 'fas fa-chair',
                'route'       => 'asset-management.room-inventory.index',
                'active_routes' => ['asset-management.room-inventory.*'],
            ],
            [
                'module_name' => 'asset_management',
                'label_key'   => 'app.menu.asset_category_vehicle',
                'icon'        => 'fas fa-car',
                'route'       => 'asset-management.vehicle.index',
                'active_routes' => ['asset-management.vehicle.*'],
            ],
            [
                'module_name' => 'asset_management',
                'label_key'   => 'app.menu.asset_category_computer',
                'icon'        => 'fas fa-desktop',
                'route'       => 'asset-management.computer.index',
                'active_routes' => ['asset-management.computer.*'],
            ],
        ],
    ],

    [
        'module_name' => 'asset_management',
        'label_key'   => 'app.menu.asset_management',
        'icon'        => 'fas fa-boxes',
        'route'       => 'maintenance-report.index',
        'roles'       => [
            UserRole::IT_SUPPORT->value,
            UserRole::ASSET_MANAGER->value,
            UserRole::PEMBINA->value,
            UserRole::QC->value,
        ],
        'children'    => [
            [
                'module_name' => 'maintenance_report',
                'label_key'   => 'app.menu.maintenance_report',
                'icon'        => 'fas fa-tools',
                'route'       => 'maintenance-report.index',
            ],
            [
                'module_name' => 'user_management',
                'label_key'   => 'app.menu.user_database',
                'icon'        => 'fas fa-users',
                'route'       => 'user-database.index',
            ],
            [
                'module_name' => 'user_management',
                'label_key'   => 'app.menu.login_history',
                'icon'        => 'fas fa-history',
                'route'       => 'user-database.login-history',
                'roles'       => [UserRole::ADMIN->value, UserRole::IT_SUPPORT->value],
            ],
        ],
    ],

    [
        'module_name' => 'admin_blast',
        'label_key' => 'app.menu.blast_message',
        'icon'  => 'fas fa-paper-plane',
        'route' => 'admin.blast.index',
        'active_routes' => ['admin.blast.*', 'admin.announcements.*'],
        'open_for_roles' => [UserRole::IT_SUPPORT->value],
        'children' => [
            [
                'label_key' => 'app.menu.whatsapp_blast',
                'icon'  => 'fab fa-whatsapp',
                'route' => 'admin.blast.whatsapp',
            ],
            [
                'label_key' => 'app.menu.arrears',
                'icon'  => 'fas fa-money-bill-wave',
                'route' => 'admin.blast.tunggakan.index',
                'module_name' => 'admin_blast',
                'active_routes' => ['admin.blast.tunggakan.*'],
            ],
            [
                'label_key' => 'app.menu.manage_phone',
                'icon'  => 'fas fa-mobile-alt',
                'route' => 'admin.blast.whatsapp.manage',
                'module_name' => 'admin_blast',
                'roles' => [UserRole::IT_SUPPORT->value],
                'active_routes' => ['admin.blast.whatsapp.manage', 'admin.blast.whatsapp.gateway-*'],
            ],
            [
                'label_key' => 'app.menu.email_blast',
                'icon'  => 'fas fa-envelope',
                'route' => 'admin.blast.email',
            ],
            [
                'label_key' => 'app.menu.recipient_data',
                'icon'  => 'fas fa-users',
                'route' => 'admin.blast.recipients.index',
                'module_name' => 'admin_blast',
                'active_routes' => [
                    'admin.blast.recipients.index',
                    'admin.blast.recipients.create',
                    'admin.blast.recipients.edit',
                ],
            ],
            [
                'label_key' => 'app.menu.recipient_koperasi',
                'icon'  => 'fas fa-id-badge',
                'route' => 'admin.blast.recipients.employees.index',
                'module_name' => 'admin_blast',
                'active_routes' => ['admin.blast.recipients.employees.*'],
            ],
            [
                'label_key' => 'app.menu.recipient_ypik',
                'icon'  => 'fas fa-address-card',
                'route' => 'admin.blast.recipients.employees-ypik.index',
                'module_name' => 'admin_blast',
                'active_routes' => ['admin.blast.recipients.employees-ypik.*'],
            ],
            [
                'label_key' => 'app.menu.recipient_pamjaya',
                'icon'  => 'fas fa-id-card',
                'route' => 'admin.blast.recipients.employees-ypik-pamjaya.index',
                'module_name' => 'admin_blast',
                'active_routes' => ['admin.blast.recipients.employees-ypik-pamjaya.*'],
            ],
            [
                'label_key' => 'app.menu.template_blast',
                'icon'  => 'fas fa-layer-group',
                'route' => 'admin.blast.templates.index',
                'module_name' => 'blast_template',
                'active_routes' => ['admin.blast.templates.*'],
            ],
            [
                'label_key' => 'app.menu.announcement',
                'icon'  => 'fas fa-bullhorn',
                'route' => 'admin.announcements.index',
                'active_routes' => ['admin.announcements.*'],
            ],
        ],
        
    ],
 [
        'module_name' => 'admin_reminder',
        'label_key' => 'app.menu.reminders',
        'icon'  => 'fas fa-bell',
        'route' => 'admin.reminders.index',
    ],
   

    [
        'module_name' => 'finance_report',
        'label_key'   => 'app.menu.finance',
        'icon'        => 'fas fa-chart-line',
        'route'       => 'finance.dashboard',
        'children'    => [
            [
                'label_key' => 'app.menu.finance_dashboard',
                'icon'  => 'fas fa-chart-pie',
                'route' => 'finance.dashboard',
            ],
            [
                'label_key' => 'app.menu.finance_categories',
                'icon'  => 'fas fa-tags',
                'route' => 'finance.categories.index',
                'roles' => [UserRole::IT_SUPPORT->value],
            ],
            [
                'label_key' => 'app.menu.asset_depreciation',
                'icon'  => 'fas fa-calculator',
                'route' => 'finance.depreciation.index',
            ],
            [
                'label_key' => 'app.menu.asset_management',
                'icon'  => 'fas fa-boxes',
                'route' => 'asset-management.index',
                'module_name' => 'asset_management',
                'roles' => [UserRole::FINANCE->value],
                'active_routes' => ['asset-management.*'],
            ],
            [
                'label_key' => 'app.menu.input_finance_report',
                'icon'  => 'fas fa-file-invoice',
                'route' => 'finance.report.index',
            ],
            [
                'label_key' => 'app.menu.balance_sheet',
                'icon'  => 'fas fa-balance-scale',
                'route' => 'finance.report.balance-sheet',
                'module_name' => 'finance_balance_sheet',
            ],
            [
                'label_key' => 'app.menu.profit_loss',
                'icon'  => 'fas fa-chart-area',
                'route' => 'finance.report.profit-loss',
                'module_name' => 'finance_profit_loss',
            ],
            [
                'label_key' => 'app.menu.general_ledger',
                'icon'  => 'fas fa-book-open',
                'route' => 'finance.report.general-ledger',
                'module_name' => 'finance_general_ledger',
            ],
            [
                'label_key' => 'app.menu.chart_of_accounts',
                'icon'  => 'fas fa-sitemap',
                'route' => 'finance.accounts.index',
            ],
            [
                'label_key' => 'app.menu.snapshot_report',
                'icon'  => 'fas fa-book',
                'route' => 'finance.report.snapshots',
            ],
            [
                'label_key' => 'app.menu.invoice',
                'icon'  => 'fas fa-file-invoice-dollar',
                'route' => 'finance.invoice.index',
            ],
        ],
    ],

    [
        'label_key' => 'app.menu.logout',
        'icon'  => 'fas fa-sign-out-alt',
        'route' => 'logout',
    ],
]);
