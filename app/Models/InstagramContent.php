<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_konten',
        'source_type',
        'source_id',
        'topic',
        'content_type',
        'tone',
        'template_style',
        'slide_count',
        'status',
        'caption',
        'hashtags',
        'ai_raw_response',
        'brand_snapshot',
        'created_by',
        'approved_by',
        'approved_at',
        'publish_error',
    ];

    protected $casts = [
        'hashtags'        => 'array',
        'ai_raw_response' => 'array',
        'brand_snapshot'  => 'array',
        'approved_at'     => 'datetime',
    ];

    // Status labels
    public static array $statusLabels = [
        'draft'     => 'Draft',
        'generated' => 'AI Generated',
        'review'    => 'Menunggu Review',
        'approved'  => 'Disetujui',
        'published' => 'Dipublikasikan',
        'failed'    => 'Gagal',
    ];

    public static array $statusColors = [
        'draft'     => '#64748b',
        'generated' => '#0284c7',
        'review'    => '#f59e0b',
        'approved'  => '#10b981',
        'published' => '#059669',
        'failed'    => '#ef4444',
    ];

    public function slides()
    {
        return $this->hasMany(InstagramContentSlide::class)->orderBy('slide_number');
    }

    public function publications()
    {
        return $this->hasMany(InstagramPublication::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::$statusColors[$this->status] ?? '#64748b';
    }

    // Generate unique kode_konten
    public static function generateKode(): string
    {
        do {
            $kode = 'IG' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 7));
        } while (self::where('kode_konten', $kode)->exists());

        return $kode;
    }
}
