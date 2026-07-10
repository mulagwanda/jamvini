<?php

namespace Plugins\TzRegistryRegistrar\src\Models;

use Illuminate\Database\Eloquent\Model;

class DomainRegistrarOperation extends Model
{
    protected $table = 'domain_registrar_operations';

    protected $fillable = [
        'domain_id',
        'registrar_slug',
        'domain_name',
        'operation',
        'status',
        'epp_code',
        'client_transaction_id',
        'server_transaction_id',
        'message',
        'request_payload',
        'response_payload',
        'completed_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'completed_at' => 'datetime',
    ];
}
