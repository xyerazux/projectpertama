<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function checkVersion()
    {
        $version = AppVersion::latest()->first();

        if (!$version) {
            return response()->json([
                'success' => false,
                'message' => 'No version info available',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'version_name' => $version->version_name,
                'download_url' => $version->download_url,
                'is_mandatory' => $version->is_mandatory,
            ]
        ]);
    }
}
