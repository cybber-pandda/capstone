<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Shelter;
use App\Models\GcashSetting;
use App\Models\Donation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\DonationReceipt;

class DonationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        $page = auth()->check() && auth()->user()->role === 'shelterowner/admin' ? 'Donations' : 'Donate';

        $shelters = Shelter::where('deleted_at', null)->pluck('shelter_name', 'id')->toArray();

        $menus = DB::table('frontmenu')->where('deleted_at', null)->get();
        $companysettings = DB::table('company_settings')->first();
        $socialmedias = DB::table('socialmedias')->get();
        $terms = DB::table('terms_conditions')
            ->select('content_type', 'content')
            ->get();

        return view('pages.front.a_donate', compact('page', 'shelters', 'menus', 'companysettings','socialmedias','terms'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $donations = Donation::with('shelter')
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->get();

        $formattedData = $donations->map(function ($item) {
            return [
                'sheltername' => $item->shelter->shelter_name ?? 'N/A',
                'donationamount' => $item->donation_amount,
                'donationproof' => $item->upload_proof_donation,
                'donationstatus' => $item->status,
                'actions' => $item->status === 'Received' ?
                    '<span>Donation received. Thank you</span>' :
                    '
                <a class="edit-btn" href="javascript:void(0)" 
                    data-id="' . $item->id . '"
                    data-shelter="' . $item->shelter_id . '"
                    data-gcash="' . $item->gcash_setting_id . '"
                    data-amount="' . $item->donation_amount . '"
                    data-status="' . $item->status . '"
                    data-donationproof="' . $item->upload_proof_donation . '"
                    data-modaltitle="Edit">
                    <i class="bi bi-pencil-square fs-3"></i>
                </a>
                <a class="delete-btn d-none" href="javascript:void(0)" data-id="' . $item->id . '">
                    <i class="bi bi-trash fs-3"></i>
                </a>'
            ];
        });

        return response()->json(['data' => $formattedData]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'shelter' => 'required|integer',
            'gcash_id' => 'required|integer',
            'amount' => 'required|integer',
            'proof_donation' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('proof_donation')) {
            $imgFile = $request->file('proof_donation');
            $filename = time() . '_' . $imgFile->getClientOriginalName();
            $imagePath = 'assets/uploads/' . $filename;
            $imgFile->move(public_path('assets/uploads'), $filename);
        }

        $donation = Donation::create([
            'shelter_id' => $request->shelter,
            'gcash_setting_id' => $request->gcash_id,
            'user_id' => auth()->user()->id,
            'donation_amount' => $request->amount,
            'upload_proof_donation' => $imagePath,
        ]);

        auth()->user()->notify(new DonationReceipt($donation));

        return response()->json([
            'message' => 'Donation sent successfully',
            'type' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $donation = Donation::find($id);

        if (!$donation) {
            return response()->json(['error' => 'Donation not found'], 404);
        }

        $request->validate([
            'shelter' => 'required|integer',
            'gcash_id' => 'required|integer',
            'amount' => 'required|integer',
            'proof_donation' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('proof_donation')) {
            if ($donation->upload_proof_donation && file_exists(public_path($donation->upload_proof_donation))) {
                unlink(public_path($donation->upload_proof_donation));
            }

            $imgFile = $request->file('proof_donation');
            $filename = time() . '_' . $imgFile->getClientOriginalName();
            $imagePath = 'assets/uploads/' . $filename;
            $imgFile->move(public_path('assets/uploads'), $filename);

            $donation->upload_proof_donation = $imagePath;
        }


        $donation->shelter_id = $request->shelter;
        $donation->gcash_setting_id = $request->gcash_id;
        $donation->donation_amount = $request->amount;
        $donation->status = $request->status;
        $donation->save();

        return response()->json([
            'message' => 'Donation details updated successfully',
            'type' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $donation = Donation::find($id);

        if (!$donation) {
            return response()->json(['error' => 'Donation not found'], 404);
        }

        if ($donation->upload_proof_donation && file_exists(public_path($donation->upload_proof_donation))) {
            unlink(public_path($donation->upload_proof_donation));
        }

        DB::transaction(function () use ($donation) {
            $donation->delete();
        });

        return response()->json(['message' => 'Donation deleted successfully', 'type' => 'success']);
    }

    public function show_gcash(Request $request)
    {
        $shelterId = $request->id;

        $shelter = Shelter::where('id', $shelterId)->first();

        if(!$shelter){
            return response()->json(['message' => 'Shelter not found.'],400);
        }

        $gcash = GcashSetting::where('shelter_id', $shelterId)->where('status', 'Active')->first();

        if ($gcash) {
            return response()->json([
                'success' => true,
                'data' => [
                    'gcash_id' => $gcash->id,
                    'gcash_number' => $gcash->gcash_number,
                    'gcash_qr' => $gcash->gcash_qr,
                    'shelter_number' => $shelter->owner_phone,
                    'shelter_address' => $shelter->shelter_address
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No Bank details found for this shelter.'
            ], 400);
        }
    }
}
