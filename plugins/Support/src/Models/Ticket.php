<?php

namespace Plugins\Support\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $table = 'support_tickets';

    protected $fillable = [
        'ticket_number', 'client_id', 'assigned_to', 'department', 'subject', 'status',
        'priority', 'source', 'related_service_id', 'metadata', 'last_reply_at', 'closed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_reply_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(\Plugins\Clients\src\Models\Client::class);
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at');
    }

    public function publicReplies()
    {
        return $this->hasMany(TicketReply::class)->where('is_private', false)->orderBy('created_at');
    }
}
