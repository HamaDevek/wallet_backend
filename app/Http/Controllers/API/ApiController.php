<?php

namespace App\Http\Controllers\API;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * API Health Check
     */
    public function index(Request $request)
    {
        return ApiResponseHelper::success([
            'status' => 'healthy',
            'version' => '1.0.0',
            'environment' => app()->environment(),
        ], 'API is running successfully');
    }
}
