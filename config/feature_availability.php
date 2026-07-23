<?php

return [
    'setting_key' => 'system.feature_availability',

    'features' => [
        'dashboard' => [
            'name' => 'Dashboard',
            'description' => 'Halaman ringkasan utama aplikasi.',
            'route_patterns' => ['dashboard.*'],
            'menu_routes' => ['dashboard.index'],
        ],
        'discussion' => [
            'name' => 'Diskusi',
            'description' => 'Fitur percakapan internal web.',
            'route_patterns' => ['discussion.*'],
            'menu_routes' => ['discussion.index'],
        ],
        'asset_management' => [
            'name' => 'Asset Management',
            'description' => 'Master data aset, kategori aset, dan laporan maintenance.',
            'route_patterns' => ['asset-management.*', 'maintenance-report.*'],
            'menu_routes' => ['asset-management.index', 'maintenance-report.index'],
        ],
        'user_management' => [
            'name' => 'User Management',
            'description' => 'Database user, login history, dan reset user.',
            'route_patterns' => ['user-database.*'],
            'menu_routes' => ['user-database.index', 'user-database.login-history'],
        ],
        'admin_blast' => [
            'name' => 'Blasting WhatsApp & Email',
            'description' => 'Kirim blast WhatsApp/email, kelola penerima, template, dan announcement.',
            'route_patterns' => ['admin.blast.*', 'admin.announcements.*'],
            'menu_routes' => ['admin.blast.index', 'admin.blast.whatsapp', 'admin.blast.email', 'admin.announcements.index'],
        ],
        'admin_reminder' => [
            'name' => 'Reminder',
            'description' => 'Pengingat dan notifikasi internal.',
            'route_patterns' => ['admin.reminders.*'],
            'menu_routes' => ['admin.reminders.index'],
        ],
        'website_theme' => [
            'name' => 'Tema Website',
            'description' => 'Pengaturan tema dan tampilan website.',
            'route_patterns' => ['admin.theme.*'],
            'menu_routes' => ['admin.theme.index'],
        ],
        'finance' => [
            'name' => 'Finance',
            'description' => 'Dashboard keuangan, kategori, laporan, invoice, dan general ledger.',
            'route_patterns' => ['finance.*'],
            'menu_routes' => ['finance.dashboard'],
        ],
        'system_management' => [
            'name' => 'Sistem Management',
            'description' => 'Console root untuk audit, maintenance, fitur, dan kontrol sistem.',
            'route_patterns' => ['system-management.*'],
            'menu_routes' => ['system-management.index'],
            'locked' => true,
        ],
    ],
];
