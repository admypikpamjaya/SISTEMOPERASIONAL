<?php

namespace App\Enums\User;

enum UserRole: string
{
    case USER = 'User';
    case ADMIN = 'Admin';

    case IT_SUPPORT = 'IT Support';
    case ASSET_MANAGER = 'Asset Manager';
    case FINANCE = 'Finance';
    case PEMBINA = 'Pembina';
    case QC = 'QC';
}
