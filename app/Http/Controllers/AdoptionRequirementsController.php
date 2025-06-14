<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shelter;
use App\Models\AdoptionRequirement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdoptionRequirementsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Shelter Form Requirements';

        //$animalType = AnimalType::pluck('name', 'name')->toArray();

        return view('pages.back.v_requirements', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $user = User::getCurrentUser();

        $requirements = AdoptionRequirement::where('deleted_at', null)
            ->orderBy('id', 'DESC')
            ->get();

        $formattedData = $requirements->map(function ($item) {
            return [
                'type' => $item->requirement_type,
                'name' => $item->requirement_name,
                'status' => $item->status,
                'actions' =>
                ' <a class="edit-btn" href="javascript:void(0)" 
                     data-id="' . $item->id . '"
                     data-type="' . $item->requirement_type . '"
                     data-name="' . $item->requirement_name . '"
                     data-status="' . $item->status . '"
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
            'requirement_name' => 'required|string',
            'requirement_type' => 'required|string',
            'status' => 'required|string'
        ]);


        $user = User::getCurrentUser();

        $shelter = Shelter::where('user_id', $user->id)->first();

        if (!$shelter) {
            return response()->json(['error' => 'Shelter not found'], 404);
        }

        AdoptionRequirement::create([
            'shelter_id' =>  $shelter->id,
            'requirement_type' => $request->requirement_type,
            'requirement_name' => $request->requirement_name,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Requirement saved successfully',
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

        $requirement = AdoptionRequirement::where('id', $id)->first();

        if (!$requirement) {
            return response()->json(['error' => 'Requirement not found'], 404);
        }

        $request->validate([
            'requirement_name' => 'required|string',
            'requirement_type' => 'required|string',
            'status' => 'required|string'
        ]);

        $requirement->update([
            'requirement_type' => $request->requirement_type,
            'requirement_name' => $request->requirement_name,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Requirement updated successfully',
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
        $requirement = AdoptionRequirement::where('id', $id)->first();
        $requirement->delete();

        return response()->json(['message' => 'Requirement deleted successfully', 'type' => 'success']);
    }
}
