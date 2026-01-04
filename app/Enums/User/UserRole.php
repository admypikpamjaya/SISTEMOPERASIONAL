<?php 

namespace App\Enums\User;

enum UserRole: string 
{
    case IT_SUPPORT = 'IT Support';
    case ASSET_MANAGER = 'Asset Manager';
    case FINANCE = 'Finance';
}