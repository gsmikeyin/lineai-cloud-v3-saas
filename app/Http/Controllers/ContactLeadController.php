<?php

namespace App\Http\Controllers;

use App\Models\ContactLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactLeadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $lead = ContactLead::create([
            ...$validated,
            'status' => ContactLead::STATUS_NEW,
            'source' => 'landing_page',
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        // 可選：寄通知信給管理員
        if (config('mail.from.address') && config('app.admin_email')) {
            try {
                Mail::raw(
                    "New Contact Lead\n\n"
                    . "Name: {$lead->name}\n"
                    . "Email: {$lead->email}\n"
                    . "Company: {$lead->company}\n"
                    . "Phone: {$lead->phone}\n\n"
                    . "Message:\n{$lead->message}",
                    function ($message) use ($lead) {
                        $message->to(config('app.admin_email'))
                            ->subject('New Contact Lead - ' . $lead->name);
                    }
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'success' => true,
            'message' => '已收到你的聯絡資訊，我們會盡快與你聯繫。',
            'data' => $lead,
        ], 201);
    }

    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'in:new,contacted,closed'],
            'keyword' => ['nullable', 'string', 'max:255'],
        ]);

        $query = ContactLead::query()->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('keyword')) {
            $keyword = (string) $request->string('keyword');

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('company', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('message', 'like', "%{$keyword}%");
            });
        }

        return response()->json($query->paginate(20));
    }

    public function show(ContactLead $contactLead)
    {
        return response()->json($contactLead);
    }

    public function update(Request $request, ContactLead $contactLead)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,contacted,closed'],
        ]);

        $payload = [
            'status' => $validated['status'],
        ];

        if ($validated['status'] === ContactLead::STATUS_CONTACTED && !$contactLead->contacted_at) {
            $payload['contacted_at'] = now();
        }

        $contactLead->update($payload);

        return response()->json([
            'success' => true,
            'data' => $contactLead->fresh(),
        ]);
    }
}