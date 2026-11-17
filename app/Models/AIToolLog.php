<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIToolLog extends Model
{
    use HasFactory;

    protected $table = 'ai_tool_logs';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'tool_name',
        'parameters',
        'result',
        'status',
        'execution_time',
    ];

    protected $casts = [
        'parameters' => 'array',
        'result' => 'array',
        'execution_time' => 'float',
    ];

    public function conversation()
    {
        return $this->belongsTo(AIConversation::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
