<?php

use App\Http\Controllers\API\ActivityController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\LeadController;
use App\Http\Controllers\API\LeadSourceController;
use App\Http\Controllers\API\LeadStatusController;
use App\Http\Controllers\API\NoteController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\TagController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// API Routes with versioning
Route::prefix('v1')->middleware('api')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        // Test protected route
        Route::get('/test', function () {
            return response()->json(['message' => 'You are authenticated!']);
        });

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index']);
        
        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('leads-by-status', [ReportController::class, 'leadsByStatus']);
            Route::get('leads-by-source', [ReportController::class, 'leadsBySource']);
            Route::get('leads-by-country', [ReportController::class, 'leadsByCountry']);
            Route::get('activity-summary', [ReportController::class, 'activitySummary']);
        });
        
        // Leads
        Route::apiResource('leads', LeadController::class);
        Route::get('leads/export', [LeadController::class, 'export']);
        Route::post('leads/import', [LeadController::class, 'import']);
        
        // Lead notes
        Route::apiResource('leads.notes', NoteController::class)->shallow();
        
        // Lead activities
        Route::apiResource('leads.activities', ActivityController::class)->shallow();
        
        // Lead management resources
        Route::apiResource('lead-statuses', LeadStatusController::class);
        Route::apiResource('lead-sources', LeadSourceController::class);
        Route::apiResource('tags', TagController::class);
        
        // Reference data
        Route::apiResource('countries', CountryController::class)->only(['index', 'show']);
    });
});