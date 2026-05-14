<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $tenantId = $request->user()->tenant_id;
        $perPage = (int) ($validated['per_page'] ?? 20);

        $items = Customer::query()
            ->where('tenant_id', $tenantId)
            ->latest('last_interaction_at')
            ->paginate($perPage);

        return response()->json($items);
    }

    public function show(Request $request, Customer $customer)
    {
        abort_if($customer->tenant_id !== $request->user()->tenant_id, 403);

        $customer->load([
            'tags',
            'notes.user:id,name',
            'conversations' => function ($q) {
                $q->with(['assignedUser:id,name'])
                    ->latest('last_message_at')
                    ->limit(10);
            },
            'messages' => function ($q) {
                $q->latest('id')
                    ->limit(20);
            },
        ]);

        return response()->json($customer);
    }
}
