
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('tenant');

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email
            ],
            'tenant' => [
                'name' => $user->tenant?->name
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'user.name' => ['required','string','max:255']
        ]);

        $user->update([
            'name' => data_get($validated,'user.name')
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Settings updated'
        ]);
    }
}
