<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Product;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::before(function (User $user) {
            if ($user->role->name === 'admin') {
                return true;
            }
        });

        $this->registerPolicies();

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return env('SPA_URL') . '/reset-password?token=' . $token;
        });
    }
}
