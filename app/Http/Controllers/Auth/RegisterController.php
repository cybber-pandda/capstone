<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Rules\NoSpecialCharacters;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'firstname' => ['required', 'string', 'max:70'],
            'lastname' => ['required', 'string', 'max:70'],
            'username' => ['required', 'string', 'max:255', 'unique:users', new NoSpecialCharacters],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d).+$/'
            ],
            'agree' => 'accepted',
        ], [
            'agree.accepted' => 'The terms and condition must be accepted.',
            'password.regex' => 'Password must be at least 8 characters long and include at least one uppercase letter and one number.',
        ]);
    }

    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['firstname'] . ' ' . $data['lastname'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Send OTP or verification email
        $user->sendEmailVerificationNotification();

        Log::info('OTP sent:', ['otp' => $user->otp_code]);

        return $user;
    }

    public function registered(Request $request, $user)
    {
        return response()->json([
            'status' => 'Please verify your email with the OTP sent.',
            'redirect' => route('verification.notice')
        ]);
    }

    public function showRegistrationForm()
    {
        $page = 'Sign Up';
        $companysettings = DB::table('company_settings')->first();
        $terms = DB::table('terms_conditions')->where('content_type', 'Terms')->first();
        $conditions = DB::table('terms_conditions')->where('content_type', 'Condition')->first();
        $policy = DB::table('terms_conditions')->where('content_type', 'Policy')->first();
        return view('auth.register', compact('page', 'companysettings', 'terms', 'conditions', 'policy'));
    }
}
