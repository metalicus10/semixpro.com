<?php

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

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::post('/manager', Manager::class)->name('manager.manager');
    Route::post('/manager/manager-dashboard', ManagerDashboard::class)->name('manager.manager-dashboard');
    Route::post('/manager/parts', ManagerParts::class)->name('manager.parts');
    Route::post('/manager/transfer', TransferPartForm::class)->name('manager.transfer');
    Route::post('/manager/technicians', ManagerTechnicians::class)->name('manager.technicians');
});

Route::middleware(['auth', 'role:technician'])->group(function () {
    Route::post('/technician', Technician::class)->name('technician.technician');
    Route::post('/technician/technician-dashboard', TechnicianDashboard::class)->name('technician.technician-dashboard');
    Route::post('/technician/parts', TechnicianParts::class)->name('technician.parts');
});
