<?php

namespace App\Http\Controllers;

use App\Models\Adopter;
use App\Models\User;
use App\Models\Pet;
use App\Models\AnimalType; 
use App\Models\AdoptionApplication;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Notifications\UserCredentialsNotification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdopterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Adopter';

        $animalType = AnimalType::pluck('name', 'name')->toArray();

        return view('pages.back.v_adopters', compact('page', 'animalType'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $adopters = User::where('role', 'adopter')
            ->whereHas('adopter', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->orderBy('id', 'DESC')
            ->get();

        $formattedData = $adopters->map(function ($item) {
            return [
                'profile' => $item->profile,
                'fullname' => $item->adopter->fullname ?? 'N/A',
                // 'prefered_animaltype' => $item->adopter->prefered_animaltype ?? 'N/A',
                // 'prefered_petgender' => $item->adopter->prefered_petgender ?? 'N/A',
                // 'prefered_petsize' => $item->adopter->prefered_petsize ?? 'N/A',
                'username' => $item->username,
                'email' => $item->email,
                'actions' => '

                 <a class="view-btn" href="javascript:void(0)"   
                    data-id="' .  ($item->adopter?->id ?? 'N/A') . '"
                    data-profile="' . $item->profile . '"
                    data-fullname="' . ($item->adopter?->fullname ?? 'N/A') . '"
                    data-bday="' . ($item->adopter?->bday ?? 'N/A') . '"
                    data-username="' . $item->username . '"
                    data-email="' . $item->email . '"
                    data-city="' . ($item->adopter?->city ?? 'N/A') . '"
                    data-state="' . ($item->adopter?->state ?? 'N/A') . '"
                    data-zipcode="' . ($item->adopter?->zipcode ?? 'N/A') . '"
                    data-phone="' . ($item->adopter?->phone ?? 'N/A') . '"
                    data-residence="' . ($item->adopter?->residence_type ?? 'N/A') . '"
                    data-ownership="' . ($item->adopter?->house_ownership ?? 'N/A') . '"
                    data-pettype="' . ($item->adopter?->household_pettype ?? 'N/A') . '"
                    data-petage="' . ($item->adopter?->household_petage ?? 'N/A') . '"
                    data-procedure="' . ($item->adopter?->household_petprocedure ?? 'N/A') . '"
                    data-vaccination="' . ($item->adopter?->household_petvaccination ?? 'N/A') . '"
                    // data-animaltype="' . ($item->adopter?->prefered_animaltype ?? 'N/A') . '"
                    // data-petgender="' . ($item->adopter?->prefered_petgender ?? 'N/A') . '"
                    // data-petsize="' . ($item->adopter?->prefered_petsize ?? 'N/A') . '"
                    // data-activitylevel="' . ($item->adopter?->prefered_activitylevel ?? 'N/A') . '"
                    data-aboutshelter="' . ($item->adopter?->about_shelter ?? 'N/A') . '"
                    data-reasonadopting="' . ($item->adopter?->reason_adopting ?? 'N/A') . '"
                    data-modaltitle="View">
                        <i class="bi bi-eye fs-3"></i>
                 </a>
                
                 <a class="edit-btn" href="javascript:void(0)" 
                    data-id="' .  ($item->adopter?->id ?? 'N/A') . '"
                    data-profile="' . $item->profile . '"
                    data-fullname="' . ($item->adopter?->fullname ?? 'N/A') . '"
                    data-bday="' . ($item->adopter?->bday ?? 'N/A') . '"
                    data-username="' . $item->username . '"
                    data-email="' . $item->email . '"
                    data-city="' . ($item->adopter?->city ?? 'N/A') . '"
                    data-state="' . ($item->adopter?->state ?? 'N/A') . '"
                    data-zipcode="' . ($item->adopter?->zipcode ?? 'N/A') . '"
                    data-phone="' . ($item->adopter?->phone ?? 'N/A') . '"
                    data-residence="' . ($item->adopter?->residence_type ?? 'N/A') . '"
                    data-ownership="' . ($item->adopter?->house_ownership ?? 'N/A') . '"
                    data-pettype="' . ($item->adopter?->household_pettype ?? 'N/A') . '"
                    data-petage="' . ($item->adopter?->household_petage ?? 'N/A') . '"
                    data-procedure="' . ($item->adopter?->household_petprocedure ?? 'N/A') . '"
                    data-vaccination="' . ($item->adopter?->household_petvaccination ?? 'N/A') . '"
                    // data-animaltype="' . ($item->adopter?->prefered_animaltype ?? 'N/A') . '"
                    // data-petgender="' . ($item->adopter?->prefered_petgender ?? 'N/A') . '"
                    // data-petsize="' . ($item->adopter?->prefered_petsize ?? 'N/A') . '"
                    // data-activitylevel="' . ($item->adopter?->prefered_activitylevel ?? 'N/A') . '"
                    data-aboutshelter="' . ($item->adopter?->about_shelter ?? 'N/A') . '"
                    data-reasonadopting="' . ($item->adopter?->reason_adopting ?? 'N/A') . '"
                    data-modaltitle="Edit">
                   <i class="bi bi-pencil-square fs-3"></i>
                 </a>

                 <a class="delete-btn" href="javascript:void(0)" data-id="' . ($item->adopter?->id ?? 'N/A') . '">
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
            'bday' => 'required|date',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'required|integer',
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
            'residence' => 'required|string',
            'ownership' => 'required|string',
            'petType' => 'required|string',
            'petAge' => 'required|integer',
            'petProcedure' => 'required|string',
            'vaccination' => 'required|string',
            // 'animaltype' => 'required|string',
            // 'petgender' => 'required|string',
            // 'size' => 'required|string',
            // 'activitylevel' => 'required|string',
            'aboutShelter' => 'required|string',
            'reasonAdopting' => 'required|string',
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
            'password' =>  Hash::make($request->input('password'))
        ]);

        if ($user) {
            $adopter = Adopter::create([
                'user_id' => $user->id,
                'fullname' => $request->name,
                'bday' => $request->bday,
                'city' => $request->city,
                'state' => $request->state,
                'zipcode' => $request->zipcode,
                'phone' => $request->phone,
                'residence_type' => $request->residence,
                'house_ownership' => $request->ownership,
                'household_pettype' => $request->petType,
                'household_petage' => $request->petAge,
                'household_petprocedure' => $request->petProcedure,
                'household_petvaccination' => $request->vaccination,
                // 'prefered_animaltype' => $request->animaltype,
                // 'prefered_petgender' => $request->petgender,
                // 'prefered_petsize' => $request->size,
                // 'prefered_activitylevel' => $request->activitylevel,
                'about_shelter' => $request->aboutShelter,
                'reason_adopting' => $request->reasonAdopting

            ]);

            if($adopter){
                AdoptionApplication::create([
                    'adopter_id' => $adopter->id,
                    'animal_id' => $request->animal_id,
                    'application_date' => Carbon::now(),
                ]);
            }

            $user->notify(new UserCredentialsNotification($user->username, $user->email, 'Adopter', $request->input('password')));
        }

        return response()->json([
            'message' => 'Adopter details saved successfully',
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


        $adopter = Adopter::where('id', $id)->first();

        if (!$adopter) {
            return response()->json(['error' => 'Adopter not found'], 404);
        }

        $user = User::find($adopter->user_id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }


        // Validate incoming request data
        $request->validate([
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'name' => 'required|string',
            'bday' => 'required|date',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'required|integer',
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
            'residence' => 'required|string',
            'ownership' => 'required|string',
            'petType' => 'required|string',
            'petAge' => 'required|integer',
            'petProcedure' => 'required|string',
            'vaccination' => 'required|string',
            // 'animaltype' => 'required|string',
            // 'petgender' => 'required|string',
            // 'size' => 'required|string',
            // 'activitylevel' => 'required|string',
            'aboutShelter' => 'required|string',
            'reasonAdopting' => 'required|string',
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

        $adopter->update([
            'fullname' => $request->name,
            'bday' => $request->bday,
            'city' => $request->city,
            'state' => $request->state,
            'zipcode' => $request->zipcode,
            'phone' => $request->phone,
            'residence_type' => $request->residence,
            'house_ownership' => $request->ownership,
            'household_pettype' => $request->petType,
            'household_petage' => $request->petAge,
            'household_petprocedure' => $request->petProcedure,
            'household_petvaccination' => $request->vaccination,
            // 'prefered_animaltype' => $request->animaltype,
            // 'prefered_petgender' => $request->petgender,
            // 'prefered_petsize' => $request->size,
            // 'prefered_activitylevel' => $request->activitylevel,
            'about_shelter' => $request->aboutShelter,
            'reason_adopting' => $request->reasonAdopting
        ]);

        $user->notify(new UserCredentialsNotification($user->username, $user->email, 'Adopter', $request->password ? $request->input('password') : null));

        return response()->json(['message' => 'Adopter details updated successfully', 'type' => 'success']);
    }

    public function filter_pet_type(Request $request)
    {
        $animalType = $request->input('animaltype');
        $animals = Pet::where('species', $animalType)->get();

        return response()->json(['data' => $animals]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $adopter = Adopter::find($id);

        if (!$adopter) {
            return response()->json(['error' => 'Adopter not found'], 404);
        }

        $user = User::find($adopter->user_id);

        if ($user) {
            if ($user->profile && file_exists(public_path($user->profile))) {
                unlink(public_path($user->profile));
            }
        }

        // Use a transaction to ensure all operations succeed together
        DB::transaction(function () use ($adopter) {
            $adopter->delete();
        });

        return response()->json(['message' => 'Adopter details deleted successfully', 'type' => 'success']);
    }
}
