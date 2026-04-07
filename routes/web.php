<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LineAuthController;



Route::get('/auth/line/redirect', [LineAuthController::class, 'redirect']);
Route::get('/auth/line/callback', [LineAuthController::class, 'callback']);


Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');


/*
Route::get('/line/test', function () {
    return 'LINE webhook module ready';
});
*/




