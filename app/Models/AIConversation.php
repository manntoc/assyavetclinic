<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIConversation extends Model
{
    use HasFactory;

    protected $table = 'ai_conversations';

    protected $fillable = [
        'user_id',
        'title',
        'status',
        'state',
        'context_data',
    ];

    protected $casts = [
        'context_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(AIMessage::class, 'conversation_id');
    }

    public function toolLogs()
    {
        return $this->hasMany(AIToolLog::class, 'conversation_id');
    }
}
