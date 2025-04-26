<?php
namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface LeadRepositoryInterface extends RepositoryInterface
{
    // Advanced search with filtering
    public function search(array $filters, int $perPage = 25);
    
    // Lead stats by status
    public function getCountByStatus();
    
    // Lead stats by source
    public function getCountBySource();
    
    // Get leads filtered by status
    public function getByStatus(int $statusId, int $perPage = 25);
    
    // Get leads filtered by country
    public function getByCountry(int $countryId, string $type = 'residence', int $perPage = 25);
    
    // Get recently created leads
    public function getRecent(int $limit = 10);
    
    // Add/remove tags to a lead
    public function syncTags(int $leadId, array $tagIds);
    
    // Add/remove sources to a lead
    public function syncSources(int $leadId, array $sourceIds);
}