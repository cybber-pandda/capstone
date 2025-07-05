<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class B2BController extends Controller
{
    public function index() {
        return view('pages.b2b.v_profile', [
            'page' => 'My Profile',
        ]);
    } 
}
