<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineWebhookController;


Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');



Route::get('/line/test', function () {
    return 'LINE webhook module ready';
});


