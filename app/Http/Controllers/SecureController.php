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
            'chat' => resource_path('function/chat.js'),

            'product_management' => resource_path('function/superadmin/product_management.js'),
            'category_management' => resource_path('function/superadmin/category_management.js'),
            'inventory_management' => resource_path('function/superadmin/inventory_management.js'),
            'user_management' => resource_path('function/superadmin/user_management.js'),
            'user_report' => resource_path('function/superadmin/user_report.js'),
            'delivery_report' => resource_path('function/superadmin/delivery_report.js'),
            'inventory_report' => resource_path('function/superadmin/inventory_report.js'),
            'b2b' => resource_path('function/superadmin/b2b.js'),
            'delivery_rider' => resource_path('function/superadmin/delivery_rider.js'),
            'salesofficer' => resource_path('function/superadmin/salesofficer.js'),
            'submitted_po' => resource_path('function/superadmin/submitted_po.js'),
            'delivery_tracking' => resource_path('function/superadmin/delivery_tracking.js'),
            'delivery_personnel' => resource_path('function/superadmin/delivery_personnel.js'),

            'delivery_order' => resource_path('function/deliveryrider/delivery_order.js'),
            'delivery_history' => resource_path('function/deliveryrider/delivery_history.js'),
            'delivery_location' => resource_path('function/deliveryrider/delivery_location.js'),

            'salesofficer_pr' => resource_path('function/salesofficer/purchase_request.js'),
            'salesofficer_sentquotations' => resource_path('function/salesofficer/send_quotation.js'),
            'salesofficer_submittedorder' => resource_path('function/salesofficer/submitted_order.js'),
           
        ];

        if (!array_key_exists($filename, $jsFiles) || !file_exists($jsFiles[$filename])) {
            abort(404); // Handle the case where the file is not found
        }

        $content = file_get_contents($jsFiles[$filename]);
        return response($content)->header('Content-Type', 'application/javascript');
    }
}
