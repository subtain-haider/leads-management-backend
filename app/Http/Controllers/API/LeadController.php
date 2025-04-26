<?php
// app/Http/Controllers/API/LeadController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadStoreRequest;
use App\Http\Requests\LeadUpdateRequest;
use App\Http\Resources\LeadCollection;
use App\Http\Resources\LeadResource;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    protected $leadService;
    
    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }
    
    /**
     * Display a listing of leads with filtering.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 25);
            
            // Prepare filters from request
            $filters = $request->only([
                'search', 'status_id', 'nationality_id', 'residence_country_id', 
                'gender', 'created_by', 'date_from', 'date_to', 'tags', 'sources',
                'sort_field', 'sort_direction'
            ]);
            
            // Add relations to eager load
            $filters['with_relations'] = ['status', 'tags'];
            
            // Get results
            $leads = $this->leadService->search($filters, $perPage);
            
            return new LeadCollection($leads);
        } catch (\Exception $e) {
            Log::error('Error retrieving leads: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve leads',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store a newly created lead.
     */
    public function store(LeadStoreRequest $request)
    {
        try {
            $lead = $this->leadService->create($request->validated());
            
            return new LeadResource($lead);
        } catch (\Exception $e) {
            Log::error('Error creating lead: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create lead',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified lead.
     */
    public function show($id)
    {
        try {
            $lead = $this->leadService->getById($id);
            
            return new LeadResource($lead);
        } catch (\Exception $e) {
            Log::error('Error retrieving lead: ' . $e->getMessage());
            return response()->json([
                'message' => 'Lead not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    
    /**
     * Update the specified lead.
     */
    public function update(LeadUpdateRequest $request, $id)
    {
        try {
            $lead = $this->leadService->update($id, $request->validated());
            
            return new LeadResource($lead);
        } catch (\Exception $e) {
            Log::error('Error updating lead: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update lead',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified lead.
     */
    public function destroy($id)
    {
        try {
            $this->leadService->delete($id);
            
            return response()->json([
                'message' => 'Lead deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting lead: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete lead',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export leads as CSV.
     */
    public function export(Request $request)
    {
        try {
            // Implementation for exporting would go here
            // This would likely use a job for large datasets
            return response()->json([
                'message' => 'Export started, you will be notified when it is ready.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting leads: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to export leads',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Import leads from CSV.
     */
    public function import(Request $request)
    {
        try {
            // Implementation for importing would go here
            // This would likely use a job for large datasets
            return response()->json([
                'message' => 'Import started, you will be notified when it is complete.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error importing leads: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to import leads',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}