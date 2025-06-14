<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shelter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function partner_store(Request $request)
    {
        
        // Validate input data
        $validated = $request->validate([
            'partnerFullname' => 'required|string|max:255',
            'partnerUsername' => 'required|string|max:255|unique:users,username',
            'partnerEmail' => 'required|email|max:255|unique:users,email',
            'partnerPhonenumber' => ['required', 'regex:/^09\d{9}$/'],
        ]);


        $user = User::create([
            'username' => $validated['partnerUsername'],
            'email' =>  $validated['partnerEmail'],
            'role' => 'shelterowner/admin'
        ]);


        if ($user) {
            Shelter::create([
                'user_id' => $user->id,
                'owner_name' => $validated['partnerFullname'],
                'owner_phone' => $validated['partnerPhonenumber']
            ]);

        }

        return response()->json([
            'message' => 'Thank you! Your request has been submitted successfully.',
        ]);
    }
}
