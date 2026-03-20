# brighterly/laravel-dynamic-policies

Namespace-scoped Laravel policies per route group via a `setPolicies()` macro.

Laravel's default policy auto-discovery is global — one policy class per model across the whole app. This package lets you register **different policies for the same model depending on which route group the request belongs to**, using a simple fluent macro.

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Installation

```bash
composer require brighterly/laravel-dynamic-policies
```

The service provider is auto-discovered via Laravel's package auto-discovery.

## Usage

### 1. Define a `PoliciesMap` for each namespace

Create an interface (or class) implementing `Brighterly\LaravelPolicies\Contracts\PoliciesMap` and declare the `POLICIES_MAPPING` constant:

```php
// app/Policies/Tutor/TutorPoliciesMap.php
namespace App\Policies\Tutor;

use App\Models\Booking;
use App\Models\TutorTask;
use Brighterly\LaravelPolicies\Contracts\PoliciesMap;

final class TutorPoliciesMap implements PoliciesMap
{
    public const array POLICIES_MAPPING = [
        Booking::class  => BookingPolicy::class,
        TutorTask::class => TutorTaskPolicy::class,
    ];
}
```

```php
// app/Policies/Customer/CustomerPoliciesMap.php
namespace App\Policies\Customer;

use App\Models\Booking;
use Brighterly\LaravelPolicies\Contracts\PoliciesMap;

final class CustomerPoliciesMap implements PoliciesMap
{
    public const array POLICIES_MAPPING = [
        Booking::class => BookingPolicy::class,
    ];
}
```

Both maps register a policy for `Booking` — but a different one per namespace.

### 2. Attach the map to a route group

Call `->setPolicies()` before `->group()` in your `RouteServiceProvider`:

```php
use App\Policies\Customer\CustomerPoliciesMap;
use App\Policies\Tutor\TutorPoliciesMap;

Route::prefix('tutor')
    ->setPolicies(TutorPoliciesMap::POLICIES_MAPPING)
    ->group(base_path('routes/tutor.php'));

Route::prefix('cs')
    ->middleware('api')
    ->setPolicies(CustomerPoliciesMap::POLICIES_MAPPING)
    ->group(base_path('routes/customer_api.php'));
```

The macro works on both `Route` (fluent route builder) and `RouteRegistrar` (the object returned when you chain attributes like `->prefix()`, `->middleware()`, etc.).

### 3. Authorize as usual

Nothing changes in controllers or policies — use Laravel's standard authorization:

```php
// in a controller
$this->authorize('update', $booking);

// or via Gate
Gate::authorize('view', $booking);

// or in Blade
@can('update', $booking)
```
The correct policy class is resolved based on which route group handled the request.

## PhpStorm plugin

For the best experience you can use this plugin [Custom Policy Navigator PhpStorm plugin](https://plugins.jetbrains.com/plugin/30763-laravel-custom-policy-navigator)

## How it works

`setPolicies()` attaches the `SetCustomPolicy` middleware to the route group, encoding the `[Model::class => Policy::class]` pairs as middleware parameters. When a request hits a route in the group, the middleware calls `Gate::policy($model, $policy)` for each pair — overriding Laravel's global policy registry for that request only.

## Optional: shared `PoliciesMap` contract in your app

If you want a shared base interface at `App\Policies\Contracts\PoliciesMap` (required by the [Custom Policy Navigator PhpStorm plugin](https://github.com/brighterly/tools-laravel-policies-plugin)), publish the stub:

```bash
php artisan vendor:publish --tag=laravel-policies-stub
```

This creates `app/Policies/Contracts/PoliciesMap.php` extending the package interface. Your maps then implement `App\Policies\Contracts\PoliciesMap` instead of the package interface directly.

## Local development

To use the package from a local path (e.g. alongside the app in a monorepo), add a path repository to `composer.json`:

```json
"repositories": [
    {
        "name": "laravel-policies",
        "type": "path",
        "url": "../tools-laravel-policies-lib",
        "options": { "symlink": false }
    }
]
```

> Use `"symlink": false` when the app runs inside Docker — symlinks to host paths are not accessible inside the container. After each change to the library, run `composer update brighterly/laravel-policies` inside the container.

## License

MIT
