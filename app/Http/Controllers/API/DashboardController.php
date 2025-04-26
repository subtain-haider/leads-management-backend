<?php
// app/Http/Controllers/API/DashboardController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $leadService;
    
    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }
    
    /**
     * Get dashboard statistics and recent data.
     */
    public function index()
    {
        try {
            $stats = $this->leadService->getDashboardStats();
            
            return response()->json([
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving dashboard data: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}