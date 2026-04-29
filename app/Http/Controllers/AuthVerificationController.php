<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class AuthVerificationController extends Controller
{
    public function send(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email is already verified.',
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent.',
        ]);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required'],
            'hash' => ['required', 'string'],
            'expires' => ['required'],
            'signature' => ['required', 'string'],
        ]);

        $signedInUser = $request->user();

        if ($signedInUser && (string) $signedInUser->getKey() !== (string) $validated['id']) {
            return response()->json([
                'message' => 'This verification link does not belong to the signed-in user.',
            ], 403);
        }

        $user = $signedInUser ?: User::query()->find($validated['id']);

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $verifyUrl = url('/api/email/verify/' . $validated['id'] . '/' . $validated['hash'])
            . '?expires=' . urlencode($validated['expires'])
            . '&signature=' . urlencode($validated['signature']);

        if (! URL::hasValidSignature(Request::create($verifyUrl))) {
            return response()->json([
                'message' => 'The verification link is invalid or expired.',
            ], 403);
        }

        if (! hash_equals((string) $validated['hash'], sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'The verification data is invalid.',
            ], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'user' => $user->fresh(),
        ]);
    }

    public function verifySigned(Request $request, string $id, string $hash)
    {
        $user = User::query()->find($id);
        $frontendUrl = rtrim($request->getSchemeAndHttpHost(), '/') . '/app/verify-email';

        if (str_contains($frontendUrl, '127.0.0.1') || str_contains($frontendUrl, 'localhost')) {
            $frontendUrl = rtrim(config('app.url'), '/') . '/app/verify-email';
        }

        if (! $user) {
            return redirect()->away($frontendUrl . '?verified=failed');
        }

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return redirect()->away($frontendUrl . '?verified=failed');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->away($frontendUrl . '?verified=1');
    }
}
