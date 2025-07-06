<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function show(Request $request)
    {

        $page = 'Verify Email';
        $companysettings = DB::table('company_settings')->first();

        return $request->user()->hasVerifiedEmail()
            ? redirect($this->redirectPath())
            : view('auth.verify', compact('page', 'companysettings'));
    }

    public function otp_verify(Request $request)
    {
        $this->validate($request, [
            'code' => 'required'
        ]);

        // Retrieve the currently authenticated user
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Check if the OTP is expired
        if (now()->greaterThan($user->otp_expire)) {
            return response()->json(['message' => 'Verification code has expired.'], 422);
        }

        // Verify the OTP code
        if (trim($request->input('code')) !== trim($user->otp_code)) {
            return response()->json(['message' => 'Invalid verification code.'], 422);
        }

        // Mark the user as verified
        $user->email_verified_at = now();
        //$user->otp_code = null; // Clear OTP code
        // $user->otp_expire = null; // Clear OTP expiration
        $user->save();

        $redirect  = Auth::user()->role === 'adopter' ? route('welcome') : route('home');

        return response()->json([
            'message' => 'Account Verified!',
            'redirect' => $redirect
        ]);
    }
}
