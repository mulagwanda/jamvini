<?php

namespace Plugins\AiAssistant\src\Models;

use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    protected $table = 'ai_assistant_messages';

    protected $fillable = [
        'conversation_id', 'role', 'message', 'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
