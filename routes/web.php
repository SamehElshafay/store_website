<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Contacts (Senders & Recipients)
    Route::resource('contacts', ContactController::class);
    Route::get('contacts-search', [ContactController::class, 'search'])->name('contacts.search');

    // Parcels
    Route::get('parcels', [App\Http\Controllers\Api\ParcelController::class, 'index'])->name('parcels.index');
    Route::get('parcels-export', [App\Http\Controllers\Api\ParcelController::class, 'export'])->name('parcels.export');
    Route::get('parcels/{id}', [App\Http\Controllers\HomeController::class, 'showParcel'])->name('parcels.show');
    Route::get('parcels/find/{barcode}', [App\Http\Controllers\Api\ParcelController::class, 'findByBarcode'])->name('parcels.find');
    Route::get('parcels/{id}/json', [App\Http\Controllers\Api\ParcelController::class, 'show'])->name('parcels.json');
    Route::post('parcels', [App\Http\Controllers\Api\ParcelController::class, 'store']);
    Route::post('parcels/{id}/deliver', [App\Http\Controllers\Api\ParcelController::class, 'deliver']);
    Route::post('parcels/{id}/status', [App\Http\Controllers\Api\ParcelController::class, 'updateStatus'])->name('parcels.status.update');
    Route::post('parcels/bulk-status', [App\Http\Controllers\Api\ParcelController::class, 'bulkUpdateStatus'])->name('parcels.bulk.status');
    Route::post('parcels/bulk-status-barcode', [App\Http\Controllers\Api\ParcelController::class, 'bulkUpdateStatusByBarcode'])->name('parcels.bulk.status.barcode');
    Route::post('parcels/bulk-register', [App\Http\Controllers\Api\ParcelController::class, 'bulkRegister'])->name('parcels.bulk.register');
    Route::delete('parcels/{id}', [App\Http\Controllers\Api\ParcelController::class, 'destroy']);

    // Status Management
    Route::resource('parcel-statuses', \App\Http\Controllers\ParcelStatusController::class);
    Route::post('parcel-statuses/reorder', [\App\Http\Controllers\ParcelStatusController::class, 'reorder'])->name('parcel-statuses.reorder');
    Route::post('parcel-statuses/{id}/default', [\App\Http\Controllers\ParcelStatusController::class, 'setDefault'])->name('parcel-statuses.default');
    Route::post('parcel-statuses/{id}/toggle-modal', [\App\Http\Controllers\ParcelStatusController::class, 'toggleModal'])->name('parcel-statuses.toggle-modal');

});
