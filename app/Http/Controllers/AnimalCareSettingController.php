<?php

namespace App\Http\Controllers;

use App\Models\AnimalCareSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnimalCareSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Animal Care Setting';

        return view('pages.back.v_animalcaresettings', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $animalcareSettings = AnimalCareSetting::orderBy('id', 'DESC')->get();

        $formattedData = $animalcareSettings->map(function ($item) {
            return [
                'tutorialquestion' => $item->tutorial_question,
                'photo' => $item->photo,
                'tutorialdescription' => $item->tutorial_description,
                'actions' => '<a class="edit-btn" href="javascript:void(0)" 
                    data-id="' . $item->id . '" 
                    data-tutorialquestion="' . $item->tutorial_question . '"
                    data-tutorialdescription="' . $item->tutorial_description . '" 
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
        $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'tutorial_question' => 'required|string|max:255|unique:animalcare_settings,tutorial_question',
            'description' => 'required|string'
        ]);

        $imagePath = null;

        if ($request->hasFile('photo')) {
            $imgFile = $request->file('photo');
            $filename = time() . '_' . $imgFile->getClientOriginalName();
            $imagePath = 'assets/uploads/' . $filename;
            $imgFile->move(public_path('assets/uploads'), $filename);
        }

        AnimalCareSetting::create([
            'tutorial_question' => $request->tutorial_question,
            'photo' => $imagePath,
            'tutorial_description' => $request->description,
        ]);

        return response()->json(['message' => 'Animal Care Setting added successfully', 'type' => 'success']);
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'tutorial_question' => 'required|string|max:255|unique:animalcare_settings,tutorial_question,' . $id,
            'description' => 'required|string'
        ]);

        $animalcare = AnimalCareSetting::findOrFail($id);

        $imagePath = $animalcare->photo;

        if ($request->hasFile('photo')) {

            if ($animalcare->photo && file_exists(public_path($animalcare->photo))) {
                unlink(public_path($animalcare->photo));
            }

            $imgFile = $request->file('photo');
            $filename = time() . '_' . $imgFile->getClientOriginalName();
            $imagePath = 'assets/uploads/' . $filename;
            $imgFile->move(public_path('assets/uploads'), $filename);
        }

        $animalcare->update([
            'tutorial_question' => $request->tutorial_question,
            'photo' => $imagePath,
            'tutorial_description' => $request->description,
        ]);

        return response()->json(['message' => 'Animal Care Setting updated successfully', 'type' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $animalcare = AnimalCareSetting::findOrFail($id);

        if ($animalcare) {
            if ($animalcare->photo && file_exists(public_path($animalcare->photo))) {
                unlink(public_path($animalcare->photo));
            }
        }

        $animalcare->delete();

        return response()->json(['message' => 'Animal Care Setting deleted successfully', 'type' => 'success']);
    }
}
