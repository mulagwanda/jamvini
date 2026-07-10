<?php

namespace Plugins\AiAssistant\src\Models;

use Illuminate\Database\Eloquent\Model;
use Plugins\Clients\src\Models\Client;
use Plugins\Support\src\Models\Ticket;

class AiConversation extends Model
{
    protected $table = 'ai_assistant_conversations';

    protected $fillable = [
        'public_token', 'client_id', 'visitor_name', 'visitor_email', 'page_url', 'page_title',
        'country_code', 'country_name', 'status', 'support_ticket_id', 'escalated_at',
        'last_staff_reply_at', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'escalated_at' => 'datetime',
        'last_staff_reply_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function messages()
    {
        return $this->hasMany(AiMessage::class, 'conversation_id');
    }

    public function supportTicket()
    {
        return $this->belongsTo(Ticket::class, 'support_ticket_id');
    }
}
