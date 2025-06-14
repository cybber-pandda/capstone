<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CompanySetting;
use App\Models\UserSecurityQuestion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        $page = 'General Setting';
        $user = User::getCurrentUser();

        $companySetting = CompanySetting::first();
        $accountSetting = User::where('id', $user->id)->first();

        // Check if $companySetting is not null before calling toArray()
        $companySettingArray = $companySetting ? $companySetting->toArray() : [];

        // Check if $accountSetting is not null before calling toArray()
        $accountSettingArray = $accountSetting ? $accountSetting->toArray() : [];

        $user = User::getCurrentUser();

        $securityQuestion = UserSecurityQuestion::where('user_id', $user->id)->first();

        return view('pages.back.v_generalsettings', compact('page', 'companySetting', 'accountSetting', 'securityQuestion'));
    }

    public function company(Request $request)
    {
        $request->validate([
            'company_email' => 'required|email',
            'company_phone' => ['required', 'regex:/^[\d\s\+\-]*$/'], // Only digits, spaces, plus, and hyphen are allowed
            'company_address' => 'required',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $companySetting = CompanySetting::firstOrNew();

        if ($request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = 'assets/uploads/' . $filename;
            $file->move(public_path('assets/uploads'), $filename);

            // if ($companySetting->company_logo && file_exists(public_path($companySetting->company_logo))) {
            //     unlink(public_path($companySetting->company_logo));
            // }

            $companySetting->company_logo = $filePath;
        }

        $companySetting->company_email = $request->company_email;
        $companySetting->company_phone = $request->company_phone;
        $companySetting->company_address = $request->company_address;
        $companySetting->save();

        return response()->json([
            'success' => 'Company setting saved successfully',
            'companySetting' => $companySetting
        ]);
    }

    public function profile(Request $request)
    {

        $user = User::getCurrentUser();

        $request->validate([
            'account_profile' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);


        $accountSetting = User::where('id', $user->id)->first();

        if ($request->hasFile('account_profile')) {
            $file = $request->file('account_profile');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = 'assets/uploads/' . $filename;
            $file->move(public_path('assets/uploads'), $filename);

            if ($accountSetting->profile && file_exists(public_path($accountSetting->profile))) {
                unlink(public_path($accountSetting->profile));
            }

            $accountSetting->profile = $filePath;
            $accountSetting->save();
        }

        return response()->json([
            'success' => 'Account saved successfully',
            'accountSetting' => $accountSetting
        ]);
    }

    // public function account(Request $request)
    // {
    //     $user = User::getCurrentUser();

    //     $request->validate([
    //         'account_username' => [
    //             'required',
    //             'string',
    //             'max:255',
    //             'unique:users,username,' . $user->id
    //         ],
    //         'account_email' => [
    //             'required',
    //             'email',
    //             'max:255',
    //             'unique:users,email,' . $user->id
    //         ],
    //     ]);


    //     $accountSetting = User::where('id', $user->id)->first();

    //     $accountSetting->username =  $request->account_username;
    //     $accountSetting->email = $request->account_email;
    //     $accountSetting->save();

    //     return response()->json([
    //         'success' => 'Account saved successfully',
    //         'accountSetting' => $accountSetting
    //     ]);
    // }


    public function account(Request $request)
    {
        $user = User::getCurrentUser();

        // Validate security question and answer
        $request->validate([
            'security_question' => 'required|integer',
            'security_answer' => 'required|string',
            'account_username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username,' . $user->id,
            ],
            'account_email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
        ]);

        // Check security question and answer
        $securityQuestion = UserSecurityQuestion::where('user_id', $user->id)
            ->where('questions', $request->security_question)
            ->first();

        if (!$securityQuestion || !hash_equals($securityQuestion->answer, $request->security_answer)) {
            return response()->json([
                'success' => false,
                'message' => 'Security question or answer is incorrect.',
            ], 400);
        }

        // Update account details
        $accountSetting = User::where('id', $user->id)->first();
        $accountSetting->username = $request->account_username;
        $accountSetting->email = $request->account_email;
        $accountSetting->save();

        return response()->json([
            'success' => 'Account saved successfully',
            'accountSetting' => $accountSetting,
        ]);
    }


    public function password(Request $request)
    {

        $user = User::getCurrentUser();

        $request->validate([
            'currentPassword' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Check if the current password matches the stored hash
                    if (!Hash::check($value, auth()->user()->password)) {
                        $fail('The current password is incorrect.');
                    }
                },
            ],
            'currentNewPassword' => [
                'required',
                'min:8', // Minimum 8 characters
                'regex:/[a-z]/', // At least one lowercase character
                'regex:/[A-Z]/', // At least one uppercase character
                'regex:/[0-9]/', // At least one number
                'regex:/[@$!%*?&]/', // At least one symbol
            ],
            'confirmNewpassword' => [
                'required',
                'same:currentNewPassword', // Must match the new password
            ],
        ]);

        $accountSetting = User::where('id', $user->id)->first();

        $accountSetting->password = Hash::make($request->input('currentNewPassword'));
        $accountSetting->save();

        return response()->json([
            'success' => 'Account password saved successfully',
            'accountSetting' => $accountSetting
        ]);
    }
}
