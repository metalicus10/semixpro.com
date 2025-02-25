<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Orchid\Platform\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manager = Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name'        => 'Менеджер',
                'permissions' => [
                    'manager'            => true,
                    'view_warehouses'    => true,
                    'manage_warehouses'  => true,
                    'view_parts'         => true,
                    'manage_parts'       => true,
                    'view_nomenclatures'  => true,
                    'manage_nomenclatures'       => true,
                ],
            ]
        );

        $technician = Role::updateOrCreate(
            ['slug' => 'technician'],
            [
                'name'        => 'Техник',
                'permissions' => [
                    'technician'         => true,
                    'view_warehouses'    => true, // Только просмотр
                    'view_parts'         => true, // Только просмотр
                    'view_nomenclatures'  => false, // Нет доступа к номенклатуре
                ],
            ]
        );
    }
}
