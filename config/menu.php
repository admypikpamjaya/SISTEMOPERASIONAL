<?php 

return [
    [
        'label' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'route' => 'dashboard.index',
    ],
    [
        'module_name' => 'asset_management',
        'label' => 'Asset Managemement',
        'icon' => 'fas fa-boxes',
        'route' => 'asset-management.index',
    ],
    [
        'module_name' => 'maintenance_report',
        'label' => 'Maintenance Report',
        'icon' => 'fas fa-tools',
        'route' => 'maintenance-report.index',
    ],
    [
        'module_name' => 'user_management', 
        'label' => 'User Database',
        'icon' => 'fas fa-users',
        'route' => 'user-database.index'
    ],
    [
        'label' => 'Log out',
        'icon' => 'fas fa-sign-out-alt',
        'route' => 'logout'
    ]
];