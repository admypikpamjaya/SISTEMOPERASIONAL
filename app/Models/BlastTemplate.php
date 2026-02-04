<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlastTemplate extends Model
{
    use HasFactory;

    protected $table = 'blast_message_templates';

    protected $fillable = [
        'name',
        'channel', // email | whatsapp
        'content',
        'created_by',
    ];
}
