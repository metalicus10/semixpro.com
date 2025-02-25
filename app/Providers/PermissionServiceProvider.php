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
        $permissions = ItemPermission::group(__('Main'))
            ->addPermission('manager', __('Access to manager features'))
            ->addPermission('technician', __('Access to technician features'))
            ->addPermission('view_warehouses', __('View Warehouses'))
            ->addPermission('manage_warehouses', __('Manage Warehouses'))
            ->addPermission('view_parts', __('View Parts'))
            ->addPermission('manage_parts', __('Manage Parts'))
            ->addPermission('view_nomenclature', __('View Nomenclature'))
            ->addPermission('manage_nomenclature', __('Manage Nomenclature'));;

        $dashboard->registerPermissions($permissions);
    }
}
