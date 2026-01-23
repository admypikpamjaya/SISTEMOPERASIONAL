<?php

return [
    [
        'label' => 'Dashboard',
        'icon'  => 'fas fa-tachometer-alt',
        'route' => 'dashboard.index',
    ],

    [
        'module_name' => 'asset_management',
        'label'       => 'Asset Management',
        'icon'        => 'fas fa-boxes',
        'route'       => 'asset-management.index',
    ],

    [
        'module_name' => 'maintenance_report',
        'label'       => 'Maintenance Report',
        'icon'        => 'fas fa-tools',
        'route'       => 'maintenance-report.index',
    ],

    [
        'module_name' => 'user_management',
        'label'       => 'User Database',
        'icon'        => 'fas fa-users',
        'route'       => 'user-database.index',
    ],

    [
        'label' => 'Announcements',
        'icon'  => 'fas fa-bullhorn',
        'route' => 'admin.announcements.index',
    ],

    /*
    |--------------------------------------------------------------------------
    | BLAST MESSAGE (PARENT + DROPDOWN) — FIXED
    |--------------------------------------------------------------------------
    */
    [
        'label' => 'Blast Message',
        'icon'  => 'fas fa-paper-plane',
        'route' => 'admin.blast.index', // ✅ ROUTE VALID
        'children' => [
            [
                'label' => 'WhatsApp Blast',
                'icon'  => 'fab fa-whatsapp',
                'route' => 'admin.blast.whatsapp',
            ],
            [
                'label' => 'Email Blast',
                'icon'  => 'fas fa-envelope',
                'route' => 'admin.blast.email',
            ],
        ],
    ],

    [
        'label' => 'Reminders',
        'icon'  => 'fas fa-bell',
        'route' => 'admin.reminders.index',
    ],

    [
        'label' => 'Billing',
        'icon'  => 'fas fa-file-invoice-dollar',
        'route' => 'admin.billings.index',
    ],

    [
        'label' => 'Log out',
        'icon'  => 'fas fa-sign-out-alt',
        'route' => 'logout',
    ],
];