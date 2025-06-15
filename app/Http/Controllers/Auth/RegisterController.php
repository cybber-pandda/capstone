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
            'phone_number' => ['required', 'regex:/^09\d{9}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'cor' => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5MB limit
            'businesspermit' => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5MB limit
            'agree' => 'accepted',
        ], [
            'phone_number.required' => 'Phone number is required.',
            'phone_number.regex' => 'Phone number must start with 09 and be exactly 11 digits.',
            'cor.required' => 'Certificate of Registration (COR) is required.',
            'cor.mimes' => 'COR must be a PDF file.',
            'businesspermit.required' => 'Business Permit is required.',
            'businesspermit.mimes' => 'Business Permit must be a PDF file.',
            'agree.accepted' => 'The terms and condition must be accepted.'
        ]);
    }

    protected function create(array $data)
    {
        // Handle file uploads
        $corPath = null;
        $businessPermitPath = null;

        if (request()->hasFile('cor')) {
            $corFile = request()->file('cor');
            $corName = 'cor_' . Str::random(10) . '.' . $corFile->getClientOriginalExtension();
            $corFile->move(public_path('assets/uploads'), $corName);
            $corPath = 'assets/uploads/' . $corName;
        }

        if (request()->hasFile('businesspermit')) {
            $permitFile = request()->file('businesspermit');
            $permitName = 'permit_' . Str::random(10) . '.' . $permitFile->getClientOriginalExtension();
            $permitFile->move(public_path('assets/uploads'), $permitName);
            $businessPermitPath = 'assets/uploads/' . $permitName;
        }

        // Create user
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['reg_password']),
            'cor' => $corPath,
            'businesspermit' => $businessPermitPath,
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
        return view('auth.register', compact('page', 'companysettings'));
    }
}
