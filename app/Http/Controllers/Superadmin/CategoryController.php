<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
         // 1️⃣ If user is NOT logged in → show login page
        if (!Auth::check()) {
            $page = 'Sign In';
            $companysettings = DB::table('company_settings')->first();

            return response()
                ->view('auth.login', compact('page', 'companysettings'))
                ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
        }

        // 2️⃣ If user is logged in → check their role
        $user = Auth::user();

        // Example role logic (adjust 'role' and role names to match your database)
        
        if ($user->role === 'superadmin') {

        if ($request->ajax()) {
            $category = Category::select(['id', 'name', 'image', 'description', 'created_at']);

            return DataTables::of($category)
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

        return view('pages.superadmin.v_categories', [
            'page' => 'Category',
            'pageCategory' => 'Management',
        ]);}
        return redirect()->route('home')->with('info', 'Redirected to your dashboard.');
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
            $destinationPath = 'assets/upload/category';
            $file->move(public_path($destinationPath), $fileName);

            $path = $destinationPath . '/' . $fileName;
        }

        Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $path,
        ]);

        return response()->json([
            'type' => 'success',
            'message' => 'Category created successfully.',
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
        $category = Category::findOrFail($id);
        return response()->json([
            'data' => $category
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
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        $path = $category->image;

        if ($request->hasFile('image')) {
            // Unlink old image if it exists
            if ($category->image && file_exists(public_path($category->image))) {
                unlink(public_path($category->image));
            }

            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'assets/upload/category';
            $file->move(public_path($destinationPath), $fileName);

            $path = $destinationPath . '/' . $fileName;
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $path,
        ]);

        return response()->json([
            'type' => 'success',
            'message' => 'Category updated successfully.',
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
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Category deleted successfully.',
        ]);
    }
}
