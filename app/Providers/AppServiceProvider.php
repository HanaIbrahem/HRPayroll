<?php

namespace App\Providers;

use App\Models\Checklist;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use App\Services\ExcelZonesProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Employee;
use App\Models\User;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //

          $this->app->singleton(ExcelZonesProvider::class, function ($app) {
            return new ExcelZonesProvider();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        Model::preventLazyLoading(true);
        Gate::define('upload-checklist-for-employee', function (User $user, Employee $employee) {
            return (int) $employee->user_id === (int) $user->id;
        });

        Gate::define('edit-checklist-for-employee', function (User $user, Checklist $checklist) {
            return (int) $checklist->user_id === (int) $user->id;
        });
        Paginator::defaultView('vendor.pagination.daisyui');
        Paginator::defaultSimpleView('vendor.pagination.daisyui-simple');
    }
}
