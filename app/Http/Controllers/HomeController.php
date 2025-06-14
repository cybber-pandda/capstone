<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pet;
use App\Models\AnimalType;
use App\Models\Shelter;
use App\Models\Characteristic;
use App\Models\UserSecurityQuestion;
use App\Models\UserDetails;
use App\Models\AdoptionApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $page = 'PawPanion';
        $animalType = [];
        $characteristics = [];
        $shelterNames =  [];
        $animalCounts =  [];
        $shelterLimits =  [];

        $shelterList = [];

        $countAllPet = 0;
        $countAllDog = 0;
        $countAllCat = 0;

        $countAdopted = 0;
        $countIncare = 0;
        $countRescued = 0;
        $countSurrendered = 0;

        $countPendingForm = 0;
        $countUnderReviewForm = 0;
        $countApprovedForm = 0;
        $countRejectedForm = 0;

        $user = User::getCurrentUser();

        if (auth()->check() && auth()->user()->role === 'superadmin') {

            $shelters = Shelter::select('shelters.*')
                ->withCount('animals') // Get the animal count in each shelter
                ->get();


            // $shelterRates = Shelter::withCount([
            //     'animals',
            //     'adoptionApplications' => function ($query) {
            //         $query->where('status', 'approved'); // there is already 2 approved but still 0 display
            //     }
            // ])->get();

            $shelterRates = Shelter::withCount([
                'animals',
                'animals as dogs_count' => function ($query) {
                    $query->where('species', 'dog');
                },
                'animals as cats_count' => function ($query) {
                    $query->where('species', 'cat');
                },
                'animals as adopted' => function ($query) {
                    $query->where('current_status', 'adopted');
                },
                'animals as incare' => function ($query) {
                    $query->where('current_status', 'in-care');
                },
                'animals as rescued' => function ($query) {
                    $query->where('current_status', 'rescued');
                },
                'animals as surrendered' => function ($query) {
                    $query->where('current_status', 'surrendered');
                },
                'adoptionApplications as approved_adoption_applications_count' => function ($query) {
                    $query->where('status', 'approved');
                },
                'adoptionApplications as under_review_adoption_applications_count' => function ($query) {
                    $query->where('status', 'under-review');
                },
                'adoptionApplications as rejected_adoption_applications_count' => function ($query) {
                    $query->where('status', 'rejected');
                },
                'adoptionApplications as pending_adoption_applications_count' => function ($query) {
                    $query->where('status', 'pending');
                }
            ])->get(); // there will be animal_count and adopted_application_count included in data due to relationaship to the model and using withCount function made that.

            $shelterNames = $shelters->pluck('shelter_name')->toArray();
            $animalCounts = $shelters->pluck('animals_count')->toArray();
            $shelterLimits = $shelters->pluck('shelter_limit_population')->toArray();

            return view('pages.back.v_home', compact('page', 'animalType', 'characteristics', 'shelterNames', 'shelterList', 'animalCounts', 'shelterLimits', 'shelterRates'));
        } elseif (auth()->check() && auth()->user()->role === 'shelterowner/admin') {
            $shelter = Shelter::where('user_id', $user->id)->first();
            $shelterId = $shelter->id ?? null;

          

            $animals = Pet::where('shelter_id', $shelterId)
                ->whereNull('deleted_at')
                ->get();

            $countAllPet = Pet::where('shelter_id', $shelter->id)->count();

            $countAllDog = Pet::where('shelter_id', $shelter->id)->where('species', 'Dog')->count();

            $countAllCat = Pet::where('shelter_id', $shelter->id)->where('species', 'Cat')->count();

            $countAdopted = Pet::where('shelter_id', $shelter->id)->where('current_status', 'adopted')->count();
            $countIncare = Pet::where('shelter_id', $shelter->id)->where('current_status', 'in-care')->count();
            $countRescued = Pet::where('shelter_id', $shelter->id)->where('current_status', 'rescued')->count();
            $countSurrendered = Pet::where('shelter_id', $shelter->id)->where('current_status', 'surrendered')->count();

            $countUnderReviewForm = AdoptionApplication::whereHas('animal', function ($query) use ($shelterId) {
                $query->where('shelter_id', $shelterId);
            })->where('status', 'under-review')->count();

            $countPendingForm = AdoptionApplication::whereHas('animal', function ($query) use ($shelterId) {
                $query->where('shelter_id', $shelterId);
            })->where('status', 'pending')->count();

            $countApprovedForm = AdoptionApplication::whereHas('animal', function ($query) use ($shelterId) {
                $query->where('shelter_id', $shelterId);
            })->where('status', 'approved')->count();

            $countRejectedForm = AdoptionApplication::whereHas('animal', function ($query) use ($shelterId) {
                $query->where('shelter_id', $shelterId);
            })->where('status', 'rejected')->count();

            return view('pages.back.v_home', compact(
                'page',
                'animals',
                'animalType',
                'characteristics',
                'countAllPet',
                'countAllDog',
                'countAllCat',
                'countAdopted',
                'countIncare',
                'countRescued',
                'countSurrendered',
                'countPendingForm',
                'countUnderReviewForm',
                'countApprovedForm',
                'countRejectedForm',
                'shelterNames',
                'animalCounts',
                'shelterLimits',
                'shelterList',
        
            ));
        } else {
            return redirect()->route('welcome');
        }
    }

    public function user_details_form(Request $request)
    {

        $user = User::getCurrentUser();

        $request->validate([
            'name' => 'required|string',
            'bday' => 'required|date',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'required|integer',
            'phone' => ['required', 'regex:/^09\d{9}$/'],
        ]);

        if ($user->role === 'shelterowner/admin') {
            $request->validate([
                'sheltername' => 'required|string',
                'shelteraddress' => 'required',
                'shelterpopulation' => 'required|integer',
            ]);
        }

        $userdetails = UserDetails::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'fullname' => $request->name,
                'birthday' => $request->bday,
                'city' => $request->city,
                'state' => $request->state,
                'zipcode' => $request->zipcode,
                'phone' => $request->phone
            ]
        );

        if ($userdetails) {

            $updateDetails = User::where('id', $user->id)->update([
                'user_details' => 1
            ]);

            if ($updateDetails &&  $user->role === 'shelterowner/admin') {
                Shelter::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'user_id' => $user->id,
                        'owner_name' => $request->name,
                        'owner_phone' => $request->phone,
                        'shelter_name' => $request->sheltername,
                        'shelter_address' => $request->shelteraddress,
                        'shelter_limit_population' => $request->shelterpopulation,
                    ]
                );
            }
        }

        return response()->json([
            'message' => 'User details saved successfully',
            'type' => 'success'
        ]);
    }

    public function save_security_auth(Request $request)
    {


        $request->validate([
            'security_question' => 'required|string',
            'security_answer' => 'required|string',
        ]);

        $user = User::getCurrentUser();

        $existingQuestion = UserSecurityQuestion::where('user_id', $user->id)->first();

        if ($existingQuestion) {
            return response()->json([
                'message' => 'You have already set a security question.',
                'type' => 'error'
            ], 400);
        }

        $securityQuestion = UserSecurityQuestion::create(
            [
                'user_id' => $user->id,
                'questions' => $request->security_question,
                'answer' => $request->security_answer,
            ]
        );

        return response()->json([
            'message' => 'Security question saved successfully',
            'type' => 'success'
        ]);
    }
}
