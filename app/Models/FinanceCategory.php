<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FinanceCategory extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const TYPE_SINGLE = 'single';
    public const TYPE_GROUP = 'group';
    public const SOURCE_STATIC = 'static';
    public const SOURCE_CUSTOM = 'custom';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'finance_categories';

    protected $fillable = [
        'name',
        'description',
        'status',
        'category_type',
        'source_type',
        'sort_order',
        'created_by',
    ];

    public const UPDATED_AT = 'updated_at';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Disembunyikan',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_SINGLE => 'Berdiri Sendiri',
            self::TYPE_GROUP => 'Gabungan',
        ];
    }

    public static function sourceOptions(): array
    {
        return [
            self::SOURCE_STATIC => 'Statik',
            self::SOURCE_CUSTOM => 'Custom',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'finance_category_members',
            'parent_category_id',
            'member_category_id'
        )->withTimestamps();
    }

    public function memberOf(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'finance_category_members',
            'member_category_id',
            'parent_category_id'
        )->withTimestamps();
    }

    public function isGroup(): bool
    {
        return (string) $this->category_type === self::TYPE_GROUP;
    }
}
