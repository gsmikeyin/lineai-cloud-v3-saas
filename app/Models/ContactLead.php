<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactLead extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'name',
        'email',
        'company',
        'phone',
        'message',
        'status',
        'source',
        'contacted_at',
        'meta',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'meta' => 'array',
    ];
}