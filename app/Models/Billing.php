<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $fillable = [
        'parent_id',
        'title',
        'amount',
        'status',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function parent()
    {
        return $this->belongsTo(ParentUser::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
