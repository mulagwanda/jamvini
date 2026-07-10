<?php

namespace Plugins\Support\src\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    protected $table = 'support_ticket_replies';

    protected $fillable = [
        'ticket_id', 'client_id', 'admin_id', 'author_type', 'message', 'is_private', 'attachments',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'attachments' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function client()
    {
        return $this->belongsTo(\Plugins\Clients\src\Models\Client::class);
    }

    public function admin()
    {
        return $this->belongsTo(\App\Models\Admin::class);
    }
}
