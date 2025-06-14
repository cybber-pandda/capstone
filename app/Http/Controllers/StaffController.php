<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Staffs';
        return view('pages.back.v_staffs', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $staffs = Staff::orderBy('id', 'DESC')->whereNull('deleted_at')->get();

        $formattedData = $staffs->map(function ($item) {
            return [
                'profile' => $item->profile,
                'firstname' => $item->firstname,
                'lastname' => $item->lastname,
                'bday' => $item->birthday,
                'email' => $item->email,
                'city' => $item->city,
                'state' => $item->state,
                'zipcode' => $item->zipcode,
                'phone' => $item->phone,
                'actions' => '

                 <a class="edit-btn" href="javascript:void(0)" 
                    data-id="' .  $item->id . '"
                    data-firstname="' . $item->firstname . '"
                    data-lastname="' . $item->lastname . '"
                    data-bday="' . $item->birthday . '"
                    data-email="' . $item->email . '"
                    data-city="' . $item->city . '"
                    data-state="' . $item->state . '"
                    data-zipcode="' . $item->zipcode . '"
                    data-phone="' . $item->phone . '"
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
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'bday' => 'required|date',
            'email' => 'required|email|max:255|unique:users,email',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'required|integer',
            'phone' => ['required', 'regex:/^09\d{9}$/']
        ]);

        $imagePath = null;

        if ($request->hasFile('profile')) {
            $imgFile = $request->file('profile');
            $filename = time() . '_' . $imgFile->getClientOriginalName();
            $imagePath = 'assets/uploads/' . $filename;
            $imgFile->move(public_path('assets/uploads'), $filename);
        }


        Staff::create([
            'profile' => $imagePath,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'birthday' => $request->bday,
            'email' => $request->email,
            'city' => $request->city,
            'state' => $request->state,
            'zipcode' => $request->zipcode,
            'phone' => $request->phone
        ]);

        return response()->json([
            'message' => 'Staff details saved successfully',
            'type' => 'success'
        ]);
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

        $staff = Staff::where('id', $id)->first();

        if (!$staff) {
            return response()->json(['error' => 'Staff not found'], 404);
        }

        $request->validate([
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'bday' => 'required|date',
            'email' => 'required|email|max:255|unique:staffs,email,' . $staff->id,
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'required|integer',
            'phone' => ['required', 'regex:/^09\d{9}$/']
        ]);

        $imagePath = $staff->profile;

        if ($request->hasFile('profile')) {

            if ($staff->profile && file_exists(public_path($staff->profile))) {
                unlink(public_path($staff->profile));
            }

            $imgFile = $request->file('profile');
            $filename = time() . '_' . $imgFile->getClientOriginalName();
            $imagePath = 'assets/uploads/' . $filename;
            $imgFile->move(public_path('assets/uploads'), $filename);
        }

        $staff->update([
            'profile' => $imagePath,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'birthday' => $request->bday,
            'email' => $request->email,
            'city' => $request->city,
            'state' => $request->state,
            'zipcode' => $request->zipcode,
            'phone' => $request->phone
        ]);

        return response()->json([
            'message' => 'Staff details updated successfully',
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
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(['error' => 'Staff not found'], 404);
        }

        DB::transaction(function () use ($staff) {
            $staff->delete();
        });

        return response()->json(['message' => 'Staff details deleted successfully', 'type' => 'success']);
    }
}
