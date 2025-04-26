<?php 
namespace App\Services;

use App\Repositories\Interfaces\LeadRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LeadService
{
    protected $leadRepository;
    
    public function __construct(LeadRepositoryInterface $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }
    
    public function getAllPaginated(int $perPage = 25)
    {
        return $this->leadRepository->paginate($perPage, ['*'], ['status', 'tags']);
    }
    
    public function search(array $filters, int $perPage = 25)
    {
        // Cache complex searches to improve performance with large datasets
        $cacheKey = 'leads_search_' . md5(json_encode($filters) . $perPage);
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters, $perPage) {
            return $this->leadRepository->search($filters, $perPage);
        });
    }
    
    public function getById(int $id)
    {
        return $this->leadRepository->find($id, ['*'], [
            'status', 
            'tags', 
            'sources', 
            'nationality', 
            'residenceCountry',
            'personalPhoneCountry',
            'businessPhoneCountry',
            'homePhoneCountry',
            'creator',
            'updater'
        ]);
    }
    
    public function create(array $data)
    {
        // Add user ID to created_by
        $data['created_by'] = Auth::id();
        
        // Create lead
        $lead = $this->leadRepository->create($data);
        
        // Sync tags if provided
        if (!empty($data['tags'])) {
            $this->leadRepository->syncTags($lead->id, $data['tags']);
        }
        
        // Sync sources if provided
        if (!empty($data['sources'])) {
            $this->leadRepository->syncSources($lead->id, $data['sources']);
        }
        
        // Clear any cached searches
        $this->clearSearchCache();
        
        return $lead->fresh(['status', 'tags', 'sources']);
    }
    
    public function update(int $id, array $data)
    {
        // Add user ID to updated_by
        $data['updated_by'] = Auth::id();
        
        // Update lead
        $lead = $this->leadRepository->update($id, $data);
        
        // Sync tags if provided
        if (isset($data['tags'])) {
            $this->leadRepository->syncTags($id, $data['tags']);
        }
        
        // Sync sources if provided
        if (isset($data['sources'])) {
            $this->leadRepository->syncSources($id, $data['sources']);
        }
        
        // Clear any cached searches
        $this->clearSearchCache();
        
        return $lead->fresh(['status', 'tags', 'sources']);
    }
    
    public function delete(int $id)
    {
        $result = $this->leadRepository->delete($id);
        
        // Clear any cached searches
        $this->clearSearchCache();
        
        return $result;
    }
    
    public function getDashboardStats()
    {
        return Cache::remember('dashboard_stats', now()->addHours(1), function () {
            return [
                'leads_by_status' => $this->leadRepository->getCountByStatus(),
                'leads_by_source' => $this->leadRepository->getCountBySource(),
                'recent_leads' => $this->leadRepository->getRecent(5)
            ];
        });
    }
    
    public function clearSearchCache()
    {
        Cache::flush();
    }
}