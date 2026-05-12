<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'keyword' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $keyword = trim((string) ($validated['keyword'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 15);

        $users = User::query()
            ->with('tenant:id,name,contact_email')
            ->select(['id', 'tenant_id', 'name', 'email', 'role', 'status', 'last_login_at', 'created_at'])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json($users);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in([
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN,
                User::ROLE_OWNER,
            ])],
        ]);

        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => '不能修改自己的權限。',
            ], 422);
        }

        if (
            $user->role === User::ROLE_SUPER_ADMIN
            && $validated['role'] !== User::ROLE_SUPER_ADMIN
            && User::where('role', User::ROLE_SUPER_ADMIN)->count() <= 1
        ) {
            return response()->json([
                'message' => '系統至少需要保留一位 super_admin。',
            ], 422);
        }

        $user->forceFill([
            'role' => $validated['role'],
        ])->save();

        return response()->json([
            'message' => '權限已更新。',
            'data' => $user->fresh(['tenant:id,name,contact_email']),
        ]);
    }
}
