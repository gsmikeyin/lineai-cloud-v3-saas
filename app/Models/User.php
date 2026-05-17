<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;


class User extends Authenticatable implements MustVerifyEmail, HasLocalePreference
{
    use HasApiTokens, HasFactory, Notifiable;


    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_FREE = 'free';
    public const ROLE_BASIC = 'basic';
    public const ROLE_PRO = 'pro';
    public const ROLE_OWNER = 'owner';
    public const ROLE_USER = 'user';


    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'last_login_at',
        'permissions',
        'email_verified_at',
        'locale',
        'line_user_id',
        'avatar',        
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


   protected static function booted()
   {
        ResetPassword::createUrlUsing(function ($user, string $token) {
             return config('app.frontend_url')
               . '/reset-password?token=' . $token
               . '&email=' . urlencode($user->email)
               . '&locale=' . urlencode($user->preferredLocale());
       });

    }



    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    public function preferredLocale(): string
    {
        return $this->locale === 'en' ? 'en' : 'zh_TW';
    }

    
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'assigned_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function createdCampaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'created_by');
    }

    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function automationRules(): HasMany
    {
        return $this->hasMany(AutomationRule::class, 'created_by');
    }

    public function scopeTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isOwner(): bool
    {
        return in_array($this->role, [
            self::ROLE_FREE,
            self::ROLE_BASIC,
            self::ROLE_PRO,
            self::ROLE_OWNER,
        ], true);
    }
    
    
   

      public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
        ], true);
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_OWNER,
            self::ROLE_FREE,
            self::ROLE_BASIC,
            self::ROLE_PRO,
        ], true);
    }


       public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        return in_array($this->role, $roles, true);
    }
    


}
