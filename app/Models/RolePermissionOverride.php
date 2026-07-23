<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermissionOverride extends Model
{
    protected $fillable = [
        'role',
        'permission',
        'allowed',
        'updated_by',
    ];

    protected $casts = [
        'allowed' => 'boolean',
    ];
}
