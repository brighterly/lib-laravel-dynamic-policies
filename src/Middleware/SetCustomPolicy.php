<?php

declare(strict_types=1);

namespace Brighterly\LaravelPolicies\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class SetCustomPolicy
{
    /**
     * Register the model→policy pairs for this route group, then pass the request.
     *
     * Middleware params arrive as flat pairs: model1, policy1, model2, policy2, …
     * They are encoded by the setPolicies() macro and decoded here.
     */
    public function handle(Request $request, Closure $next, string ...$pairs): Response
    {
        foreach (array_chunk($pairs, 2) as [$model, $policy]) {
            Gate::policy($model, $policy);
        }

        return $next($request);
    }
}
