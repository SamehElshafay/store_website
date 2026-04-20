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
    Route::post('parcels', [App\Http\Controllers\Api\ParcelController::class, 'store']);
    Route::post('parcels/{id}/deliver', [App\Http\Controllers\Api\ParcelController::class, 'deliver']);
    Route::post('parcels/{id}/status', [App\Http\Controllers\Api\ParcelController::class, 'updateStatus'])->name('parcels.status.update');
    Route::post('parcels/bulk-status', [App\Http\Controllers\Api\ParcelController::class, 'bulkUpdateStatus'])->name('parcels.bulk.status');
    Route::delete('parcels/{id}', [App\Http\Controllers\Api\ParcelController::class, 'destroy']);

    // Status Management
    Route::resource('parcel-statuses', \App\Http\Controllers\ParcelStatusController::class);
    Route::post('parcel-statuses/reorder', [\App\Http\Controllers\ParcelStatusController::class, 'reorder'])->name('parcel-statuses.reorder');
    Route::post('parcel-statuses/{id}/default', [\App\Http\Controllers\ParcelStatusController::class, 'setDefault'])->name('parcel-statuses.default');

});
