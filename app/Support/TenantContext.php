<?php

namespace App\Support;

class TenantContext
{
    public static function id(): ?int
    {
        return auth()->user()?->tenant_id;
    }
}
