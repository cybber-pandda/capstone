<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index() {
        return view('pages.b2b.v_delivery', [
            'page' => 'Track Delivery',
        ]);
    } 
}
