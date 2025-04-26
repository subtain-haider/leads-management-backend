<?php
namespace App\Repositories\Eloquent;

use App\Models\Lead;
use App\Repositories\Interfaces\LeadRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class LeadRepository extends BaseRepository implements LeadRepositoryInterface
{
    public function __construct(Lead $model)
    {
        parent::__construct($model);
    }

    public function search(array $filters, int $perPage = 25)
    {
        $query = $this->model->query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('personal_phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if (!empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        // Filter by nationality
        if (!empty($filters['nationality_id'])) {
            $query->where('nationality_id', $filters['nationality_id']);
        }

        // Filter by residence country
        if (!empty($filters['residence_country_id'])) {
            $query->where('residence_country_id', $filters['residence_country_id']);
        }

        // Filter by gender
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        // Filter by creator
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Filter by tags
        if (!empty($filters['tags'])) {
            $query->whereHas('tags', function($q) use ($filters) {
                $q->whereIn('tags.id', $filters['tags']);
            });
        }

        // Filter by sources
        if (!empty($filters['sources'])) {
            $query->whereHas('sources', function($q) use ($filters) {
                $q->whereIn('lead_sources.id', $filters['sources']);
            });
        }

        // Apply sorting
        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        
        // Ensure the sort field is actually a column in the table
        $allowedSortFields = ['first_name', 'last_name', 'email', 'created_at', 'updated_at'];
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Get relations
        $relations = [];
        if (!empty($filters['with_relations'])) {
            if (in_array('status', $filters['with_relations'])) $relations[] = 'status';
            if (in_array('tags', $filters['with_relations'])) $relations[] = 'tags';
            if (in_array('sources', $filters['with_relations'])) $relations[] = 'sources';
            if (in_array('nationality', $filters['with_relations'])) $relations[] = 'nationality';
            if (in_array('residence', $filters['with_relations'])) $relations[] = 'residenceCountry';
            if (in_array('creator', $filters['with_relations'])) $relations[] = 'creator';
        }

        // Return paginated results
        return $query->with($relations)->paginate($perPage)->withQueryString();
    }

    public function getCountByStatus()
    {
        return DB::table('leads')
            ->select('status_id', DB::raw('count(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('status_id')
            ->get()
            ->pluck('count', 'status_id')
            ->toArray();
    }

    public function getCountBySource()
    {
        return DB::table('lead_lead_source')
            ->select('lead_source_id', DB::raw('count(*) as count'))
            ->groupBy('lead_source_id')
            ->get()
            ->pluck('count', 'lead_source_id')
            ->toArray();
    }

    public function getByStatus(int $statusId, int $perPage = 25)
    {
        return $this->model->where('status_id', $statusId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByCountry(int $countryId, string $type = 'residence', int $perPage = 25)
    {
        $column = $type === 'nationality' ? 'nationality_id' : 'residence_country_id';
        
        return $this->model->where($column, $countryId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getRecent(int $limit = 10)
    {
        return $this->model->with(['status', 'tags'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function syncTags(int $leadId, array $tagIds)
    {
        $lead = $this->find($leadId);
        $lead->tags()->sync($tagIds);
        return $lead->fresh(['tags']);
    }

    public function syncSources(int $leadId, array $sourceIds)
    {
        $lead = $this->find($leadId);
        
        $sourcesWithTimestamp = collect($sourceIds)->mapWithKeys(function ($id) {
            return [$id => ['associated_at' => now()]];
        })->toArray();
        
        $lead->sources()->sync($sourcesWithTimestamp);
        return $lead->fresh(['sources']);
    }
}