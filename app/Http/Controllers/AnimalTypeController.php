<?php

namespace App\Http\Controllers;

use App\Models\AnimalType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnimalTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'Animal Type Setting';

        return view('pages.back.v_animaltypesettings', compact('page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $animaltypes = AnimalType::orderBy('id', 'DESC')->whereNull('deleted_at')->get();

        $formattedData = $animaltypes->map(function ($item) {
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
            'name' => 'required|string|max:255|unique:animal_types,name'
        ]);

        AnimalType::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Animal Type added successfully', 'type' => 'success']);

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

        $animaltype = AnimalType::findOrFail($id);
        $animaltype->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Animal Type updated successfully', 'type' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $animaltype = AnimalType::findOrFail($id);
        $animaltype->delete();

        return response()->json(['message' => 'Animal Type deleted successfully', 'type' => 'success']);
    }
}
