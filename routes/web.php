<?php

use App\Http\Controllers\AntgoReAdminController;
use App\Http\Controllers\AntgoReCharacteristicController;
use App\Http\Controllers\AntgoReDateController;
use App\Http\Controllers\AntgoReEquipmentController;
use App\Http\Controllers\AntgoReInfrastructureController;
use App\Http\Controllers\AntgoReMeasureController;
use App\Http\Controllers\AntgoReRoleController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('admins', AntgoReAdminController::class)
        ->parameters(['admins' => 'admin'])
        ->except(['show']);

    Route::resource('roles', AntgoReRoleController::class)
        ->parameters(['roles' => 'role'])
        ->except(['show', 'create']);
    Route::get('roles/template-csv', [AntgoReRoleController::class, 'downloadTemplateCsv'])->name('roles.template-csv');
    Route::post('roles/import-csv', [AntgoReRoleController::class, 'importCsv'])->name('roles.import-csv');

    Route::resource('dates', AntgoReDateController::class)
        ->parameters(['dates' => 'date'])
        ->except(['show', 'create']);
    Route::get('dates/template-csv', [AntgoReDateController::class, 'downloadTemplateCsv'])->name('dates.template-csv');
    Route::post('dates/import-csv', [AntgoReDateController::class, 'importCsv'])->name('dates.import-csv');

    Route::resource('characteristics', AntgoReCharacteristicController::class)
        ->parameters(['characteristics' => 'characteristic'])
        ->except(['show', 'create']);
    Route::get('characteristics/template-csv', [AntgoReCharacteristicController::class, 'downloadTemplateCsv'])->name('characteristics.template-csv');
    Route::post('characteristics/import-csv', [AntgoReCharacteristicController::class, 'importCsv'])->name('characteristics.import-csv');

    Route::resource('equipments', AntgoReEquipmentController::class)
        ->parameters(['equipments' => 'equipment'])
        ->except(['show', 'create']);
    Route::get('equipments/template-csv', [AntgoReEquipmentController::class, 'downloadTemplateCsv'])->name('equipments.template-csv');
    Route::post('equipments/import-csv', [AntgoReEquipmentController::class, 'importCsv'])->name('equipments.import-csv');

    Route::resource('infrastructures', AntgoReInfrastructureController::class)
        ->parameters(['infrastructures' => 'infrastructure'])
        ->except(['show', 'create']);
    Route::get('infrastructures/template-csv', [AntgoReInfrastructureController::class, 'downloadTemplateCsv'])->name('infrastructures.template-csv');
    Route::post('infrastructures/import-csv', [AntgoReInfrastructureController::class, 'importCsv'])->name('infrastructures.import-csv');

    Route::resource('measures', AntgoReMeasureController::class)
        ->parameters(['measures' => 'measure'])
        ->except(['show', 'create']);
    Route::get('measures/template-csv', [AntgoReMeasureController::class, 'downloadTemplateCsv'])->name('measures.template-csv');
    Route::post('measures/import-csv', [AntgoReMeasureController::class, 'importCsv'])->name('measures.import-csv');

    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
    Route::post('users/import-csv', [UserManagementController::class, 'importCsv'])->name('users.import-csv');
    Route::get('users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/settings.php';
