<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

use App\Models\Bank;

class BankManagementController extends Controller
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
            $bank = Bank::select(['id', 'name', 'image', 'account_number', 'created_at']);

            return DataTables::of($bank)
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

        return view('pages.superadmin.v_bank', [
            'page' => 'Bank',
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
            'account_number' => 'required|string',
            'image' => 'nullable|image|max:2048'
        ]);

        $path = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'assets/upload/bank';
            $file->move(public_path($destinationPath), $fileName);

            $path = $destinationPath . '/' . $fileName;
        }

        Bank::create([
            'name' => $request->name,
            'account_number' => $request->account_number,
            'image' => $path,
        ]);

        return response()->json([
            'type' => 'success',
            'message' => 'Bank created successfully.',
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
        $bank = Bank::findOrFail($id);
        return response()->json([
            'data' => $bank
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
        $bank = Bank::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'required|string',
            'image' => 'nullable|image|max:2048'
        ]);

        $path = $bank->image;

        if ($request->hasFile('image')) {
            // Unlink old image if it exists
            if ($bank->image && file_exists(public_path($bank->image))) {
                unlink(public_path($bank->image));
            }

            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'assets/upload/bank';
            $file->move(public_path($destinationPath), $fileName);

            $path = $destinationPath . '/' . $fileName;
        }

        $bank->update([
            'name' => $request->name,
            'account_number' => $request->account_number,
            'image' => $path,
        ]);

        return response()->json([
            'type' => 'success',
            'message' => 'Bank updated successfully.',
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
        $bank = Bank::findOrFail($id);
        $bank->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Bank deleted successfully.',
        ]);
    }
}
