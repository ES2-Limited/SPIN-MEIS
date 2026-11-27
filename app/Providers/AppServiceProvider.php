<?php

namespace App\Providers;

use App\Models\Scheme;
use App\Models\User;
use App\Policies\OwnerScopedPolicy;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Scheme::class, OwnerScopedPolicy::class);

        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
                AuditLogger::log('auth.login', $event->user, null, ['email' => $event->user->email], $event->user);
            }
        });

        Event::listen(RoleAttached::class, function (RoleAttached $event) {
            $model = $event->model;
            if ($model instanceof User) {
                AuditLogger::log('role.assigned', $model, null, [
                    'roles' => $event->rolesOrIds,
                    'owner_type' => $model->owner_type,
                    'owner_id' => $model->owner_id,
                ]);
            }
        });

        Event::listen(RoleDetached::class, function (RoleDetached $event) {
            $model = $event->model;
            if ($model instanceof User) {
                AuditLogger::log('role.removed', $model, [
                    'roles' => $event->rolesOrIds,
                ], null);
            }
        });
    }
}
