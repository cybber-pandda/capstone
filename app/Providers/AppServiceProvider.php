<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register company settings as a singleton
        // $this->app->singleton('companySettings', function () {
        //     return DB::table('company_settings')->first();
        // });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Share company settings globally
        // $companySettings = app('companySettings');
        // View::share('companySettings', $companySettings);

        // // Use a view composer for adopter-specific data
        // View::composer('*', function ($view) {
        //     if (Auth::check() && Auth::user()->role === 'adopter') {
        //         $user = Auth::user();
                
        //         $userAdopter = DB::table('b2b_details')->where('user_id', $user->id)->first();

        //         // if ($userAdopter) {
        //         //     $adoptionApplicationCount = DB::table('adoption_applications')
        //         //         ->where('adopter_id', $userAdopter->id)
        //         //         ->whereIn('status', ['pending', 'rejected'])
        //         //         ->count();

        //         //     $adoptedPets = \App\Models\AdoptionApplication::with(['animal', 'adopter'])
        //         //         ->whereHas('adopter', function ($query) use ($user) {
        //         //             $query->where('user_id', $user->id)
        //         //                 ->whereNull('deleted_at');
        //         //         })
        //         //         ->whereIn('status', ['pending', 'rejected','approved'])
        //         //         ->orderBy('id', 'DESC')
        //         //         ->limit(5)
        //         //         ->get();

        //         //     // Share adopter-specific data with views
        //         //     $view->with('adoptionApplicationCount', $adoptionApplicationCount)
        //         //          ->with('adoptedPets', $adoptedPets);
        //         // }
        //     }
        // });
    }
}
