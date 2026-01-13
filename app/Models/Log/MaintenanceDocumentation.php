<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class MaintenanceDocumentation extends Model
{
    use HasFactory, HasUuids;
    
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public static function store(UploadedFile $file)
    {
        $manager = new ImageManager(new Driver());

        $image = $manager->read($file->getRealPath());
        $image->scale(width: 1920);

        $filename = uniqid() . '.jpg';
        $path = 'maintenance-documentation/' . $filename;

        $encoded = $image->toJpeg(75);

        Storage::disk('public')->put($path, (string) $encoded);
        
        return $path;
    }

    public function getUrlAttribute(): string 
    {
        return asset('storage/' . $this->document_path);
    }

    public function maintenanceLog()
    {
        return $this->belongsTo(MaintenanceLog::class);
    }
}
