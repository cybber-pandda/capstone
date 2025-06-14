<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function show($slug)
    {
        // Check if the view file exists
        $viewPath = resource_path("views/pages/front/{$slug}.blade.php");

        if (!file_exists($viewPath)) {
            abort(404);
        }

        $menus = DB::table('frontmenu')->where('deleted_at',null)->get();

        $wcus = DB::table('wcu_section')->get();

        $companysettings = DB::table('company_settings')->first();

        $socialmedias = DB::table('socialmedias')->get();

        $terms = DB::table('terms_conditions')
        ->select('content_type', 'content')
        ->get();

        $shelters = DB::table('shelters')
        ->select('users.profile','shelters.*')
        ->leftJoin('users', 'shelters.user_id', '=', 'users.id')
        ->where('shelters.deleted_at', null) 
        ->get();

        // Render the view
        return view("pages.front.{$slug}",compact('menus','wcus','companysettings','shelters','socialmedias','terms'));
    }
}
