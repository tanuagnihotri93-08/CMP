<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Observers\CrmAuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $observer = CrmAuditObserver::class;

        Lead::observe($observer);
        Customer::observe($observer);
        FollowUp::observe($observer);
        Activity::observe($observer);
    }
}
