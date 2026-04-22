<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DifyApp extends Model
{
    protected $fillable = [
        'app_id',
        'app_mode',
        'app_name',
        'app_description',
        'api_key',
        'dsl_path',
    ];
}