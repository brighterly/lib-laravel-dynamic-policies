<?php

declare(strict_types=1);

namespace Brighterly\LaravelPolicies\Contracts;

interface PoliciesMap
{
    /**
     * Maps model FQNs to their policy FQNs for this namespace group.
     *
     * Example:
     *   public const array POLICIES_MAPPING = [
     *       Booking::class => BookingPolicy::class,
     *       Customer::class => CustomerPolicy::class,
     *   ];
     *
     * @var array<class-string, class-string>
     */
    public const array POLICIES_MAPPING = [];
}
