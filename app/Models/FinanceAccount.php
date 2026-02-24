<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceAccount extends Model
{
    use HasFactory, HasUuids;

    public const TYPE_PIUTANG = 'PIUTANG';
    public const TYPE_PASIVA_TERKINI = 'PASIVA_TERKINI';
    public const TYPE_HUTANG = 'HUTANG';
    public const TYPE_HUTANG_TIDAK_LANCAR = 'HUTANG_TIDAK_LANCAR';
    public const TYPE_EKUITAS = 'EKUITAS';
    public const TYPE_PENGHASILAN = 'PENGHASILAN';
    public const TYPE_PENGELUARAN = 'PENGELUARAN';
    public const TYPE_PENGHASILAN_LAINNYA = 'PENGHASILAN_LAINNYA';
    public const TYPE_PENGHASILAN_TAHUN_TERKINI = 'PENGHASILAN_TAHUN_TERKINI';

    public const TYPE_CLASS_MAP = [
        self::TYPE_PIUTANG => 1,
        self::TYPE_PASIVA_TERKINI => 2,
        self::TYPE_HUTANG => 2,
        self::TYPE_HUTANG_TIDAK_LANCAR => 2,
        self::TYPE_EKUITAS => 3,
        self::TYPE_PENGHASILAN => 4,
        self::TYPE_PENGELUARAN => 5,
        self::TYPE_PENGHASILAN_LAINNYA => 8,
        self::TYPE_PENGHASILAN_TAHUN_TERKINI => 9,
    ];

    public const TYPE_LABELS = [
        self::TYPE_PIUTANG => 'Piutang',
        self::TYPE_PASIVA_TERKINI => 'Pasiva Terkini',
        self::TYPE_HUTANG => 'Hutang',
        self::TYPE_HUTANG_TIDAK_LANCAR => 'Hutang Tidak Lancar',
        self::TYPE_EKUITAS => 'Ekuitas',
        self::TYPE_PENGHASILAN => 'Penghasilan',
        self::TYPE_PENGELUARAN => 'Pengeluaran',
        self::TYPE_PENGHASILAN_LAINNYA => 'Penghasilan Lainnya',
        self::TYPE_PENGHASILAN_TAHUN_TERKINI => 'Penghasilan Tahun Terkini',
    ];

    public const CLASS_LABELS = [
        1 => 'Piutang',
        2 => 'Pasiva Terkini / Hutang / Hutang Tidak Lancar',
        3 => 'Ekuitas',
        4 => 'Penghasilan',
        5 => 'Pengeluaran',
        8 => 'Penghasilan Lainnya',
        9 => 'Penghasilan Tahun Terkini',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'finance_accounts';

    protected $fillable = [
        'code',
        'name',
        'type',
        'class_no',
        'is_active',
        'created_by',
        'updated_by',
        'meta',
    ];

    protected $casts = [
        'class_no' => 'integer',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public static function allowedTypes(): array
    {
        return array_keys(self::TYPE_CLASS_MAP);
    }

    public static function classOrder(): array
    {
        return [1, 2, 3, 4, 5, 8, 9];
    }

    public static function classForType(string $type): int
    {
        return (int) (self::TYPE_CLASS_MAP[$type] ?? 0);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? (string) $this->type;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FinanceAccountLog::class, 'finance_account_id');
    }
}
