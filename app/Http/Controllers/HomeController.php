<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\b2bDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $page = 'TantucoCTC';
        $user = User::getCurrentUser();

        $view = match ($user->role) {
            'b2b' => 'pages.b2b.index',
            'deliveryrider/admin', 'assistantsales/admin' => 'pages.admin.index',
            'salesofficer/superadmin' => 'pages.superadmin.index',
            default => abort(404, 'Page not found.'),
        };

        return view($view, compact('page'));
    }


    public function b2b_details_form(Request $request)
    {

        $user = User::getCurrentUser();

        $request->validate([
            'name' => 'required|string',
            'bday' => 'required|date',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'required|integer',
            'phone' => ['required', 'regex:/^09\d{9}$/'],
        ]);

        if ($user->role === 'shelterowner/admin') {
            $request->validate([
                'sheltername' => 'required|string',
                'shelteraddress' => 'required',
                'shelterpopulation' => 'required|integer',
            ]);
        }

        $b2bdetails = b2bDetails::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'fullname' => $request->name,
                'birthday' => $request->bday,
                'city' => $request->city,
                'state' => $request->state,
                'zipcode' => $request->zipcode,
                'phone' => $request->phone
            ]
        );

        if ($b2bdetails) {

            $updateDetails = User::where('id', $user->id)->update([
                'b2b_details' => 1
            ]);

            // if ($updateDetails &&  $user->role === 'shelterowner/admin') {
            //     Shelter::updateOrCreate(
            //         ['user_id' => $user->id],
            //         [
            //             'user_id' => $user->id,
            //             'owner_name' => $request->name,
            //             'owner_phone' => $request->phone,
            //             'shelter_name' => $request->sheltername,
            //             'shelter_address' => $request->shelteraddress,
            //             'shelter_limit_population' => $request->shelterpopulation,
            //         ]
            //     );
            // }
        }

        return response()->json([
            'message' => 'User details saved successfully',
            'type' => 'success'
        ]);
    }
}
