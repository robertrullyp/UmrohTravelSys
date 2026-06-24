<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu Review',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_REJECTED => 'Ditolak',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    protected $fillable = [
        'booking_number',
        'public_token',
        'umrah_package_id',
        'schedule_id',
        'customer_name',
        'whatsapp',
        'email',
        'pilgrims_count',
        'notes',
        'status',
        'admin_notes',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'approved_at',
        'quota_deducted_at',
        'quota_restored_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'quota_deducted_at' => 'datetime',
            'quota_restored_at' => 'datetime',
        ];
    }

    public function umrahPackage(): BelongsTo
    {
        return $this->belongsTo(UmrahPackage::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getMaskedWhatsappAttribute(): string
    {
        $digits = preg_replace('/\D+/', '', $this->whatsapp) ?? '';

        if (strlen($digits) <= 7) {
            return $digits;
        }

        return substr($digits, 0, 4).str_repeat('*', max(strlen($digits) - 7, 3)).substr($digits, -3);
    }

    public function getMaskedEmailAttribute(): ?string
    {
        if (blank($this->email) || ! str_contains($this->email, '@')) {
            return null;
        }

        [$name, $domain] = explode('@', $this->email, 2);

        return substr($name, 0, 1).str_repeat('*', max(strlen($name) - 1, 3)).'@'.$domain;
    }
}
