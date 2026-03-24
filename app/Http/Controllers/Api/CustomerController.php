<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $customers = Customer::query()
            ->tenant($tenantId)
            ->latest('last_interaction_at')
            ->paginate(20);

        return response()->json($customers);
    }

    public function show(Request $request, Customer $customer): JsonResponse
    {
        abort_unless($customer->tenant_id === $request->user()->tenant_id, 404);

        return response()->json(
            $customer->load(['tags', 'conversations.messages'])
        );
    }
}
