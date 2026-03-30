<?php

namespace App\Http\Controllers;

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
                'message' => 'Email 已驗證',
            ]);
        }


        $user->sendEmailVerificationNotification();


        return response()->json([
            'success' => true,
            'message' => '驗證信已寄出',
        ]);
    }

    public function verify(Request $request)
    {  
      
       error_log("verify mail in: " );

       $validated = $request->validate([
            'id' => ['required'],
            'hash' => ['required', 'string'],
            'expires' => ['required'],
            'signature' => ['required', 'string'],
        ]);

        $user = $request->user();

        if ((string) $user->getKey() !== (string) $validated['id']) {
            return response()->json([
                'message' => '驗證使用者不一致',
            ], 403);
        }

        $verifyUrl = url('/api/email/verify/' . $validated['id'] . '/' . $validated['hash'])
            . '?expires=' . urlencode($validated['expires'])
            . '&signature=' . urlencode($validated['signature']);

        if (! URL::hasValidSignature(Request::create($verifyUrl))) {
            return response()->json([
                'message' => '驗證連結無效或已過期',
            ], 403);
        }

        if (! hash_equals((string) $validated['hash'], sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => '驗證資料不正確',
            ], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return response()->json([
            'success' => true,
            'message' => 'Email 驗證成功',
        ]);
    }
}