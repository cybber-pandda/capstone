<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Notification;

class UserManagementController extends Controller
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
            $status = $request->get('status');

            $users = User::select(['id', 'name', 'profile', 'username', 'email', 'role', 'created_at'])
                ->whereIn('role', ['b2b', 'deliveryrider', 'salesofficer'])
                ->where('status', $status);

            return DataTables::of($users)
                ->addColumn('profile', function ($row) {
                    $image = $row->profile
                        ? asset($row->profile)
                        : asset('assets/dashboard/images/noimage.png');

                    return '<img src="' . $image . '" alt="Profile" class="img-thumbnail" width="80">';
                })
                ->addColumn('action', function ($row) use ($status) {
                    $buttons = '';

                    // View button
                    $buttons .= '
                            <button class="btn btn-sm btn-inverse-light mx-1 view-details-btn" data-id="' . $row->id . '">
                                <i class="link-icon" data-lucide="eye"></i>
                            </button>
                        ';

                    if ($status == 1) {
                        $buttons .= '
                            <button class="btn btn-sm btn-inverse-danger mx-1 toggle-status-btn" data-id="' . $row->id . '" data-action="deactivate">
                                <i class="link-icon" data-lucide="x-circle"></i>
                            </button>
                        ';
                    } else {
                        $buttons .= '
                            <button class="btn btn-sm btn-inverse-success mx-1 toggle-status-btn" data-id="' . $row->id . '" data-action="activate">
                                <i class="link-icon" data-lucide="check-circle"></i>
                            </button>
                        ';
                    }

                    return $buttons;
                })

                ->rawColumns(['profile', 'action'])
                ->make(true);
        }

        return view('pages.superadmin.v_userManagement', [
            'page' => 'User Management',
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::with('userLog')->findOrFail($id);

        $html = view('components.user-details', compact('user'))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $user = User::findOrFail($id);

        if ($request->has('action')) {
            if ($request->action === 'activate') {
                $user->status = 1;
                $message = 'Your account has been activated. You may now log in.';
            } elseif ($request->action === 'deactivate') {
                $user->status = 0;
                $message = 'Your account has been deactivated. Please contact support for more information.';
            }

            $user->save();

            Notification::create([
                'user_id' => $user->id,
                'type' => 'account',
                'message' => $message,
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Invalid action'], 400);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
