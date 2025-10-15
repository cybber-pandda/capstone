<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

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
            $terms = TermCondition::select(['id', 'content_type', 'content', 'created_at']);

            return DataTables::of($terms)
                ->addColumn('action', function ($row) {
                    return '
                        <button type="button" class="btn btn-sm btn-inverse-light mx-1 edit p-2" data-id="' . $row->id . '">
                            <i class="link-icon" data-lucide="edit-3"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-inverse-danger delete p-2 d-none" data-id="' . $row->id . '">
                            <i class="link-icon" data-lucide="trash-2"></i>
                        </button>
                    ';
                })
                ->rawColumns(['action', 'content'])
                ->make(true);
        }

        return view('pages.terms', [
            'page' => 'Terms & Conditions',
            'pageCategory' => 'Settings',
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
            //'content_type' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        TermCondition::create([
            'content_type' => $request->content_type,
            'content' => $request->content,
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
            //'content_type' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $terms->update([
           // 'content_type' => $request->content_type,
            'content' => $request->content,
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
