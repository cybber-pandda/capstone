<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Models\CompanySetting;

class SettingsController extends Controller
{
    public function company(Request $request)
    {
        $page = 'Company Setting';
        $pageCategory = 'Settings';

        $companySetting = CompanySetting::first();

        if ($companySetting) {
            $companySetting->logo_url = asset($companySetting->company_logo ?? 'assets/dashboard/images/noimage.png');
        }

        if ($request->ajax()) {
            return response()->json([
                'companySetting' => $companySetting
            ]);
        }

        return view('pages.company', compact('page', 'pageCategory', 'companySetting'));
    }


    public function updateCompany(Request $request)
    {
        $request->validate([
            'company_email' => 'required|email',
            'company_phone' => 'required|string',
            'company_address' => 'required|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $companySetting = CompanySetting::first();

        if ($request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('assets/upload');

            // create directory if not exist
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // delete old file
            if ($companySetting->company_logo && file_exists(public_path($companySetting->company_logo))) {
                unlink(public_path($companySetting->company_logo));
            }

            $file->move($destinationPath, $filename);
            $companySetting->company_logo = 'assets/upload/' . $filename;
        }

        $companySetting->company_email = $request->company_email;
        $companySetting->company_phone = $request->company_phone;
        $companySetting->company_address = $request->company_address;
        $companySetting->save();

        return response()->json([
            'success' => 'Company details updated successfully.',
            'companySetting' => $companySetting
        ]);
    }


    public function profile()
    {
        $page = 'Profile Setting';
        $pageCategory = 'Settings';
        $user = User::getCurrentUser();

        $profile = User::where('id', $user->id)->first();

        return view('pages.profile', compact('page', 'pageCategory', 'profile'));
    }

    public function getUserProfile(Request $request)
    {
        $id = $request->input('id');

        $user = User::where('id', 'like', "%$id%")->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'about' => $user->about ?? 'No bio available',
            'joined' => $user->created_at->format('F d, Y'),
            'profile_image' => $user->profile
                ? $user->profile
                : asset('assets/dashboard/images/noprofile.png'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = User::getCurrentUser();
        $user = User::where('id', $user->id)->first();
        $tab = $request->input('tab');

        switch ($tab) {
            case 'profile':
                if ($request->hasFile('profile')) {
                    $file = $request->file('profile');
                    $destinationPath = public_path('assets/upload/profiles');

                    // Delete old file if exists
                    if ($user->profile && file_exists(public_path($user->profile))) {
                        unlink(public_path($user->profile));
                    }

                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move($destinationPath, $filename);

                    $user->profile = 'assets/upload/profiles/' . $filename;
                    $user->save();
                }
                break;

            case 'name':
                $user->name = $request->input('first_name') . ' ' . $request->input('last_name');
                $user->save();
                break;

            case 'username':
                $validator = Validator::make($request->all(), [
                    'username' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('users')->ignore($user->id),
                    ],
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }

                $user->username = $request->input('username');
                $user->save();
                break;

            case 'email':
                $validator = Validator::make($request->all(), [
                    'email' => [
                        'required',
                        'email',
                        Rule::unique('users')->ignore($user->id),
                    ],
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }

                $user->email = $request->input('email');
                $user->save();
                break;

            case 'password':
                $validator = Validator::make($request->all(), [
                    'current_password' => 'required',
                    'new_password' => 'required|string|min:8|confirmed',
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }

                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json(['error' => 'Current password is incorrect.'], 422);
                }

                $user->password = Hash::make($request->new_password);
                $user->save();
                break;

            case 'about':
                $user->about = $request->input('about');
                $user->save();
                break;
        }

        return response()->json(['message' => 'Profile updated successfully.']);
    }



    // public function company(Request $request)
    // {
    //     $request->validate([
    //         'company_email' => 'required|email',
    //         'company_phone' => ['required', 'regex:/^[\d\s\+\-]*$/'], // Only digits, spaces, plus, and hyphen are allowed
    //         'company_address' => 'required',
    //         'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    //     ]);

    //     $companySetting = CompanySetting::firstOrNew();

    //     if ($request->hasFile('company_logo')) {
    //         $file = $request->file('company_logo');
    //         $filename = time() . '_' . $file->getClientOriginalName();
    //         $filePath = 'assets/uploads/' . $filename;
    //         $file->move(public_path('assets/uploads'), $filename);

    //         // if ($companySetting->company_logo && file_exists(public_path($companySetting->company_logo))) {
    //         //     unlink(public_path($companySetting->company_logo));
    //         // }

    //         $companySetting->company_logo = $filePath;
    //     }

    //     $companySetting->company_email = $request->company_email;
    //     $companySetting->company_phone = $request->company_phone;
    //     $companySetting->company_address = $request->company_address;
    //     $companySetting->save();

    //     return response()->json([
    //         'success' => 'Company setting saved successfully',
    //         'companySetting' => $companySetting
    //     ]);
    // }

    // public function profile(Request $request)
    // {

    //     $user = User::getCurrentUser();

    //     $request->validate([
    //         'account_profile' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    //     ]);


    //     $accountSetting = User::where('id', $user->id)->first();

    //     if ($request->hasFile('account_profile')) {
    //         $file = $request->file('account_profile');
    //         $filename = time() . '_' . $file->getClientOriginalName();
    //         $filePath = 'assets/uploads/' . $filename;
    //         $file->move(public_path('assets/uploads'), $filename);

    //         if ($accountSetting->profile && file_exists(public_path($accountSetting->profile))) {
    //             unlink(public_path($accountSetting->profile));
    //         }

    //         $accountSetting->profile = $filePath;
    //         $accountSetting->save();
    //     }

    //     return response()->json([
    //         'success' => 'Account saved successfully',
    //         'accountSetting' => $accountSetting
    //     ]);
    // }

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


    // public function account(Request $request)
    // {
    //     $user = User::getCurrentUser();

    //     // Validate security question and answer
    //     $request->validate([
    //         'security_question' => 'required|integer',
    //         'security_answer' => 'required|string',
    //         'account_username' => [
    //             'required',
    //             'string',
    //             'max:255',
    //             'unique:users,username,' . $user->id,
    //         ],
    //         'account_email' => [
    //             'required',
    //             'email',
    //             'max:255',
    //             'unique:users,email,' . $user->id,
    //         ],
    //     ]);

    //     // Check security question and answer
    //     $securityQuestion = UserSecurityQuestion::where('user_id', $user->id)
    //         ->where('questions', $request->security_question)
    //         ->first();

    //     if (!$securityQuestion || !hash_equals($securityQuestion->answer, $request->security_answer)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Security question or answer is incorrect.',
    //         ], 400);
    //     }

    //     // Update account details
    //     $accountSetting = User::where('id', $user->id)->first();
    //     $accountSetting->username = $request->account_username;
    //     $accountSetting->email = $request->account_email;
    //     $accountSetting->save();

    //     return response()->json([
    //         'success' => 'Account saved successfully',
    //         'accountSetting' => $accountSetting,
    //     ]);
    // }


    // public function password(Request $request)
    // {

    //     $user = User::getCurrentUser();

    //     $request->validate([
    //         'currentPassword' => [
    //             'required',
    //             function ($attribute, $value, $fail) {
    //                 // Check if the current password matches the stored hash
    //                 if (!Hash::check($value, auth()->user()->password)) {
    //                     $fail('The current password is incorrect.');
    //                 }
    //             },
    //         ],
    //         'currentNewPassword' => [
    //             'required',
    //             'min:8', // Minimum 8 characters
    //             'regex:/[a-z]/', // At least one lowercase character
    //             'regex:/[A-Z]/', // At least one uppercase character
    //             'regex:/[0-9]/', // At least one number
    //             'regex:/[@$!%*?&]/', // At least one symbol
    //         ],
    //         'confirmNewpassword' => [
    //             'required',
    //             'same:currentNewPassword', // Must match the new password
    //         ],
    //     ]);

    //     $accountSetting = User::where('id', $user->id)->first();

    //     $accountSetting->password = Hash::make($request->input('currentNewPassword'));
    //     $accountSetting->save();

    //     return response()->json([
    //         'success' => 'Account password saved successfully',
    //         'accountSetting' => $accountSetting
    //     ]);
    // }
}
