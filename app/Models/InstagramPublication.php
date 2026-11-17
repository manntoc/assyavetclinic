<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'instagram_content_id',
        'platform',
        'external_post_id',
        'status',
        'published_at',
        'error_message',
        'response_metadata',
    ];

    protected $casts = [
        'published_at'      => 'datetime',
        'response_metadata' => 'array',
    ];

    public function content()
    {
        return $this->belongsTo(InstagramContent::class, 'instagram_content_id');
    }
}
