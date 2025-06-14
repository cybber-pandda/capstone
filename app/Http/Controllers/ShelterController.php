<?php

namespace App\Http\Controllers;

use App\Models\Shelter;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Notifications\UserCredentialsNotification;
use Illuminate\Support\Facades\DB;

class ShelterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Shelters';

        return view('pages.back.v_shelters', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $shelters = User::where('role', 'shelterowner/admin')
            ->where(function ($query) {
                // Check if user has a shelter where 'deleted_at' is null
                $query->whereHas('shelter', function ($query) {
                    $query->whereNull('deleted_at');
                });
                // OR the user doesn't have any shelters (i.e., no record in shelters table)
                //->orWhereDoesntHave('shelter');
            })
            ->orderBy('id', 'DESC')
            ->get();

        $formattedData = $shelters->map(function ($item) {
            return [
                'profile' => $item->profile,
                'username' => $item->username,
                'email' => $item->email,
                'password' => $item->password,
                'ownername' => $item->shelter->owner_name ?? 'N/A',
                'ownerphone' => $item->shelter->owner_phone ?? 'N/A',
                'sheltername' => $item->shelter->shelter_name ?? 'N/A',
                'shelteraddress' => $item->shelter->shelter_address ?? 'N/A',
                'actions' => '
    
                 <a class="edit-btn" href="javascript:void(0)" 
                    data-id="' .  ($item->shelter?->id ?? 'N/A') . '"
                    data-profile="' . $item->profile . '"
                    data-username="' . $item->username . '"
                    data-email="' . $item->email . '"
                    data-name="' . ($item->shelter?->owner_name ?? 'N/A') . '"
                    data-phone="' . ($item->shelter?->owner_phone ?? 'N/A') . '"
                    data-sheltername="' . ($item->shelter?->shelter_name ?? 'N/A') . '"
                    data-shelteraddress="' . ($item->shelter?->shelter_address ?? 'N/A') . '"
                    data-modaltitle="Edit">
                   <i class="bi bi-pencil-square fs-3"></i>
                 </a>

                 <a class="delete-btn" href="javascript:void(0)" data-id="' . ($item->shelter?->id ?? 'N/A') . '">
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
            'name' => 'required|string',
            'phone' => ['required', 'regex:/^09\d{9}$/'], // 11 digits starting with 09
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => [
                'required',
                'min:8',
                // 'regex:/[a-z]/', // at least one lowercase letter
                // 'regex:/[A-Z]/', // at least one uppercase letter
                // 'regex:/[0-9]/', // at least one number
                // 'regex:/[@$!%*?&]/', // at least one special character
            ],
            'sheltername' => 'nullable|string',
            'shelteraddress' => 'nullable|string',
        ]);

        $imagePath = null;

        if ($request->hasFile('profile')) {
            $imgFile = $request->file('profile');
            $filename = time() . '_' . $imgFile->getClientOriginalName();
            $imagePath = 'assets/uploads/' . $filename;
            $imgFile->move(public_path('assets/uploads'), $filename);
        }

        $user = User::create([
            'profile' => $imagePath,
            'username' => $request->username,
            'email' => $request->email,
            'password' =>  Hash::make($request->input('password')),
            'role' => 'shelterowner/admin'
        ]);

        if ($user) {
            Shelter::create([
                'user_id' => $user->id,
                'owner_name' => $request->name,
                'owner_phone' => $request->phone,
                'shelter_name' => $request->sheltername,
                'shelter_address' => $request->shelteraddress,

            ]);

            $user->notify(new UserCredentialsNotification($user->username, $user->email, 'Shelter', $request->input('password')));
        }

        return response()->json([
            'message' => 'Shelter details saved successfully',
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


        $shelter = Shelter::where('id', $id)->first();

        if (!$shelter) {
            return response()->json(['error' => 'Shelter not found'], 404);
        }

        $user = User::find($shelter->user_id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }


        // Validate incoming request data
        $request->validate([
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'name' => 'required|string',
            'phone' => ['required', 'regex:/^09\d{9}$/'],
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => [
                'nullable', // make password optional on update
                'min:8',
                'regex:/[a-z]/', // at least one lowercase letter
                'regex:/[A-Z]/', // at least one uppercase letter
                'regex:/[0-9]/', // at least one number
                'regex:/[@$!%*?&]/', // at least one special character
            ],
            'sheltername' => 'nullable|string',
            'shelteraddress' => 'nullable|string',
        ]);

        $imagePath = $user->profile;

        if ($request->hasFile('profile')) {

            if ($user->profile && file_exists(public_path($user->profile))) {
                unlink(public_path($user->profile));
            }

            $imgFile = $request->file('profile');
            $filename = time() . '_' . $imgFile->getClientOriginalName();
            $imagePath = 'assets/uploads/' . $filename;
            $imgFile->move(public_path('assets/uploads'), $filename);
        }

        $user->update([
            'profile' => $imagePath,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->input('password')) : $user->password
        ]);

        $shelter->update([
            'user_id' => $user->id,
            'owner_name' => $request->name,
            'owner_phone' => $request->phone,
            'shelter_name' => $request->sheltername,
            'shelter_address' => $request->shelteraddress
        ]);

        $user->notify(new UserCredentialsNotification($user->username, $user->email, 'Shelter', $request->password ? $request->input('password') : null));

        return response()->json(['message' => 'Shelter details updated successfully', 'type' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $shelter = Shelter::find($id);

        if (!$shelter) {
            return response()->json(['error' => 'Shelter not found'], 404);
        }

        $user = User::find($shelter->user_id);

        if ($user) {
            if ($user->profile && file_exists(public_path($user->profile))) {
                unlink(public_path($user->profile));
            }
        }

        // Use a transaction to ensure all operations succeed together
        DB::transaction(function () use ($shelter) {
            $shelter->delete();
        });

        return response()->json(['message' => 'Shelter details deleted successfully', 'type' => 'success']);
    }
}
