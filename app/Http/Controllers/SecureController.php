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
            'contentmenu' => resource_path('function/contentmenu.js'),
            'animaltype' => resource_path('function/animaltype.js'),
            'pets' => resource_path('function/pets.js'),
            'adopters' => resource_path('function/adopters.js'),
            'shelters' => resource_path('function/shelters.js'),
            'mypet' => resource_path('function/mypet.js'),
            'requirement' => resource_path('function/requirement.js'),
            'matchingpet' => resource_path('function/matchingpet.js'),
            'eventsettings' => resource_path('function/eventsettings.js'),
            'volunteers' => resource_path('function/volunteers.js'),
            'staff' => resource_path('function/staff.js'),
            'staffschedule' => resource_path('function/staffschedule.js'),
            'stafftask' => resource_path('function/stafftask.js'),
            'expenses' => resource_path('function/expenses.js'),
            'foodinventory' => resource_path('function/foodinventory.js'),
            'donations' => resource_path('function/donations.js'),
            'gcash' => resource_path('function/gcash.js'),
            'characteristic' => resource_path('function/characteristic.js'),
            'animalcare' => resource_path('function/animalcare.js'),
            'adoptedpet' => resource_path('function/adoptedpet.js')
        ];

        if (!array_key_exists($filename, $jsFiles) || !file_exists($jsFiles[$filename])) {
            abort(404); // Handle the case where the file is not found
        }

        $content = file_get_contents($jsFiles[$filename]);
        return response($content)->header('Content-Type', 'application/javascript');
    }
}
