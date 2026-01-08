<?php

namespace App\Models\Log;

use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;
use App\Models\Asset\Asset;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory, HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'status' => AssetMaintenanceReportStatus::class
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
