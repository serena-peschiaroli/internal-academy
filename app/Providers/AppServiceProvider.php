<?php

namespace App\Providers;

use App\Models\Registration;
use App\Models\Workshop;
use App\Policies\RegistrationPolicy;
use App\Policies\WorkshopPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureAuthorization();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Configure roles, gates and policies.
     */
    protected function configureAuthorization(): void
    {
        Gate::define('access-admin-area', fn ($user) => $user->isAdmin());
        Gate::define('access-employee-area', fn ($user) => $user->isEmployee());

        Gate::policy(Workshop::class, WorkshopPolicy::class);
        Gate::policy(Registration::class, RegistrationPolicy::class);
    }
}
