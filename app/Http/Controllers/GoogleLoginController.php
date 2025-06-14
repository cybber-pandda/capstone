<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }


  public function handleGoogleCallback()
{
    // Get the Google user information
    $googleUser = Socialite::driver('google')->stateless()->user();

    // Check if a user with the given email already exists
    $existingUser = User::where('email', $googleUser->email)->first();

    if ($existingUser) {
        // If the user exists, log them in
        Auth::login($existingUser);
        return redirect(RouteServiceProvider::HOME); // Redirect to the home page or intended route
    }

    // If the user does not exist, create a new user
    $user = User::create([
        'username' => $googleUser->name,
        'email' => $googleUser->email,
        'email_verified_at' => Carbon::now(),
        'password' => Hash::make(rand(100000, 999999)), // Use a random password
        'profile' => $googleUser->avatar,
    ]);

    // Log in the newly created user
    Auth::login($user);

    return redirect(RouteServiceProvider::HOME); // Redirect to the home page or intended route
}

}
