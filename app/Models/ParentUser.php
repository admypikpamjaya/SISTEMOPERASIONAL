<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParentUser extends Model
{
    use HasFactory;

    protected $table = 'parent_users';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];

    /* ================= RELATION ================= */

    public function billings()
    {
        return $this->hasMany(Billing::class, 'parent_id');
    }
}
