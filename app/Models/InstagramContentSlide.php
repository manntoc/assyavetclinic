<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramContentSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'instagram_content_id',
        'slide_number',
        'purpose',
        'layout',
        'headline',
        'body',
        'image_path',
        'rendered_path',
        'design_data',
    ];

    protected $casts = [
        'design_data' => 'array',
    ];

    public function content()
    {
        return $this->belongsTo(InstagramContent::class, 'instagram_content_id');
    }

    /**
     * Return public URL for the slide image (uploaded or rendered).
     */
    public function getImageUrlAttribute(): ?string
    {
        $path = $this->rendered_path ?: $this->image_path;
        if (!$path) return null;

        // If already an absolute URL (e.g. http://...)
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;

        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Purpose-to-label mapping.
     */
    public static array $purposeLabels = [
        'hook'     => 'Hook / Headline',
        'problem'  => 'Masalah / Kasus',
        'solution' => 'Edukasi / Solusi',
        'tips'     => 'Tips / Info Tambahan',
        'cta'      => 'Call to Action',
    ];
}
