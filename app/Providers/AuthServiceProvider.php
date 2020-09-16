<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Models\Scope;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $scope = [];
        passport::routes();
        $data = Scope::all();
        foreach($data as $val){
            $scope[$val['scope']] = $val['scope_name'];  
        }   
        passport::tokensCan($scope);
        passport::setDefaultScope([
            'user'
        ]);
        passport::tokensExpireIn(Carbon::now()->addDays(1));
        passport::refreshTokensExpireIn(Carbon::now()->addDays(10));

    }
}
