<?php

declare(strict_types=1);

namespace Brighterly\LaravelPolicies;

use Brighterly\LaravelPolicies\Middleware\SetCustomPolicy;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Support\ServiceProvider;

class PoliciesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerMacros();
        $this->registerPublishables();
    }

    private function registerMacros(): void
    {
        $macro = function (array $policies): Route|RouteRegistrar {
            $flatParams = [];
            foreach ($policies as $model => $policy) {
                $flatParams[] = $model;
                $flatParams[] = $policy;
            }

            /** @var Route|RouteRegistrar $this */
            return $this->middleware(SetCustomPolicy::class . ':' . implode(',', $flatParams));
        };

        Route::macro('setPolicies', $macro);
        RouteRegistrar::macro('setPolicies', $macro);
    }

    private function registerPublishables(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../stubs/PoliciesMap.stub' => app_path('Policies/Contracts/PoliciesMap.php'),
            ], 'laravel-policies-stub');
        }
    }
}
