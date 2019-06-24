<?php

namespace App\Providers;

use App\User;
use App\Mail\UserCreated;
use App\Mail\UserMailChanged;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Concerns\isDirty;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        User::created(function($user){
            Mail::to($user)->send(new UserCreated($user));
        });

        User::updated(function($user){
            if($user->isDirty('email')){
                Mail::to($user)->send(new UserMailChanged($user));
            }            
        });
    }
}
