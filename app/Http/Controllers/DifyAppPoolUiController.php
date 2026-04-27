<?php

namespace App\Http\Controllers;

use App\Models\DifyAppPool;

class DifyAppPoolUiController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => DifyAppPool::query()
                ->with('assignedTenant:id,name,contact_name,contact_email')
                ->latest('id')
                ->get(),
        ]);
    }

}
