<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Recipient extends Model
{
    protected $table = 'recipients';

    protected $fillable = [
        'nama_siswa',
        'kelas',
        'nama_wali',
        'email',
        'phone',
        'note',
    ];
}