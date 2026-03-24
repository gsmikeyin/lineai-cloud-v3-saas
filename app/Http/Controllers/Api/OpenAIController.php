<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class OpenAIController extends Controller
{

    //file-HXuz65R2ToXwqFdHF6xg1c
    //file-XkRvKHxKQrM5ChMz7zKQ7z
    public function uploadFile(Request $request): JsonResponse
    {
      $response = Http::withToken(env('OPENAI_API_KEY'))
              ->attach(
                  'file',
                  fopen(storage_path('app/private/faq.pdf'), 'r'),
                 'faq.pdf'
      )
       ->post('https://api.openai.com/v1/files', [
              'purpose' => 'assistants',
       ]);

       $data = $response->json();
       $fileId = $data['id'] ?? null;

        return response()->json([
            'token' => env('OPENAI_API_KEY'),
            'fileID' => $fileId,
        ]);
    }

    ////////////////////////////////////////////
    //vs_69c239c4375c81918ac8559f8fe3ba0a
    public function VectorStore(Request $request): JsonResponse
    {                   
        $fileId = 'file-HXuz65R2ToXwqFdHF6xg1c';

        $storeResponse = Http::withToken(env('OPENAI_API_KEY'))
               ->post('https://api.openai.com/v1/vector_stores', [
               'name' => 'customer-faq-store',
               'file_ids' => [$fileId],
        ]);

        $storeData = $storeResponse->json();
        $vectorStoreId = $storeData['id'] ?? null;

        return response()->json([
            'token' => env('OPENAI_API_KEY'),
            'vectorStoreId' => $vectorStoreId,
        ]);

    }


    public function AddFileVectorStore(Request $request): JsonResponse
    {                                 
       $attachResponse = Http::withToken(env('OPENAI_API_KEY'))
               ->post("https://api.openai.com/v1/vector_stores/{$request->vectorStoreId}/files", [
               'file_id' => $request->fileId,
        ]);
        $attachData = $attachResponse->json();

        return response()->json([
            'token' => env('OPENAI_API_KEY'),            
        ]);

    }



    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()?->load('tenant'));
    }

    public function logout(Request $request): JsonResponse
    {
      $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true
        ]);
    }
}
