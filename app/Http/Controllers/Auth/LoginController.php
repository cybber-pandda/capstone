<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\UserLog;
use App\Models\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {

        if (auth()->check()) {
            return redirect()->route('home');
        }

        $page = 'Sign In';
        $companysettings = DB::table('company_settings')->first();

         return response()
        ->view('auth.login', compact('page', 'companysettings'))
        ->header('Cache-Control','no-cache, no-store, max-age=0, must-revalidate')
        ->header('Pragma','no-cache')
        ->header('Expires','Sat, 01 Jan 1990 00:00:00 GMT');
    }

    public function customLogin(Request $request)
    {
        $identifier = $request->input('identifier');
        $password = $request->input('password');

        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        // If user not found
        if (!$user) {
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                return back()->withErrors(['identifier' => 'Email not registered.'])->withInput();
            } else {
                return back()->withErrors(['identifier' => 'Username not found.'])->withInput();
            }
        }

        // ✅ Check if account is deactivated - ITO
        if ($user->status == 0) {
            Auth::logout(); // ensure no residual session
            return redirect()->route('login')->with('account_deactivated', true);
        }
        //HANGGANG DITO
        // If password incorrect
        if (!Hash::check($password, $user->password)) {
            return back()->withErrors([
                'password' => 'The password you’ve entered is incorrect.'
            ])->withInput();
        }


        // Login success
        Auth::login($user);
        return redirect()->intended('home');
    }


    public function ajaxLogin(Request $request)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Attempt to log in
        $credentials = $this->credentials($request);

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            $rolesToForceReset = ['b2b', 'salesofficer', 'deliveryrider'];

            if ($user->force_password_change &&  $user->created_by_admin && in_array($user->role, $rolesToForceReset)) {
                return response()->json([
                    'message' => 'Password change required',
                    //'redirect' => route('password.change.form')
                ], 200);
            }

            $redirect  = Auth::user()->role === 'b2b' ? '/' : '/home';
            return response()->json(['message' => 'Login successful', 'redirect' => $redirect], 200);
        }

        return response()->json(['errors' => ['password' => ['Invalid credentials']]], 422);
    }

    /**
     * Override the credentials method to support both email and username.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $identifier = $request->input('identifier');

        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $field => $identifier,
            'password' => $request->input('password'),
            //'status' => 1, ITO
        ];
    }

    /**
     * The user has been authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return mixed
     */

    protected function authenticated(Request $request, $user)
    {

        if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && $user->email_verified_at === null) {
            $user->sendEmailVerificationNotification();
        }

        UserLog::create([
            'user_id' => $user->id,
            'event' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'logged_at' => now(),
        ]);

        session()->flash('checkCart', true);
    }

    public function username()
    {
        return 'identifier';
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        // Log the logout
        if ($user) {
            UserLog::create([
                'user_id' => $user->id,
                'event' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_at' => now(),
            ]);
        }

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? response()->json([], 204)
            : redirect('/');
    }
}
