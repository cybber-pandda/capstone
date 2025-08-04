<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\B2BDetail;

class B2BController extends Controller
{
    public function index()
    {
        return view('pages.b2b.v_profile', [
            'page' => 'My Profile',
        ]);
    }

    public function update(Request $request)
    {
        $userid = auth()->user()->id;
        $user = User::where('id', $userid)->first();

        $request->validate([
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'about' => 'nullable|max:255',
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|confirmed|min:6',
        ]);

        $user->name = $request->firstname . ' ' . $request->lastname;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->about = $request->about;

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'profile_picture' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);


        $userid = auth()->user()->id;
        $user = User::where('id', $userid)->first();

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('assets/upload/profiles/');

            // Create directory if it doesn't exist
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            // Delete old profile picture if exists
            if ($user->profile && file_exists(public_path($user->profile))) {
                unlink(public_path($user->profile));
            }

            // Move uploaded file to destination
            $file->move($destination, $filename);

            // Save new path to 'profile' column
            $user->profile = 'assets/upload/profiles/' . $filename;
            $user->save();
        }

        return back()->with('success', 'Profile picture updated.');
    }

    public function business_requirement(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'certificate_registration' => 'required|file|mimes:pdf|max:2048',
            'business_permit' => 'required|file|mimes:pdf|max:2048',
        ], [
            'certificate_registration.required' => 'Certificate registration is required',
            'business_permit.required' => 'Business permit is required',
            'certificate_registration.mimes' => 'Certificate must be PDF, JPG, JPEG, or PNG',
            'business_permit.mimes' => 'Business permit must be PDF, JPG, JPEG, or PNG',
            'certificate_registration.max' => 'Certificate file too large (max 2MB)',
            'business_permit.max' => 'Business permit file too large (max 2MB)',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();

            // Create upload directory if it doesn't exist
            $uploadPath = public_path('assets/upload/requirements');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Process certificate registration file
            $certificateFile = $request->file('certificate_registration');
            $certificateFileName = 'certificate_' . $user->id . '_' . time() . '.' . $certificateFile->getClientOriginalExtension();
            $certificateFile->move($uploadPath, $certificateFileName);
            $certificatePath = 'assets/upload/requirements/' . $certificateFileName;

            // Process business permit file
            $businessPermitFile = $request->file('business_permit');
            $businessPermitFileName = 'permit_' . $user->id . '_' . time() . '.' . $businessPermitFile->getClientOriginalExtension();
            $businessPermitFile->move($uploadPath, $businessPermitFileName);
            $businessPermitPath = 'assets/upload/requirements/' . $businessPermitFileName;

            // Create or update B2B details
            B2BDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'certificate_registration' => $certificatePath,
                    'business_permit' => $businessPermitPath,
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Requirements submitted successfully. Please wait for approval.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting requirements: ' . $e->getMessage()
            ], 500);
        }
    }
}
