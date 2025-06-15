<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

   $page = "Welcome to TantucoCTC Hardware";

   return view('pages.welcome', compact('page'));
})->name('welcome');

// routes/web.php
Route::post('/login/ajax', [App\Http\Controllers\Auth\LoginController::class, 'ajaxLogin']);

Auth::routes(['verify' => true]);

Route::get('/secure-js-file/{filename}', [App\Http\Controllers\SecureController::class, 'serveJsFile'])->name('secure.js');
Route::post('/verify/code',  [App\Http\Controllers\Auth\VerificationController::class, 'otp_verify']);

Route::get('/google/redirect', [App\Http\Controllers\GoogleLoginController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback', [App\Http\Controllers\GoogleLoginController::class, 'handleGoogleCallback'])->name('google.callback');

Route::middleware(['prevent-back-history', 'auth', 'verified'])->group(function () {
    Route::get('home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::post('user-details-form', [App\Http\Controllers\HomeController::class, 'user_details_form']);

    //General Setting
    Route::get('generalsettings', [App\Http\Controllers\GeneralSettingsController::class, 'index'])->name('generalsettings');
    Route::post('generalsettings-company', [App\Http\Controllers\GeneralSettingsController::class, 'company']);
    Route::post('generalsettings-profile', [App\Http\Controllers\GeneralSettingsController::class, 'profile']);
    Route::post('generalsettings-account', [App\Http\Controllers\GeneralSettingsController::class, 'account']);
    Route::post('generalsettings-password', [App\Http\Controllers\GeneralSettingsController::class, 'password']);

    //Content Settings
    Route::get('contentsettings', [App\Http\Controllers\ContentSettingsController::class, 'index'])->name('contentsettings');
    //Menu
    Route::get('contentsettings-menu-api', [App\Http\Controllers\ContentSettingsController::class, 'frontmenu_api']);
    Route::get('contentsettings-menu-restore-api', [App\Http\Controllers\ContentSettingsController::class, 'frontmenu_restore_api']);
    Route::post('contentsettings-save-frontmenu', [App\Http\Controllers\ContentSettingsController::class, 'save_frontmenu']);
    Route::post('contentsettings-remove-frontmenu', [App\Http\Controllers\ContentSettingsController::class, 'remove_frontmenu']);
    Route::post('contentsettings-restore-frontmenu', [App\Http\Controllers\ContentSettingsController::class, 'restore_frontmenu']);
    //Section
    Route::get('contentsettings-section-api', [App\Http\Controllers\ContentSettingsController::class, 'contentsection_api']);
    Route::post('contentsettings-save-section', [App\Http\Controllers\ContentSettingsController::class, 'save_section']);
    Route::post('contentsettings-remove-section', [App\Http\Controllers\ContentSettingsController::class, 'remove_section']);
    //Why Choose Us Section
    Route::get('contentsettings-wcu-api', [App\Http\Controllers\ContentSettingsController::class, 'wcu_api']);
    Route::post('contentsettings-wcu-section', [App\Http\Controllers\ContentSettingsController::class, 'wcu_section']);
    Route::post('contentsettings-remove-wcu', [App\Http\Controllers\ContentSettingsController::class, 'remove_wcu']);
    //FAQ Section
    Route::get('contentsettings-faq-api', [App\Http\Controllers\ContentSettingsController::class, 'faq_api']);
    Route::post('contentsettings-faq-section', [App\Http\Controllers\ContentSettingsController::class, 'faq_section']);
    Route::post('contentsettings-remove-faq', [App\Http\Controllers\ContentSettingsController::class, 'remove_faq']);
    //Social Media Section
    Route::get('contentsettings-socialmedia-api', [App\Http\Controllers\ContentSettingsController::class, 'socialmedia_api']);
    Route::post('contentsettings-socialmedia-section', [App\Http\Controllers\ContentSettingsController::class, 'socialmedia_section']);
    Route::post('contentsettings-remove-socialmedia', [App\Http\Controllers\ContentSettingsController::class, 'remove_socialmedia']);
    //Terms Section
    Route::get('contentsettings-terms-api', [App\Http\Controllers\ContentSettingsController::class, 'terms_api']);
    Route::post('contentsettings-terms-section', [App\Http\Controllers\ContentSettingsController::class, 'terms_section']);


    Route::resource('animaltype', App\Http\Controllers\AnimalTypeController::class);
    Route::resource('characteristic', App\Http\Controllers\CharacteristicsController::class);
    Route::resource('animalcaresetting', App\Http\Controllers\AnimalCareSettingController::class);

    Route::resource('pet', App\Http\Controllers\PetsController::class);
    Route::get('rescued-pet', [App\Http\Controllers\PetsController::class, 'rescued_pet']);
    Route::get('surrendered-pet', [App\Http\Controllers\PetsController::class, 'surrendered_pet']);
    Route::get('incare-pet', [App\Http\Controllers\PetsController::class, 'incare_pet']);
    Route::get('adopted-pet', [App\Http\Controllers\PetsController::class, 'adopted_pet']);
    Route::get('/pets-report/pdf', [App\Http\Controllers\PetsController::class, 'generatePdf']);

    Route::resource('adopter', App\Http\Controllers\AdopterController::class);
    Route::get('filter-pet-type', [App\Http\Controllers\AdopterController::class, 'filter_pet_type']);

    Route::resource('shelter', App\Http\Controllers\ShelterController::class);
    Route::resource('requirement', App\Http\Controllers\AdoptionRequirementsController::class);
    Route::resource('eventsettings', App\Http\Controllers\EventQuestionaireController::class);
    Route::resource('volunteer', App\Http\Controllers\VolunteersController::class);
    Route::resource('staff', App\Http\Controllers\StaffController::class);
    Route::resource('schedule', App\Http\Controllers\StaffScheduleController::class);

    Route::resource('task', App\Http\Controllers\StaffTaskController::class);
    Route::post('task-percentage/{staffTaskId}', [App\Http\Controllers\StaffTaskController::class, 'task_percentage']);

    Route::resource('expense', App\Http\Controllers\ExpensesController::class);
    Route::resource('foodinventory', App\Http\Controllers\FoodInventoryController::class);


    Route::resource('donation', App\Http\Controllers\DonationsController::class);
    Route::post('show-gcash', [App\Http\Controllers\DonationsController::class, 'show_gcash']);

    Route::resource('gcash', App\Http\Controllers\GcashSettingsController::class);
    Route::post('gcash-switch', [App\Http\Controllers\GcashSettingsController::class, 'switch_status']);

    Route::get('/generate-report-index', [App\Http\Controllers\GenerateReportController::class, 'index']);
    Route::post('/generate-report', [App\Http\Controllers\GenerateReportController::class, 'generate_report']);
    Route::post('/generate-pdf', [App\Http\Controllers\GenerateReportController::class, 'generate_pdf']);
    Route::post('/generate-excel', [App\Http\Controllers\GenerateReportController::class, 'generate_excel']);

    Route::post('save-security-auth', [App\Http\Controllers\HomeController::class, 'save_security_auth']);

    //ADOPTER
    Route::get('/adoption-pets', [App\Http\Controllers\Adopter\HomeController::class, 'adoption_pet']);
    Route::get('/pet-details-qr/{name}/{id}', [App\Http\Controllers\Adopter\HomeController::class, 'pet_details_qr'])->name('pet.details');
    Route::get('/pet-api', [App\Http\Controllers\Adopter\HomeController::class, 'pet_api']);
    Route::post('adopter-pet-form', [App\Http\Controllers\Adopter\HomeController::class, 'adopter_form']);
    Route::get('adopter-pet', [App\Http\Controllers\Adopter\HomeController::class, 'adopter_pet'])->name('adopter.mypet');
    Route::get('adopter-pet-api', [App\Http\Controllers\Adopter\HomeController::class, 'adopter_pet_api']);
    Route::post('adopter-application-status', [App\Http\Controllers\Adopter\HomeController::class, 'adopter_application_status']);
    Route::get('adoption-application-status', [App\Http\Controllers\Adopter\HomeController::class, 'adoption_application_status']);
    Route::post('adoption-under-review-status', [App\Http\Controllers\Adopter\HomeController::class, 'adoption_under_review_status']);
    Route::get('adopter-matching-pet', [App\Http\Controllers\Adopter\HomeController::class, 'adopter_matching_pet'])->name('adopter.matchingpet');
    Route::get('adopter-matchingpet-api', [App\Http\Controllers\Adopter\HomeController::class, 'adopter_matchingpet_api']);
    Route::post('/pets/search', [App\Http\Controllers\Adopter\HomeController::class, 'pet_search'])->name('pets.search');
    Route::get('shelter-requirement-form', [App\Http\Controllers\Adopter\HomeController::class, 'shelter_requirement_form']);
    Route::post('update-animal-match', [App\Http\Controllers\Adopter\HomeController::class, 'matching_criteria']);
    Route::get('/volunteer-event', [App\Http\Controllers\Adopter\VolunteerEventController::class, 'volunteer_event']);
    Route::get('volunteer-questionaire-api', [App\Http\Controllers\Adopter\VolunteerEventController::class, 'volunteer_questionaire_api']);
    Route::post('volunteer-answers', [App\Http\Controllers\Adopter\VolunteerEventController::class, 'volunteer_answers']);
    Route::get('/animal-care', [App\Http\Controllers\Adopter\HomeController::class, 'animal_care']);
    Route::get('/adopter-profile', [App\Http\Controllers\Adopter\HomeController::class, 'adopter_profile']);
    Route::get('/adopter-contact', [App\Http\Controllers\Adopter\HomeController::class, 'adopter_contact']);
    Route::get('/adopter-process', [App\Http\Controllers\Adopter\HomeController::class, 'adopter_process']);
    Route::get('/adopter-partner-shelter', [App\Http\Controllers\Adopter\HomeController::class, 'adopter_partner_shelter']);
    Route::post('/adopter-reupload-form', [App\Http\Controllers\Adopter\HomeController::class, 'adopter_reupload_form']);
});

