<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Example protected route
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // RBAC Management
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/permissions', [RoleController::class, 'permissions']);
    Route::post('/roles/{role}/permissions', [RoleController::class, 'assignPermissions']);
    Route::post('/users/{user}/roles', [RoleController::class, 'assignRoleToUser']);

    // Parcel Management
    Route::get('/parcels', [\App\Http\Controllers\Api\ParcelController::class, 'index']);
    Route::post('/parcels', [\App\Http\Controllers\Api\ParcelController::class, 'store']);
    Route::get('/parcels/{id}', [\App\Http\Controllers\Api\ParcelController::class, 'show']);
    Route::post('/parcels/{id}/deliver', [\App\Http\Controllers\Api\ParcelController::class, 'deliver']);
    Route::delete('/parcels/{id}', [\App\Http\Controllers\Api\ParcelController::class, 'destroy']);
});
