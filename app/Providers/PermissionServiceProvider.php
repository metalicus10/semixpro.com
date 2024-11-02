<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * @param Dashboard $dashboard
     */
    public function boot(Dashboard $dashboard): void
    {
        $permissions = ItemPermission::group('Main')
            ->addPermission('manager', 'Access to manager features')
            ->addPermission('technician', 'Access to technician features');

        $dashboard->registerPermissions($permissions);
    }
}
