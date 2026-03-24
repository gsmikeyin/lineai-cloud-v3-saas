<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'plan_id','owner_user_id','name','slug','company_name','tax_id','industry',
        'contact_name','contact_email','contact_phone','status','trial_ends_at',
        'subscribed_at','timezone','locale','currency','brand_summary',
        'ai_system_prompt','settings','meta' , 'webhook_key',        
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscribed_at' => 'datetime',
        'settings' => 'array',
        'meta' => 'array',
    ];


    
   protected static function booted(): void
   {
       static::creating(function ($tenant) {
           if (empty($tenant->webhook_key)) {
               $tenant->webhook_key = \Illuminate\Support\Str::random(32);
           }
       });
    }



    public function plan(): BelongsTo { return $this->belongsTo(Plan::class); }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_user_id'); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function customers(): HasMany { return $this->hasMany(Customer::class); }
    public function conversations(): HasMany { return $this->hasMany(Conversation::class); }
    public function messages(): HasMany { return $this->hasMany(Message::class); }

   public function lineChannel(){return $this->hasOne(TenantLineChannel::class);}


    public function knowledgeItems()
    {
        return $this->hasMany(\App\Models\KnowledgeItem::class);
    }
}
