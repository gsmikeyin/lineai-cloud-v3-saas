<?php

namespace App\Services\CRM;

use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Tenant;

class CustomerService
{
    public function firstOrCreateLineCustomer(Tenant $tenant, string $lineUserId, ?string $displayName = null): Customer
    {
        return Customer::firstOrCreate(
            ['tenant_id' => $tenant->id, 'line_user_id' => $lineUserId],
            [
                'source' => 'line',
                'display_name' => $displayName ?: 'LINE User',
                'status' => 'active',
                'first_interaction_at' => now(),
                'last_interaction_at' => now(),
            ]
        );
    }

    public function openConversation(Customer $customer): Conversation
    {
        return Conversation::firstOrCreate(
            ['tenant_id' => $customer->tenant_id, 'customer_id' => $customer->id, 'status' => 'open'],
            [
                'channel' => 'line',
                'priority' => 'normal',
                'ai_enabled' => true,
                'human_handoff' => false,
                'last_message_at' => now(),
            ]
        );
    }
}
