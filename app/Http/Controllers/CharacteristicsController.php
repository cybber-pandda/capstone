<?php

namespace App\Http\Controllers;

use App\Models\Characteristic;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CharacteristicsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Characteristics Setting';

        return view('pages.back.v_characteristics', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $characteristics = Characteristic::orderBy('id', 'DESC')->whereNull('deleted_at')->get();

        $formattedData =  $characteristics->map(function ($item) {
            return [
                'name' => $item->name,
                'actions' => '<a class="edit-btn" href="javascript:void(0)" data-id="' . $item->id . '"  data-name="' . $item->name . '" data-modaltitle="Edit">
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
            'name' => 'required|string|max:255|unique:characteristics,name'
        ]);

        Characteristic::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Characteristic added successfully', 'type' => 'success']);

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
            'name' => 'required|string|max:255|unique:animal_types,name,' . $id
        ]);

        $character = Characteristic::findOrFail($id);
        $character->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Characteristic updated successfully', 'type' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $characteristics = Characteristic::findOrFail($id);
        $characteristics->delete();

        return response()->json(['message' => 'Characteristic deleted successfully', 'type' => 'success']);
    }
}
