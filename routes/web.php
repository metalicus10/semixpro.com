<?php

use App\Livewire\ManagerProfile;
use Illuminate\Support\Facades\Route;
use App\Livewire\Manager;
use App\Livewire\ManagerDashboard;
use App\Livewire\ManagerParts;
use App\Livewire\ManagerTechnicians;
use App\Livewire\Technician;
use App\Livewire\TechnicianDashboard;
use App\Livewire\TechnicianParts;
use App\Livewire\TransferPartForm;

Route::view('/', 'welcome');

/*Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');*/

/*Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');*/

require __DIR__.'/auth.php';

Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/manager', Manager::class)->name('manager.manager');
    Route::get('/manager/manager-dashboard', ManagerDashboard::class)->name('manager.manager-dashboard');
    Route::get('/manager/parts', ManagerParts::class)->name('manager.parts');
    Route::get('/manager/transfer', TransferPartForm::class)->name('manager.transfer');
    Route::get('/manager/technicians', ManagerTechnicians::class)->name('manager.technicians');
    Route::get('/manager/manager-profile', ManagerProfile::class)->name('manager.manager-profile');
});

Route::middleware(['auth', 'role:technician'])->group(function () {
    Route::get('/technician', Technician::class)->name('technician.technician');
    Route::get('/technician/technician-dashboard', TechnicianDashboard::class)->name('technician.technician-dashboard');
    Route::get('/technician/parts', TechnicianParts::class)->name('technician.parts');
});
