<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use App\Notifications\UserCredentialsNotification;

use App\Models\User;

class DeliveryRiderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $deliveryRiders = User::select(['id', 'name', 'profile', 'username', 'email', 'created_at'])->where('role', 'deliveryrider/admin');

            return DataTables::of($deliveryRiders)
                ->addColumn('profile', function ($row) {
                    if ($row->profile) {
                        return '<img src="' . asset($row->profile) . '" alt="Profile" class="img-thumbnail" width="80">';
                    }
                    return '<img src="' . asset('assets/dashboard/images/noimage.png') . '" alt="No image" class="img-thumbnail" width="80">';
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
                ->rawColumns(['profile', 'action'])
                ->make(true);
        }

        return view('pages.superadmin.v_deliveryRider', [
            'page' => 'Delivery Rider',
            'pageCategory' => 'Account Creation',
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
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->firstname . ' ' . $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'deliveryrider/admin',
            'created_by_admin' => true,
            'force_password_change' => true,
        ]);


        $user->notify(new UserCredentialsNotification($user->name, $user->username, $user->email, 'Delivery Rider', $request->input('password')));


        return response()->json([
            'type' => 'success',
            'message' => 'Delivery rider created successfully!',
            'data' => $user,
        ], 200);
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
        $user = User::findOrFail($id);

        return response()->json([
            'data' => $user,
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
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->name = $request->firstname . ' ' . $request->lastname;
        $user->username = $request->username;
        $user->email = $request->email;

        $sendNotification = false;
        $plainPassword = null;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->force_password_change = true;
            $sendNotification = true;
            $plainPassword = $request->password; // store plain password for email
        }

        $user->save();

        if ($sendNotification) {
            $user->notify(new UserCredentialsNotification(
                $user->name,
                $user->username,
                $user->email,
                'Delivery Rider',
                $plainPassword
            ));
        }

        return response()->json([
            'type' => 'success',
            'message' => 'Delivery rider updated successfully!',
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
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Delivery rider deleted successfully!',
        ]);
    }
}
