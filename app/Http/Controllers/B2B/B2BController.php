<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


use App\Models\User;
use App\Models\B2BDetail;
use App\Models\B2BAddress;
use App\Models\PurchaseRequest;

class B2BController extends Controller
{
    public function index()
    {
         // 1️⃣ If user is NOT logged in → show login page
    if (!Auth::check()) {
        $page = 'Sign In';
        $companysettings = DB::table('company_settings')->first();

        return response()
            ->view('auth.login', compact('page', 'companysettings'))
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

    // 2️⃣ If user is logged in → check their role
    $user = Auth::user();

    // Example role logic (adjust 'role' and role names to match your database)
    
   if ($user->role === 'b2b') {
        return view('pages.b2b.v_profile', [
            'page' => 'My Profile',
        ]); }
        //for returning to the dashboard
         return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
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
            'business_name' => 'required|max:100',
            'tin_number' => 'required|max:20',
            'contact_number' => 'required|max:20',
            'contact_person' => 'required|max:100',
            'contact_person_number' => 'required|max:20',
        ], [
            'business.required' => 'Business store name is required',
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
            
            $b2b = B2BDetail::where('user_id', $user->id)->first();

            $status = $b2b->status ?? null; // keep old status if not rejected

            if ($status === 'rejected') {
                $status = null; // or null if you prefer
            }

            // Create or update B2B details
            B2BDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'certificate_registration' => $certificatePath,
                    'business_permit' => $businessPermitPath,
                    'business_name' => $request->business_name,
                    'tin_number' => $request->tin_number,
                    'contact_number' => $request->contact_number,
                    'contact_person' => $request->contact_person,
                    'contact_person_number' => $request->contact_person_number,
                    'status' => $status
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

    public function my_purchase_order()
    {
        // 1️⃣ If user is NOT logged in → show login page
    if (!Auth::check()) {
        $page = 'Sign In';
        $companysettings = DB::table('company_settings')->first();

        return response()
            ->view('auth.login', compact('page', 'companysettings'))
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

    // 2️⃣ If user is logged in → check their role
    $user = Auth::user();

    // Example role logic (adjust 'role' and role names to match your database)
    
   if ($user->role === 'b2b') {

        $userId = auth()->id();

        $purchaseRequests = PurchaseRequest::withCount('items')
            ->withSum('items', 'subtotal') // sum of all item subtotals
            ->where('customer_id', $userId)
            ->latest()
            ->get();

        $hasAddress = B2BAddress::where('user_id', $userId)->exists();

        return view('pages.b2b.v_purchase_order', [
            'page' => 'My Purchase Order',
            'purchaseRequests' => $purchaseRequests,
            'hasAddress' => $hasAddress
        ]);
    }
    //for returning to the dashboard
         return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
    }

    public function show_po($id)
    {
        $page = "Purchase Order Detail";
        $b2bReqDetails = null;
        $b2bAddress = null;
        $salesOfficer = null;

        $superadmin = User::where('role', 'superadmin')->first();

        $quotation = PurchaseRequest::with(['customer', 'items.product'])
            ->where('customer_id', auth()->id())
            ->findOrFail($id);

        if ($quotation->customer_id) {
            $b2bReqDetails = B2BDetail::where('user_id', $quotation->customer_id)->first();
            $b2bAddress = B2BAddress::where('user_id', $quotation->customer_id)->where('status', 'active')->first();
        }

        if ($quotation->prepared_by_id) {
            $salesOfficer = User::where('id', $quotation->prepared_by_id)->first();
        }

        return view('pages.b2b.v_purchase_order_show', compact('quotation', 'page', 'b2bReqDetails', 'b2bAddress', 'salesOfficer', 'superadmin'));
    }
}
