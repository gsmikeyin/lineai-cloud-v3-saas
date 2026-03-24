<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name','code','price_monthly','price_yearly','max_customers',
        'max_team_members','max_monthly_ai_replies','max_monthly_broadcasts',
        'max_automation_rules','supports_ai','supports_crm','supports_campaigns',
        'supports_analytics','supports_team','is_active','meta'
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'supports_ai' => 'boolean',
        'supports_crm' => 'boolean',
        'supports_campaigns' => 'boolean',
        'supports_analytics' => 'boolean',
        'supports_team' => 'boolean',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }
}
