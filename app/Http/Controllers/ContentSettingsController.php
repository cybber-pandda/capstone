<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Models\WCUSection;
use App\Models\TermCondition;
use App\Models\FrontMenu;
use App\Models\ContentSection;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ContentSettingsController extends Controller
{
    public function index()
    {
        $page = 'Content Setting';

        $menu = FrontMenu::get();

        // Filter out the "Home" option
        $menuOptions = $menu->filter(function ($item) {
            return $item->name !== 'Home';
        })->pluck('name', 'id')->toArray();

        return view('pages.back.v_contentsettings', compact('page', 'menuOptions'));
    }

    public function frontmenu_api()
    {
        //$frontmenu = FrontMenu::orderBy('id', 'DESC')->withTrashed()->get();
        $frontmenu = FrontMenu::orderBy('id', 'DESC')->get();

        // Transform data into the desired array format
        $formattedData = $frontmenu->map(function ($item) {
            return [
                'name' => $item->name,
                'url' => $item->url,
                'bannertitle' => $item->banner_title,
                'bannersubtitle' => $item->banner_sub_title,
                'bannerphoto' => $item->banner_photo,
                'actions' => '<a class="edit-btn-menu" href="javascript:void(0)" 
                data-id="' . $item->id . '"  
                data-name="' . $item->name . '" 
                data-url="' . $item->url . '" 
                data-bannertitle="' . $item->banner_title . '"
                data-bannersubtitle="' . $item->banner_sub_title . '" 
                data-modaltitle="Edit">
                <i class="bi bi-pencil-square fs-3"></i>
                </a>
                <a class="delete-btn-menu" href="javascript:void(0)" data-id="' . $item->id . '">
                    <i class="bi bi-trash fs-3"></i>
                </a>'
            ];
        });

        return response()->json(['menudata' => $formattedData]);
    }

    public function frontmenu_restore_api()
    {

        $frontmenu = FrontMenu::orderBy('id', 'DESC')->onlyTrashed()->get();

        // Transform data into the desired array format
        $formattedData = $frontmenu->map(function ($item) {
            return [
                'name' => $item->name,
                'url' => $item->url,
                'bannertitle' => $item->banner_title,
                'bannersubtitle' => $item->banner_sub_title,
                'bannerphoto' => $item->banner_photo,
                'actions' => '
                  <a class="restore-btn-menu btn btn-primary btn-sm" href="#" data-id="' . $item->id . '">
                      <i class="bi bi-arrow-return-left"></i> Restore
                  </a>'
            ];
        });

        return response()->json(['menudata' => $formattedData]);
    }

    public function save_frontmenu(Request $request)
    {
        $id = $request->menu_id; // Move this above validation

        $request->validate([
            'frontmenu_name' => 'required|string|unique:frontmenu,name,' . $id,
            'frontmenu_url' => 'required|string',
            'banner_title' => 'required|string',
            'banner_sub_title' => 'required|string',
            'banner_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'frontmenu_name.required' => 'The name is required.',
            'frontmenu_name.string' => 'The name should be a string.',
            'frontmenu_name.unique' => 'The name is already taken. Please check the restore button if available.',
            'frontmenu_url.required' => 'The URL is required.',
            'frontmenu_url.string' => 'The URL should be a string.'
        ]);

        // Find the menu item by ID (it might be null if no menu with that ID exists)
        $menu = FrontMenu::find($id);

        // Initialize $imagePath with null or existing banner_photo if menu exists
        $imagePath = $menu ? $menu->banner_photo : null;

        // Handle file uploads
        if ($request->hasFile('banner_photo')) {
            if ($menu && $menu->banner_photo && file_exists(public_path($menu->banner_photo))) {
                unlink(public_path($menu->banner_photo));
            }

            $imgFile = $request->file('banner_photo');
            $filename = time() . '_' . $imgFile->getClientOriginalName();
            $imagePath = 'assets/uploads/' . $filename;
            $imgFile->move(public_path('assets/uploads'), $filename);
        }

        // Handle updating an existing menu item
        if ($id && $menu) {
            $oldMenuName = strtolower($menu->name);
            $oldFilePath = resource_path("views/pages/front/{$oldMenuName}.blade.php");

            // Delete the old file if it exists
            if (File::exists($oldFilePath)) {
                File::delete($oldFilePath);
            }
        }

        // Update or create the menu item
        $menu = FrontMenu::updateOrCreate(
            ['id' => $id], // Condition to find the record
            [ // Data to be updated or created
                'name' => $request->frontmenu_name,
                'url' => $request->frontmenu_url,
                'banner_title' => $request->banner_title,
                'banner_sub_title' => $request->banner_sub_title,
                'banner_photo' => $imagePath,
            ]
        );

        // Generate the view file
        $this->createViewFile(strtolower($request->frontmenu_name));

        return response()->json([
            'success' => 'Menu saved successfully',
        ]);
    }


    public function remove_frontmenu(Request $request)
    {
        $id = $request->menu_id;

        $menu = FrontMenu::find($id);

        // Check if menu item exists
        if (!$menu) {
            return response()->json(['message' => 'Menu not found', 'type' => 'error'], 404);
        }

        $menuName = strtolower($menu->name);
        $filePath = resource_path("views/pages/front/{$menuName}.blade.php");

        // Delete the view file if it exists
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Delete the menu item
        $menu->delete();

        return response()->json(['message' => 'Deleted successfully', 'type' => 'success']);
    }

    public function restore_frontmenu(Request $request)
    {
        $id = $request->menu_id;

        // Retrieve the trashed menu item
        $menu = FrontMenu::withTrashed()->find($id);

        // Check if the menu item exists
        if (!$menu) {
            return response()->json(['message' => 'Menu not found', 'type' => 'error'], 404);
        }

        // Restore the menu item
        $menu->restore();

        $menuName = strtolower($menu->name);
        $filePath = resource_path("views/pages/front/{$menuName}.blade.php");

        // Create the view file if it doesn't already exist
        if (!file_exists($filePath)) {
            $this->createViewFile($menuName);
        }

        return response()->json(['message' => 'Menu restored successfully', 'type' => 'success']);
    }

    public function contentsection_api()
    {
        $sections = ContentSection::with('frontMenu')
            ->whereHas('frontMenu', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->orderBy('id', 'DESC')
            ->get();

        $formattedData = $sections->map(function ($item) {
            return [
                'menu' => $item->frontMenu->name ?? 'N/A',
                'layout' => $item->layout_type,
                'object_type' => $item->object_type,
                'image' => $item->isImage,
                'icon' => $item->isIcon,
                'title' => $item->title,
                'content' => $item->content,
                'object_position' => $item->object_position,
                'actions' => '

                <a class="view-btn-section" href="javascript:void(0)"   
                data-id="' . $item->id . '"  
                data-menu="' . $item->frontmenu_id . '"
                data-menuname="' . $item->frontMenu->name . '" 
                data-layout="' . $item->layout_type . '" 
                data-objecttype="' . $item->object_type . '" 
                data-image="' . $item->isImage . '" 
                data-icon="' . $item->isIcon . '" 
                data-title="' . $item->title . '" 
                data-content="' . $item->content . '" 
                data-objectposition="' . $item->object_position . '"  
                data-modaltitle="View">
                    <i class="bi bi-eye fs-3"></i>
                </a>

                <a class="edit-btn-section" href="javascript:void(0)" 
                data-id="' . $item->id . '"  
                data-menu="' . $item->frontmenu_id . '" 
                data-layout="' . $item->layout_type . '" 
                data-objecttype="' . $item->object_type . '" 
                data-image="' . $item->isImage . '" 
                data-icon="' . $item->isIcon . '" 
                data-title="' . $item->title . '" 
                data-content="' . $item->content . '" 
                data-objectposition="' . $item->object_position . '"  
                data-modaltitle="Edit">
                <i class="bi bi-pencil-square fs-3"></i>
                </a>
                <a class="delete-btn-section" href="javascript:void(0)" data-id="' . $item->id . '">
                    <i class="bi bi-trash fs-3"></i>
                </a>'
            ];
        });

        return response()->json(['sectiondata' => $formattedData]);
    }

    public function save_section(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'menu' => 'required',
            'layout' => 'required',
            // 'object' => 'required',
            'title' => 'required|string',
            'description' => 'required',
            'position' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'icon' => 'nullable|mimes:svg|max:2048',
        ]);

        $id = $request->section_id;

        // Find the section if it exists
        $section = ContentSection::find($id);

        // Handle file uploads
        $imagePath = $section ? $section->isImage : null;
        $iconPath = $section ? $section->isIcon : null;

        if ($request->hasFile('image')) {
            $imgFile = $request->file('image');
            $filename = time() . '_' . $imgFile->getClientOriginalName();
            $imagePath = 'assets/uploads/' . $filename;
            $iconPath = null;
            $imgFile->move(public_path('assets/uploads'), $filename);
        }

        if ($request->hasFile('icon')) {
            $iconFile = $request->file('icon');
            $filename = time() . '_' . $iconFile->getClientOriginalName();
            $iconPath = 'assets/uploads/' . $filename;
            $imagePath = null;
            $iconFile->move(public_path('assets/uploads'), $filename);
        }

        // Sanitize the content description while allowing specific tags and styles
        $allowedTags = '<p><b><strong><i><ol><ul><li>';
        $allowedStyles = ['text-indent', 'text-align'];
        // $sanitizedContent = $this->sanitizeContent($request->description, $allowedTags, $allowedStyles);

        // Update or create the new menu item
        $section = ContentSection::updateOrCreate(
            ['id' => $id], // Condition to find the record
            [ // Data to be updated or created
                'frontmenu_id' => $request->menu,
                'layout_type' => $request->layout,
                // 'object_type' => $request->object,
                'isImage' => $imagePath,
                'isIcon' => $iconPath,
                'title' => $request->title,
                'content' => $request->description,
                'object_position' => $request->position,
            ]
        );

        if ($section) {
            $menu = FrontMenu::find($request->menu);

            // Check if menu item exists
            if (!$menu) {
                return response()->json(['message' => 'Menu not found', 'type' => 'error'], 404);
            }

            $menuName = strtolower($menu->name);
            $filePath = resource_path("views/pages/front/{$menuName}.blade.php");

            // Delete the view file if it exists
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            $this->createViewFile($menuName);
        }

        // Return success response
        return response()->json([
            'success' => 'Section saved successfully',
        ]);
    }

    public function remove_section(Request $request)
    {
        $id = $request->section_id;

        // Find the section
        $section = ContentSection::find($id);

        // Check if the section exists
        if (!$section) {
            return response()->json(['message' => 'Section not found', 'type' => 'error'], 404);
        }

        // Find the menu item associated with the section
        $menu = FrontMenu::find($section->frontmenu_id);

        // Check if the menu item exists
        if (!$menu) {
            return response()->json(['message' => 'Menu not found', 'type' => 'error'], 404);
        }

        $menuName = strtolower($menu->name);
        $filePath = resource_path("views/pages/front/{$menuName}.blade.php");

        // Delete the existing view file if it exists
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Create the view file
        //

        // Check and delete associated files if they exist
        if ($section->isImage && file_exists(public_path($section->isImage)) && is_file(public_path($section->isImage))) {
            unlink(public_path($section->isImage));
        }

        if ($section->isIcon && file_exists(public_path($section->isIcon)) && is_file(public_path($section->isIcon))) {
            unlink(public_path($section->isIcon));
        }

        // Delete the section
        $section->delete();

        $this->createViewFile($menuName);

        return response()->json(['message' => 'Deleted successfully', 'type' => 'success']);
    }

    public function wcu_api()
    {

        $wcu = WCUSection::orderBy('id', 'DESC')->get();

        $formattedData = $wcu->map(function ($item) {
            return [
                'icon' => $item->icon,
                'title' => $item->title,
                'content' => $item->content,
                'actions' => '
            
                <a class="edit-btn-wcu" href="javascript:void(0)" 
                data-id="' . $item->id . '"  
                data-icon="' . $item->icon . '" 
                data-title="' . $item->title . '" 
                data-content="' . $item->content . '"   
                data-modaltitle="Edit">
                <i class="bi bi-pencil-square fs-3"></i>
                </a>
                <a class="delete-btn-wcu" href="javascript:void(0)" data-id="' . $item->id . '">
                    <i class="bi bi-trash fs-3"></i>
                </a>'
            ];
        });

        return response()->json(['wcudata' => $formattedData]);
    }

    public function wcu_section(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'wcu_icon' => 'nullable|mimes:svg|max:2048',
            'wcu_title' => 'required|string',
            'wcu_description' => 'required',
        ], [
            'wcu_icon.mimes' => 'The icon must be an SVG file.',
            'wcu_icon.max' => 'The icon size should not exceed 2MB.',
            'wcu_title.required' => 'The title is required.',
            'wcu_title.string' => 'The title should be a string.',
            'wcu_description.required' => 'The description is required.',
        ]);

        $id = $request->wcu_id;

        // Find the section if it exists
        $wcu = WCUSection::find($id);

        // Handle file uploads
        $iconPath = $wcu ? $wcu->icon : null;

        if ($request->hasFile('wcu_icon')) {
            $iconFile = $request->file('wcu_icon');
            $filename = time() . '_' . $iconFile->getClientOriginalName();
            $iconPath = 'assets/uploads/' . $filename;
            $iconFile->move(public_path('assets/uploads'), $filename);
        }

        WCUSection::updateOrCreate(
            ['id' => $id], // Condition to find the record
            [ // Data to be updated or created
                'icon' => $iconPath,
                'title' => $request->wcu_title,
                'content' => $request->wcu_description,
            ]
        );

        return response()->json([
            'success' => 'Saved successfully',
        ]);
    }

    public function remove_wcu(Request $request)
    {
        $id = $request->wcu_id;

        $wcu = WCUSection::find($id);

        if (!$wcu) {
            return response()->json(['message' => 'WCU Section not found', 'type' => 'error'], 404);
        }

        if ($wcu->icon && file_exists(public_path($wcu->icon)) && is_file(public_path($wcu->icon))) {
            unlink(public_path($wcu->icon));
        }

        $wcu->delete();

        return response()->json(['message' => 'Deleted successfully', 'type' => 'success']);
    }

    public function faq_api()
    {

        $faq = FAQ::orderBy('id', 'DESC')->get();

        $formattedData = $faq->map(function ($item) {
            return [
                'question' => $item->question,
                'answers' => $item->answers,
                'actions' => '
           
               <a class="edit-btn-faq" href="javascript:void(0)" 
               data-id="' . $item->id . '"  
               data-question="' . $item->question . '" 
               data-answers="' . $item->answers . '"   
               data-modaltitle="Edit">
               <i class="bi bi-pencil-square fs-3"></i>
               </a>
               <a class="delete-btn-faq" href="javascript:void(0)" data-id="' . $item->id . '">
                   <i class="bi bi-trash fs-3"></i>
               </a>'
            ];
        });

        return response()->json(['faqdata' => $formattedData]);
    }

    public function faq_section(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'faq_question' => 'required|string',
            'faq_answer' => 'required',
        ], [
            'faq_question.required' => 'The question is required.',
            'faq_question.string' => 'The question should be a string.',
            'faq_answer.required' => 'The answers is required.',
        ]);

        $id = $request->faq_id;

        $sanitizedAnswer = strip_tags($request->faq_answer, '<ol><li>');

        FAQ::updateOrCreate(
            ['id' => $id], // Condition to find the record
            [ // Data to be updated or created
                'question' => $request->faq_question,
                'answers' => $sanitizedAnswer,
            ]
        );

        return response()->json([
            'success' => 'Saved successfully',
        ]);
    }

    public function remove_faq(Request $request)
    {
        $id = $request->faq_id;

        $faq = FAQ::find($id);

        if (!$faq) {
            return response()->json(['message' => 'FAQ Section not found', 'type' => 'error'], 404);
        }

        $faq->delete();

        return response()->json(['message' => 'Deleted successfully', 'type' => 'success']);
    }


    public function socialmedia_api()
    {

        $socialmedia = SocialMedia::orderBy('id', 'DESC')->get();

        $formattedData = $socialmedia->map(function ($item) {
            return [
                'icon' => $item->icon,
                'url' => $item->url,
                'actions' => '
           
               <a class="edit-btn-socialmedia" href="javascript:void(0)" 
               data-id="' . $item->id . '"  
               data-icon="' . $item->icon . '" 
               data-url="' . $item->url . '"   
               data-modaltitle="Edit">
               <i class="bi bi-pencil-square fs-3"></i>
               </a>
               <a class="delete-btn-socialmedia" href="javascript:void(0)" data-id="' . $item->id . '">
                   <i class="bi bi-trash fs-3"></i>
               </a>'
            ];
        });

        return response()->json(['socialmediadata' => $formattedData]);
    }

    public function socialmedia_section(Request $request)
    {

        // Validate incoming request data
        $request->validate([
            'socialmedia_icon' => 'required|string',
            'url' => ['required', 'url', 'starts_with:https://'],
        ], [
            'socialmedia_icon.required' => 'The icon is required.'
        ]);

        $id = $request->socialmedia_id;

        SocialMedia::updateOrCreate(
            ['id' => $id], // Condition to find the record
            [ // Data to be updated or created
                'icon' => $request->socialmedia_icon,
                'url' => $request->url,
            ]
        );

        return response()->json([
            'success' => 'Saved successfully',
        ]);
    }

    public function remove_socialmedia(Request $request)
    {
        $id = $request->socialmedia_id;

        $socialmedia = SocialMedia::find($id);

        if (!$socialmedia) {
            return response()->json(['message' => 'Socialmedia Icon not found', 'type' => 'error'], 404);
        }

        $socialmedia->delete();

        return response()->json(['message' => 'Deleted successfully', 'type' => 'success']);
    }

    public function terms_api()
    {
        //$frontmenu = FrontMenu::orderBy('id', 'DESC')->withTrashed()->get();
        $terms = TermCondition::orderBy('id', 'DESC')->get();

        // Transform data into the desired array format
        $formattedData = $terms->map(function ($item) {
            return [
                'contenttype' => $item->content_type,
                'content' => $item->content,
                'actions' => '<a class="edit-btn-terms" href="javascript:void(0)" 
                data-id="' . $item->id . '"  
                data-contenttype="' . $item->content_type . '" 
                data-content="' . $item->content . '" 
                data-modaltitle="Edit">
                <i class="bi bi-pencil-square fs-3"></i>
                </a>'
            ];
        });

        return response()->json(['termsdata' => $formattedData]);
    }

    public function terms_section(Request $request)
    {

        // Validate incoming request data
        $request->validate([
            'terms_description' => 'required|string',
        ]);

        $id = $request->terms_id;

        TermCondition::updateOrCreate(
            ['id' => $id],
            [
             'content' => $request->terms_description,
            ]
        );

        return response()->json([
            'success' => 'Saved successfully',
        ]);
    }

    protected function createViewFile($slug)
    {
        $filePath = resource_path("views/pages/front/{$slug}.blade.php");

        // Default fallback content for the view file
        $defaultContent = <<<EOD
        <!-- resources/views/pages/front/{$slug}.blade.php -->
        @extends('layouts.front.app')

        @section('content')
             <div id="about-lana-pet-container" class="container-fluid bg-light text-dark section-py--large overflow-hidden">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="heading-section mb-5 mt-5 mt-lg-0">
                                <h5 class="mb-3">No data yet for the {$slug} page.</h5>
                                <p>Please wait for the administrator. Thank you.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endsection
        EOD;

        // Get the menu by name
        $menu = FrontMenu::where('name', ucfirst($slug))->first();

        if (!$menu) {
            // If the menu doesn't exist, create the file with default content
            $this->writeViewFile($filePath, $defaultContent);
            return;
        }

        // Fetch content sections related to the menu
        $contentSections = ContentSection::where('frontmenu_id', $menu->id)->get();

        if ($contentSections->isEmpty()) {
            // If no content sections exist, create the file with default content
            $this->writeViewFile($filePath, $defaultContent);
            return;
        }

        // Generate the appropriate template based on layout type
        $bladeTemplate = $this->generateBladeTemplate($slug, $contentSections);

        // Write the generated template to the file
        $this->writeViewFile($filePath, $bladeTemplate);
    }

    private function generateBladeTemplate($slug, $contentSections)
    {
        foreach ($contentSections as $section) {
            $imagePath = $section->isImage ? asset($section->isImage) : '';

            $fixedTopImagePath = $section->isImage != null
                ? asset($section->isImage)
                : ($section->isIcon != null
                    ? asset($section->isIcon)
                    : ''
                );

            switch ($section->layout_type) {
                case 'col 12':
                    return <<<EOD
                    <!-- resources/views/pages/front/{$slug}.blade.php -->
                    @extends('layouts.front.app')

                    @section('content')
            
                        <div id="about-lana-pet-container" class="container-fluid bg-light text-dark section-py--large overflow-hidden">
                            <div class="container">
                                <div class="row">
                                    <div class="col-12 col-md-12 px-0 px-md-5 pb-5 pb-md-0">
                                    <img class="img-fluid post-thumbnail mb-5" src="{$imagePath}" alt="Pet">
                                        <div class="d-flex align-items-start flex-column" data-scroll-animate="comeInRight">
                                            <div class="my-auto">
                                                <h1 class="mb-4 font-weight-bold">{$section->title}</h1>
                                                <div class="lana-hr lana-hr-4 border-primary mb-4"></div>
                                                $section->content
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endsection
                    EOD;

                case 'col 8 to 4':
                    $orderRightClass = $section->object_position === 'Right Image to Left Content' ? 'order-md-first' : 'order-md-last';
                    $orderLeftClass = $section->object_position === 'Left Image to Right Content' ? 'order-md-last' : 'order-md-first';

                    return <<<EOD
                    <!-- resources/views/pages/front/{$slug}.blade.php -->
                    @extends('layouts.front.app')

                    @section('content')
                        <div id="about-lana-pet-container" class="container-fluid bg-light text-dark section-py--large overflow-hidden">
                            <div class="container">
                                <div class="row">
                                    <div class="col-12 col-md-8 px-0 px-md-5 pb-5 pb-md-0 {$orderRightClass}">
                                        <img src="{$imagePath}" class="img-fluid" alt="Lana Pet" data-scroll-animate="comeInLeft">
                                    </div>
                                    <div class="col-12 col-md-4 px-0 px-md-5 {$orderLeftClass}">
                                        <div class="h-100 d-flex align-items-start flex-column" data-scroll-animate="comeInRight">
                                            <div class="my-auto">
                                                <h2 class="mb-4 font-weight-bold">{$section->title}</h2>
                                                <div class="lana-hr lana-hr-4 border-primary mb-4"></div>
                                                $section->content
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endsection
                    EOD;

                case 'col 7 to 5':
                    $orderRightClass = $section->object_position === 'Right Image to Left Content' ? 'order-md-first' : 'order-md-last';
                    $orderLeftClass = $section->object_position === 'Left Image to Right Content' ? 'order-md-last' : 'order-md-first';

                    return <<<EOD
                    <!-- resources/views/pages/front/{$slug}.blade.php -->
                    @extends('layouts.front.app')

                    @section('content')
                        <div id="about-lana-pet-container" class="container-fluid bg-light text-dark section-py--large overflow-hidden">
                            <div class="container">
                                <div class="row">
                                    <div class="col-12 col-md-7 px-0 px-md-5 pb-5 pb-md-0 {$orderRightClass}">
                                        <img src="{$imagePath}" class="img-fluid" alt="Lana Pet" data-scroll-animate="comeInLeft">
                                    </div>
                                    <div class="col-12 col-md-5 px-0 px-md-5 {$orderLeftClass}">
                                        <div class="h-100 d-flex align-items-start flex-column" data-scroll-animate="comeInRight">
                                            <div class="my-auto">
                                                <h2 class="mb-4 font-weight-bold">{$section->title}</h2>
                                                <div class="lana-hr lana-hr-4 border-primary mb-4"></div>
                                                $section->content
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endsection
                    EOD;

                case 'col 6 to 6':

                    $col6Sections = '';

                    foreach ($contentSections as $col6Section) {
                        if ($col6Section->layout_type === 'col 6 to 6') {
                            $orderRightClass = $col6Section->object_position === 'Right Image to Left Content' ? 'order-md-first' : 'order-md-last';
                            $orderLeftClass = $col6Section->object_position === 'Left Image to Right Content' ? 'order-md-last' : 'order-md-first';

                            $imagePath = $col6Section->isImage ? asset($col6Section->isImage) : '';

                            $col6Sections .= <<<EOD
                                <div class="col-12 col-md-6 px-0 px-md-5 pb-5 pb-md-0  {$orderRightClass}">
                                    <img src="{$imagePath}" class="img-fluid" alt="Lana Pet" data-scroll-animate="comeInLeft">
                                </div>
                                <div class="col-12 col-md-6 px-0 px-md-5 {$orderLeftClass}">
                                    <div class="h-100 d-flex align-items-start flex-column" data-scroll-animate="comeInRight">
                                        <div class="my-auto">
                                            <h1 class="mb-4 font-weight-bold">{$col6Section->title}</h1>
                                            <div class="lana-hr lana-hr-4 border-primary mb-4"></div>
                                            {$col6Section->content}
                                        </div>
                                    </div>
                                </div>
                                EOD;
                        }
                    }

                    return <<<EOD
                        <!-- resources/views/pages/front/{$slug}.blade.php -->
                        @extends('layouts.front.app')
                    
                        @section('content')
                            <div id="about-lana-pet-container" class="container-fluid bg-light text-dark section-py--large overflow-hidden">
                                <div class="container">
                                    <div class="row">
                                        {$col6Sections}
                                    </div>
                                </div>
                            </div>
                        @endsection
                        EOD;

                case 'col 4':

                    // Generate multiple col-4 entries in a loop if there are multiple 'col 4' sections
                    $col4Sections = '';

                    foreach ($contentSections as $col4Section) {
                        if ($col4Section->layout_type === 'col 4') {


                            $col4Sections .= <<<EOD

                            <div class="col-12 col-md-6 col-lg-4">
                                <div id="post-4" class="post type-post post-4 card post-card post-grid-card h-100">
                                    <img class="card-img-top img-fluid" src="{$fixedTopImagePath}"
                                        alt="Post">
                                    <div class="card-body">
                                    
                                        <h5 class="post-title card-title">{$col4Section->title}</h5>
                                        {$col4Section->content}
                                    </div>
                                </div>
                            </div>

                            EOD;
                        }
                    }

                    return <<<EOD
                    <!-- resources/views/pages/front/{$slug}.blade.php -->
                    @extends('layouts.front.app')

                    @section('content')
                        <div id="about-lana-pet-container" class="container-fluid bg-light text-dark section-py--large overflow-hidden">
                            <div class="container">
                                <div class="row">
                                    {$col4Sections}
                                </div>
                            </div>
                        </div>
                    @endsection
                    EOD;

                case 'col 3':

                    // Generate multiple col-4 entries in a loop if there are multiple 'col 4' sections
                    $col3Sections = '';

                    foreach ($contentSections as $col3Section) {
                        if ($col3Section->layout_type === 'col 3') {


                            $col3Sections .= <<<EOD
                            <div class="col-12 col-md-6 col-lg-3">
                                <div id="post-4" class="post type-post post-4 card post-card post-grid-card h-100">
                                    <img class="card-img-top img-fluid"
                                        src="{$fixedTopImagePath}"
                                        alt="Post">
                                    <div class="card-body">
                                        <h5 class="post-title card-title"><a href="single.html">{$col3Section->title}</a></h5>
                                        {$col3Section->content}
                                    </div>
                                </div>
                            </div>

                            EOD;
                        }
                    }

                    return <<<EOD
                    <!-- resources/views/pages/front/{$slug}.blade.php -->
                    @extends('layouts.front.app')

                    @section('content')
                        <div id="about-lana-pet-container" class="container-fluid bg-light text-dark section-py--large overflow-hidden">
                            <div class="container">
                                <div class="row">
                                    {$col3Sections}
                                </div>
                            </div>
                        </div>
                    @endsection
                    EOD;
            }
        }

        return ''; // Default empty template if no layout matches
    }

    private function writeViewFile($filePath, $content)
    {
        $directory = dirname($filePath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($filePath, $content);
    }
}
