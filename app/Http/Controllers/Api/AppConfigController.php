<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AppConfig;

class AppConfigController extends Controller
{
    public function checkUpdate()
    {
        $config = AppConfig::latest()->first();

        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }
}
