<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SecureController extends Controller
{
    public function serveJsFile(Request $request, $filename)
    {
        // Define the secure JavaScript file paths
        $jsFiles = [
            'login' => resource_path('function/login.js'),
            'register' => resource_path('function/register.js'),
            'forgot' => resource_path('function/forgotpw.js'),
            'reset' => resource_path('function/resetpw.js'),
            'verify' => resource_path('function/verify.js'),
            'company' => resource_path('function/company.js'),
            'account' => resource_path('function/account.js'),
            'productManagement' => resource_path('function/product_management.js'),
            'user_report' => resource_path('function/user_report.js'),
            'delivery_report' => resource_path('function/delivery_report.js'),
            'inventory_report' => resource_path('function/inventory_report.js'),
            'delivery_rider' => resource_path('function/delivery_rider.js'),
           
        ];

        if (!array_key_exists($filename, $jsFiles) || !file_exists($jsFiles[$filename])) {
            abort(404); // Handle the case where the file is not found
        }

        $content = file_get_contents($jsFiles[$filename]);
        return response($content)->header('Content-Type', 'application/javascript');
    }
}
