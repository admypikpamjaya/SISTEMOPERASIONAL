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

    /*
    |--------------------------------------------------------------------------
    | ADMIN – COMMUNICATION & BILLING (PHASE 6.2)
    |--------------------------------------------------------------------------
    | STATUS:
    | ✔ Routing & Controller ready
    | ✔ Dummy UI
    | ✔ Shared access (Admin / Finance / Superadmin)
    | ✖ Permission detail (PHASE 6.3+)
    */

    [
        'label' => 'Announcements',
        'icon'  => 'fas fa-bullhorn',
        'route' => 'admin.announcements.index',
    ],

    [
        'label' => 'Blast Message',
        'icon'  => 'fas fa-paper-plane',
        'route' => 'admin.blast.index',
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
