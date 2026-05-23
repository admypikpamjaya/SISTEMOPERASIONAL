<?php

namespace App\Models\Asset;

use App\Enums\Asset\AssetCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetImportBatch extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'category' => AssetCategory::class,
        'processed_rows' => 'integer',
        'imported_rows' => 'integer',
        'sheet_count' => 'integer',
        'sheet_names' => 'array',
        'metadata' => 'array',
    ];

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
