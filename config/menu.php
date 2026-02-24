<?php

return [
    [
        'label' => 'Dashboard',
        'icon'  => 'fas fa-tachometer-alt',
        'route' => 'dashboard.index',
    ],

    [
        'label' => 'Discussion',
        'icon'  => 'fas fa-comments',
        'route' => 'discussion.index',
    ],

    [
        'module_name' => 'asset_management',
        'label'       => 'Asset Management',
        'icon'        => 'fas fa-boxes',
        'route'       => 'asset-management.index',
        'children'    => [
            [
                'module_name' => 'asset_management',
                'label'       => 'Kelola Aset',
                'icon'        => 'fas fa-list',
                'route'       => 'asset-management.index',
            ],
            [
                'module_name' => 'asset_management',
                'label'       => 'Register Asset',
                'icon'        => 'fas fa-file-signature',
                'route'       => 'asset-management.register-form',
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
        ],
    ],

    [
        'module_name' => 'admin_blast',
        'label' => 'Blast Message',
        'icon'  => 'fas fa-paper-plane',
        'route' => 'admin.blast.index',
        'hide_on_routes' => ['finance.*'],
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
            [
                'label' => 'Recipient Data',
                'icon'  => 'fas fa-users',
                'route' => 'admin.blast.recipients.index',
                'module_name' => 'admin_blast',
            ],
            [
                'label' => 'Announcement',
                'icon'  => 'fas fa-bullhorn',
                'route' => 'admin.announcements.index',
            ],
        ],
        
    ],
 [
        'module_name' => 'admin_reminder',
        'label' => 'Reminders',
        'icon'  => 'fas fa-bell',
        'route' => 'admin.reminders.index',
    ],
   

    [
        'module_name' => 'finance_report',
        'label'       => 'Finance',
        'icon'        => 'fas fa-chart-line',
        'route'       => 'finance.dashboard',
        'children'    => [
            [
                'label' => 'Finance Dashboard',
                'icon'  => 'fas fa-chart-pie',
                'route' => 'finance.dashboard',
            ],
            [
                'label' => 'Asset Depreciation',
                'icon'  => 'fas fa-calculator',
                'route' => 'finance.depreciation.index',
            ],
            [
                'label' => 'Input Finance Report',
                'icon'  => 'fas fa-file-invoice',
                'route' => 'finance.report.index',
            ],
            [
                'label' => 'Bagan Akun',
                'icon'  => 'fas fa-sitemap',
                'route' => 'finance.accounts.index',
            ],
            [
                'label' => 'Snapshot Report',
                'icon'  => 'fas fa-book',
                'route' => 'finance.report.snapshots',
            ],
            [
                'label' => 'Invoice',
                'icon'  => 'fas fa-file-invoice-dollar',
                'route' => 'finance.invoice.index',
            ],
        ],
    ],

    [
        'label' => 'Log out',
        'icon'  => 'fas fa-sign-out-alt',
        'route' => 'logout',
    ],
];
