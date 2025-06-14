<?php

namespace App\Http\Controllers;

use App\Models\Shelter;
use App\Models\GcashSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GcashSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $page = 'Bank Setting';
        return view('pages.back.v_gcash', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $userShelter = Shelter::where('user_id', auth()->id())->first();

        if (!$userShelter) {
            return response()->json(['data' => [], 'message' => 'User shelter not found'], 200);
        }

        $gcash = GcashSetting::where('shelter_id', $userShelter->id)->get();

        $formattedData = $gcash->map(function ($item) {
            return [
                'id' => $item->id,
                'gcashnumber' => $item->gcash_number,
                'gcashqr' => $item->gcash_qr,
                'status' => $item->status,
                'actions' => '

             <a class="edit-btn" href="javascript:void(0)" 
                data-id="' . $item->id . '"
                data-gcashnumber="' . $item->gcash_number . '"
                data-modaltitle="Edit">
               <i class="bi bi-pencil-square fs-3"></i>
             </a>

             <a class="delete-btn" href="javascript:void(0)" data-id="' . $item->id . '">
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
            'gcash_number' => ['required'],
            'gcash_qr' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'gcash_number.required' => 'Bank number is required.',
            'gcash_qr.required' => 'Bank QR Code is required.',
            'gcash_qr.mimes' => 'Bank QR must be a jpeg, png, jpg, or webp file.',
        ]);

        $userShelter = Shelter::where('user_id', auth()->id())->first();

        if (!$userShelter) {
            return response()->json(['data' => [], 'message' => 'User shelter not found'], 200);
        }


        $imagePath = null;

        if ($request->hasFile('gcash_qr')) {
            $imgFile = $request->file('gcash_qr');
            $filename = time() . '_' . $imgFile->getClientOriginalName();
            $imagePath = 'assets/uploads/' . $filename;
            $imgFile->move(public_path('assets/uploads'), $filename);
        }

        GcashSetting::create([
            'shelter_id' => $userShelter->id,
            'gcash_number' => $request->gcash_number,
            'gcash_qr' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Bank details saved successfully',
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
        $gcash = GcashSetting::find($id);

        if (!$gcash) {
            return response()->json(['error' => 'Gcash not found'], 404);
        }

        $request->validate([
            'gcash_number' => ['required'],
            'gcash_qr' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'gcash_number.required' => 'Bank number is required.',
            'gcash_qr.image' => 'Bank QR must be an image.',
            'gcash_qr.mimes' => 'Bank QR must be a jpeg, png, jpg, or webp file.',
        ]);

        if ($request->hasFile('gcash_qr')) {
            if ($gcash->gcash_qr && file_exists(public_path($gcash->gcash_qr))) {
                unlink(public_path($gcash->gcash_qr));
            }

            $imgFile = $request->file('gcash_qr');
            $filename = time() . '_' . $imgFile->getClientOriginalName();
            $imagePath = 'assets/uploads/' . $filename;
            $imgFile->move(public_path('assets/uploads'), $filename);

            $gcash->gcash_qr = $imagePath;
        }

        $gcash->gcash_number = $request->gcash_number;
        $gcash->save();

        return response()->json([
            'message' => 'Bank details updated successfully',
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
        $gcash = GcashSetting::find($id);

        if (!$gcash) {
            return response()->json(['error' => 'Gcash not found'], 404);
        }

        if ($gcash->gcash_qr && file_exists(public_path($gcash->gcash_qr))) {
            unlink(public_path($gcash->gcash_qr));
        }

        DB::transaction(function () use ($gcash) {
            $gcash->delete();
        });

        return response()->json(['message' => 'Bank deleted successfully', 'type' => 'success']);
    }

    public function switch_status(Request $request)
    {
        $gcashId = $request->id;
        $status = $request->status;
    
        $gcash = GcashSetting::find($gcashId);
    
        if (!$gcash) {
            return response()->json(['message' => 'Bank not found'], 400);
        }
    
        if ($status == 1) {
            GcashSetting::where('status', 'Active')->update(['status' => 'Inactive']);
        }
    
        $gcash->status = $status;
        $gcash->save();
    
        return response()->json([
            'message' => 'Status updated successfully',
            'type' => 'success'
        ]);
    }
    

}
