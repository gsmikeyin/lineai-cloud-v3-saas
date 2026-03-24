<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'campaign_id','tenant_id','customer_id','delivery_status','error_code',
        'error_message','sent_at','delivered_at','opened_at','clicked_at','meta'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'meta' => 'array',
    ];

    public function campaign(): BelongsTo { return $this->belongsTo(Campaign::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
