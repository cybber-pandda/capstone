<?php

namespace App\Http\Controllers;

use App\Models\QRCode;
use App\Models\Pet;
use App\Models\Shelter;
use App\Models\AnimalType;
use App\Models\Characteristic;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PetsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'All Pets';

        $animalType = AnimalType::pluck('name', 'name')->toArray();
        $isShelterAdmin = false;

        if (auth()->check() && auth()->user()->role === 'shelterowner/admin') {
            $shelters = Shelter::where('user_id', auth()->user()->id)->pluck('shelter_name', 'id')->toArray();
            $isShelterAdmin = true;
        } else {
            $shelters = Shelter::pluck('shelter_name', 'id')->toArray();
            $isShelterAdmin = false;
        }


        return view('pages.back.v_pets', compact('page', 'animalType', 'shelters', 'isShelterAdmin'));
    }

    public function rescued_pet()
    {
        $page = 'Rescued Pets';
        return view('pages.back.v_rescuedpets', compact('page'));
    }

    public function surrendered_pet()
    {
        $page = 'Surrendered Pets';
        return view('pages.back.v_rescuedpets', compact('page'));
    }

    public function incare_pet()
    {
        $page = 'In-Care Pets';
        return view('pages.back.v_incarepets', compact('page'));
    }

    public function adopted_pet()
    {
        $page = 'Adopted Pets';
        return view('pages.back.v_adoptedpets', compact('page'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $dataType = $request->query('data');
        $filters = $request->query('filters');

        if (auth()->check() && auth()->user()->role === 'shelterowner/admin') {
            $userShelter = Shelter::where('user_id', auth()->id())->first();

            if (!$userShelter) {
                return response()->json(['data' => [], 'message' => 'User shelter not found'], 200);
            }

            $currentStatusMap = [
                'rescueddata' => 'rescued',
                'surrendereddata' => 'surrendered',
                'incaredata' => 'in-care',
                'adopteddata' => 'adopted',
            ];

            $currentStatus = $currentStatusMap[$dataType] ?? 'available';

            // $animals = Pet::with(['healthRecords', 'qrCode', 'shelter'])
            //     ->where('shelter_id', $userShelter->id)
            //     // ->where('current_status', $currentStatus)
            //     ->where('deleted_at', null)
            //     ->orderBy('id', 'DESC')
            //     ->get();


            $animals = Pet::with(['healthRecords', 'qrCode', 'shelter'])
                ->where('shelter_id', $userShelter->id)
                ->where('deleted_at', null);

            // Apply filters based on selected options
            if ($filters) {
                $filtersArray = explode(',', $filters);
                $animals->where(function ($query) use ($filtersArray) {
                    foreach ($filtersArray as $filter) {
                        // Adjust the following logic based on how you want to filter.
                        // For example, filtering by species, gender, etc.
                        $query->orWhere('species', $filter)
                            ->orWhere('gender', $filter)
                            ->orWhere('current_status', $filter);
                    }
                });
            }

            $animals = $animals->orderBy('id', 'DESC')->get();
        } else {
            $animals = Pet::with(['healthRecords', 'qrCode', 'shelter'])
                ->where('deleted_at', null)
                ->orderBy('id', 'DESC')
                ->get();
        }


        $FormattedData = $animals->map(function ($item) use ($dataType) {


            $photos = array_map('trim', explode(',', $item->photo));
            $totalPhotos = count($photos);

            // Limit to showing at most 3 photos, and calculate any remaining photos for the "+X" indicator
            $photosToShow = array_slice($photos, 0, 3);
            $remainingPhotosCount = $totalPhotos - count($photosToShow);

            $photosHtml = '<div class="avatar-group">';

            foreach ($photosToShow as $key => $photo) {
                if (!empty($photo)) {
                    $photosHtml .= '<span class="avatar avatar-sm">
                            <img src="' . e($photo) . '" alt="Pet Photo ' . ($key + 1) . '" class="rounded-circle imgtooltip" data-template="template' . $key . '">
                            <span id="template' . $key . '" class="d-none">
                                <span>Photo ' . ($key + 1) . '</span>
                            </span>
                        </span>';
                }
            }

            if ($remainingPhotosCount > 0) {
                $photosHtml .= '<span class="avatar avatar-sm">
                        <span class="avatar-initials rounded-circle bg-light text-dark">+' . $remainingPhotosCount . '</span>
                    </span>';
            }

            $photosHtml .= '</div>';


            return [
                'photos' => $photosHtml,
                'video' => $item->video_url,
                'name' => $item->name,
                'species' => $item->species,
                'breed' => $item->breed,
                'age' => $item->age,
                'gender' => $item->gender,
                'datecreated' => $item->registration_date,
                'color' => $item->color,
                'size' => $item->size,
                'characteristics' => $item->characteristics,
                'status' => $item->current_status,
                'actions' => (auth()->user()->role === 'shelterowner/admin' && $dataType === 'alldata') ?
                    '
                        <a class="view-btn" href="javascript:void(0)"   
                            data-id="' . $item->id . '"
                            data-photo="' . $item->photo . '"
                            data-video="' . $item->video_url . '"
                            data-name="' . $item->name . '"
                            data-species="' . $item->species . '"
                            data-breed="' . $item->breed . '"
                            data-age="' . $item->age . '"
                            data-gender="' . $item->gender . '"
                            data-color="' . $item->color . '"
                            data-size="' . $item->size . '"
                            data-characteristics="' . $item->characteristics . '"
                            data-medicalhistory="' . $item->medical_history . '"
                            data-petstory="' . $item->pet_story . '"
                            data-sheltername="' . ($item->shelter->shelter_name ?? 'N/A') . '"
                            data-shelteraddress="' . ($item->shelter->shelter_address ?? 'N/A') . '"
                            data-regdate="' . $item->registration_date . '"
                            data-qrcodeurl="' . htmlspecialchars($item->qrCode->qr_code_image_url ?? 'N/A') . '"
                            data-modaltitle="View">
                            <i class="bi bi-eye fs-3"></i>
                        </a>
                        
                        <a class="edit-btn" href="javascript:void(0)" 
                            data-id="' . $item->id . '"
                            data-photo="' . $item->photo . '"
                            data-video="' . $item->video_url . '"
                            data-name="' . $item->name . '"
                            data-species="' . $item->species . '"
                            data-breed="' . $item->breed . '"
                            data-age="' . $item->age . '"
                            data-gender="' . $item->gender . '"
                            data-color="' . $item->color . '"
                            data-size="' . $item->size . '"
                            data-characteristics="' . $item->characteristics . '"
                            data-medicalhistory="' . $item->medical_history . '"
                            data-petstory="' . $item->pet_story . '"
                            data-shelter="' . $item->shelter_id . '"
                            data-status="' . $item->current_status . '"
                            data-renote="' . $item->isRescuedNote . '"
                            data-sunote="' . $item->isSurrenderedNote . '"
                            data-modaltitle="Edit">
                            <i class="bi bi-pencil-square fs-3"></i>
                        </a>
        
                        <a class="delete-btn" href="javascript:void(0)" data-id="' . $item->id . '">
                            <i class="bi bi-trash fs-3"></i>
                        </a>
                    ' :
                    '
                        <a class="view-btn" href="javascript:void(0)"   
                            data-id="' . $item->id . '"
                            data-photo="' . $item->photo . '"
                            data-video="' . $item->video_url . '"
                            data-name="' . $item->name . '"
                            data-species="' . $item->species . '"
                            data-breed="' . $item->breed . '"
                            data-age="' . $item->age . '"
                            data-gender="' . $item->gender . '"
                            data-color="' . $item->color . '"
                            data-medicalhistory="' . $item->medical_history . '"
                            data-petstory="' . $item->pet_story . '"
                            data-sheltername="' . ($item->shelter->shelter_name ?? 'N/A') . '"
                            data-shelteraddress="' . ($item->shelter->shelter_address ?? 'N/A') . '"
                            data-regdate="' . $item->registration_date . '"
                            data-qrcodeurl="' . htmlspecialchars($item->qrCode->qr_code_image_url ?? 'N/A') . '"
                            data-modaltitle="View">
                            <i class="bi bi-eye fs-3"></i>
                        </a>
                    '
            ];
        });

        return response()->json(['data' => $FormattedData]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'video_url' => 'nullable',
            'url',
            'regex:/^https:\/\/.*/',
            'name' => 'required|string',
            'species' => 'required|string',
            'breed' => 'required|string',
            'age' => 'required|string',
            'gender' => 'required|string',
            'color' => 'required|string',
            'size' => 'required|string',
            'characteristics' => 'required|string',
            'shelter' => 'required|integer',
            'description' => 'required',
            'status' => 'required',
            'pet_story' => 'nullable|string',
        ], [
            'description.required' => 'The medical history is required'
        ]);

        // $imagePath = null;

        // if ($request->hasFile('photo')) {
        //     $imgFile = $request->file('photo');
        //     $filename = time() . '_' . $imgFile->getClientOriginalName();
        //     $imagePath = 'assets/uploads/' . $filename;
        //     $imgFile->move(public_path('assets/uploads'), $filename);
        // }

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $imgFile) {
                // Create a unique filename for each photo
                $filename = Str::random(10) . '_' . time() . '.' . $imgFile->getClientOriginalExtension();
                $imagePath = 'assets/uploads/' . $filename;

                $imgFile->move(public_path('assets/uploads'), $filename);

                $photoPaths[] = $imagePath;
            }
        }

        $animal = Pet::create([
            'photo' => implode(',', $photoPaths),
            'video_url' => $request->video_url,
            'name' => $request->name,
            'species' => $request->species,
            'breed' => $request->breed,
            'age' => $request->age,
            'gender' => $request->gender,
            'color' => $request->color,
            'size' => $request->size,
            'characteristics' => $request->characteristics,
            'medical_history' => $request->description,
            'pet_story' => $request->pet_story,
            'shelter_id' => $request->shelter,
            'isRescuedNote' => $request->rescued_note,
            'isSurrenderedNote' => $request->surrendered_note,
            'registration_date' => Carbon::now()->format('Y-m-d'),
            'current_status' => $request->status,
        ]);

        $url = route('pet.details', ['name' => $animal->name, 'id' => $animal->id]);

        QRCode::create([
            'animal_id' => $animal->id,
            'qr_code_image_url' => $url,
        ]);

        return response()->json(['message' => 'Animal added successfully', 'type' => 'success']);
    }


    public function show($id)
    {
        $page = "Pet Details";

        $pet = Pet::with(['shelter', 'qrCode'])->find($id);
        $animalType = AnimalType::pluck('name', 'name')->toArray();
        $characteristics = Characteristic::pluck('name', 'name')->toArray();
        

        $menus = DB::table('frontmenu')->where('deleted_at',null)->get();
        $companysettings = DB::table('company_settings')->first();
        $socialmedias = DB::table('socialmedias')->get();
        $terms = DB::table('terms_conditions')
        ->select('content_type', 'content')
        ->get();

        return view('pages.front.a_animaldetails', compact('page', 'pet', 'animalType', 'characteristics','terms','menus','companysettings','socialmedias'));
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
        $request->validate([
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'video_url' => 'nullable',
            'url',
            'regex:/^https:\/\/.*/',
            'name' => 'required|string',
            'species' => 'required|string',
            'breed' => 'required|string',
            'age' => 'required|string',
            'gender' => 'required|string',
            'color' => 'required|string',
            'size' => 'required|string',
            'characteristics' => 'required|string',
            'shelter' => 'required|integer',
            'description' => 'required',
            'status' => 'required',
            'pet_story' => 'nullable|string',
        ], [
            'description.required' => 'The medical history is required'
        ]);

        $animal = Pet::findOrFail($id);


        // if ($request->hasFile('photo')) {
        //     // Check if the animal has an existing photo and delete it
        //     if ($animal->photo && file_exists(public_path($animal->photo))) {
        //         unlink(public_path($animal->photo));
        //     }

        //     // Process the new uploaded photo
        //     $imgFile = $request->file('photo');
        //     $filename = time() . '_' . $imgFile->getClientOriginalName();
        //     $imagePath = 'assets/uploads/' . $filename;
        //     $imgFile->move(public_path('assets/uploads'), $filename);
        // }

        // Get existing photos if no new ones are provided
        $photoPaths = explode(',', $animal->photo);

        if ($request->hasFile('photos')) {
            // Delete existing photos from storage
            foreach ($photoPaths as $photo) {
                $photoPath = public_path($photo);
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }

            // Clear the photo paths since new photos will replace them
            $photoPaths = [];

            // Process new photo uploads
            foreach ($request->file('photos') as $imgFile) {
                $filename = Str::random(10) . '_' . time() . '.' . $imgFile->getClientOriginalExtension();
                $imagePath = 'assets/uploads/' . $filename;

                $imgFile->move(public_path('assets/uploads'), $filename);
                $photoPaths[] = $imagePath; // Add new path to array
            }
        }

        $animal->update([
            'photo' => implode(',', $photoPaths),
            'video_url' => $request->video_url,
            'name' => $request->name,
            'species' => $request->species,
            'breed' => $request->breed,
            'age' => $request->age,
            'gender' => $request->gender,
            'color' => $request->color,
            'size' => $request->size,
            'characteristics' => $request->characteristics,
            'medical_history' => $request->description,
            'pet_story' => $request->pet_story,
            'shelter_id' => $request->shelter,
            'isRescuedNote' => $request->rescued_note,
            'isSurrenderedNote' => $request->surrendered_note,
            'current_status' => $request->status,
        ]);

        return response()->json(['message' => 'Animal updated successfully', 'type' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $animal = Pet::findOrFail($id);
        $animal->delete();

        return response()->json(['message' => 'Animal deleted successfully', 'type' => 'success']);
    }

    public function generatePdf(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'pettype' => 'required|string',
        ]);

        if ($request->pettype === 'alldata') {
            $pets = Pet::whereBetween('registration_date', [$request->start, $request->end])->get();
        } else {
            $pets = Pet::where('current_status', $request->pettype)->whereBetween('registration_date', [$request->start, $request->end])->get();
        }


        $pdf = Pdf::loadView('pdf.pets_report', [
            'pets' => $pets,
            'start' => $request->start,
            'end' => $request->end,
        ]);

        return $pdf->download('pets_report_' . $request->start . '_to_' . $request->end . '.pdf');
    }
}
