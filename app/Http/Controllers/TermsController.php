<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\Models\TermCondition;

class TermsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $terms = TermCondition::select(['id', 'name', 'image', 'description', 'created_at']);

            return DataTables::of($terms)
                ->addColumn('image', function ($row) {
                    if ($row->image) {
                        return '<img src="' . asset($row->image) . '" alt="' . $row->name . '" class="img-thumbnail">';
                    }
                    return '<img src="' . asset('assets/dashboard/images/noimage.png') . '" alt="No image" class="img-thumbnail">';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button type="button" class="btn btn-sm btn-inverse-light mx-1 edit p-2" data-id="' . $row->id . '">
                            <i class="link-icon" data-lucide="edit-3"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-inverse-danger delete p-2" data-id="' . $row->id . '">
                            <i class="link-icon" data-lucide="trash-2"></i>
                        </button>
                    ';
                })
                ->rawColumns(['image', 'action'])
                ->make(true);
        }

        return view('pages.terms', [
            'page' => 'Terms & Conditions',
            'pageterms' => 'Settings',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        $path = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'assets/upload/terms';
            $file->move(public_path($destinationPath), $fileName);

            $path = $destinationPath . '/' . $fileName;
        }

        TermCondition::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $path,
        ]);

        return response()->json([
            'type' => 'success',
            'message' => 'Terms & Condition created successfully.',
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
        $terms = TermCondition::findOrFail($id);
        return response()->json([
            'data' => $terms
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
        $terms = TermCondition::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        $path = $terms->image;

        if ($request->hasFile('image')) {
            // Unlink old image if it exists
            if ($terms->image && file_exists(public_path($terms->image))) {
                unlink(public_path($terms->image));
            }

            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'assets/upload/terms';
            $file->move(public_path($destinationPath), $fileName);

            $path = $destinationPath . '/' . $fileName;
        }

        $terms->update([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $path,
        ]);

        return response()->json([
            'type' => 'success',
            'message' => 'Terms & Condition updated successfully.',
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
        $terms = TermCondition::findOrFail($id);
        $terms->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Terms & Condition deleted successfully.',
        ]);
    }
}
