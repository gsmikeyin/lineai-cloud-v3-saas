<?php

namespace App\Services\Line;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LineSignatureService
{
    public function verify(Request $request, string $channelSecret): void
    {
        $signature = $request->header('x-line-signature');

        if (!$signature) {
            throw new AccessDeniedHttpException('Missing LINE signature.');
        }

        $body = $request->getContent();
        $hash = base64_encode(hash_hmac('sha256', $body, $channelSecret, true));

        if (!hash_equals($hash, $signature)) {
            throw new AccessDeniedHttpException('Invalid LINE signature.');
        }
    }
}