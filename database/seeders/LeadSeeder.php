<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Note;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Don't seed leads in production mode
        if (app()->environment() === 'production') {
            return;
        }

        // Get IDs of available sources, statuses, and tags
        $sourceIds = LeadSource::where('active', true)->pluck('id')->toArray();
        $tagIds = Tag::pluck('id')->toArray();

        // Generate a variable number of leads for each environment
        $leadsCount = 1000;

        // This will generate leads in batches to improve performance
        $batchSize = 100;
        $batches = ceil($leadsCount / $batchSize);

        for ($i = 0; $i < $batches; $i++) {
            // Calculate batch size (last batch may be smaller)
            $currentBatchSize = min($batchSize, $leadsCount - ($i * $batchSize));
            
            // Calculate distribution
            $newLeadsCount = (int)($currentBatchSize * 0.4);
            $wonLeadsCount = (int)($currentBatchSize * 0.2);
            $lostLeadsCount = (int)($currentBatchSize * 0.1);
            $otherLeadsCount = $currentBatchSize - $newLeadsCount - $wonLeadsCount - $lostLeadsCount;
            
            $this->command->info("Creating batch " . ($i + 1) . " of $batches (size: $currentBatchSize)");
            
            // Use database transactions for better performance
            DB::beginTransaction();
            
            try {
                // Create leads with different statuses
                $this->createLeadsWithRelations($newLeadsCount, 'asNew', $sourceIds, $tagIds);
                $this->createLeadsWithRelations($wonLeadsCount, 'won', $sourceIds, $tagIds);
                $this->createLeadsWithRelations($lostLeadsCount, 'lost', $sourceIds, $tagIds);
                $this->createLeadsWithRelations($otherLeadsCount, null, $sourceIds, $tagIds);
                
                DB::commit();
                $this->command->info("Completed batch " . ($i + 1));
            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("Error in batch $i: " . $e->getMessage());
                throw $e;
            }
        }

        $this->command->info("Generated $leadsCount leads with related data");
    }
    
    /**
     * Create leads with a specific status and add relations
     */
    private function createLeadsWithRelations($count, $status = null, $sourceIds = [], $tagIds = [])
    {
        if ($count <= 0) {
            return;
        }
        
        $this->command->info("Creating $count leads with status: " . ($status ?? 'random'));
        
        // Create leads in smaller chunks for better memory management
        $chunkSize = 10;
        
        for ($i = 0; $i < $count; $i += $chunkSize) {
            $currentCount = min($chunkSize, $count - $i);
            
            // Create a factory query
            $factory = Lead::factory()->count($currentCount);
            
            // Apply status method if provided
            $leads = $status ? $factory->$status()->create() : $factory->create();
            
            // Add relations to each lead
            foreach ($leads as $lead) {
                // Add sources
                if (!empty($sourceIds)) {
                    $sourceCount = min(rand(1, 3), count($sourceIds));
                    $sourcesToAttach = collect($sourceIds)->random($sourceCount)->mapWithKeys(function ($id) {
                        return [$id => ['associated_at' => now()->subDays(rand(0, 180))]];
                    })->toArray();
                    $lead->sources()->attach($sourcesToAttach);
                }
                
                // Add tags
                if (!empty($tagIds) && rand(0, 100) > 20) {
                    $tagsCount = min(rand(1, 5), count($tagIds));
                    $tagsToAttach = collect($tagIds)->random($tagsCount)->toArray();
                    $lead->tags()->attach($tagsToAttach);
                }
                
                // Add notes
                $notesCount = rand(0, 5);
                if ($notesCount > 0) {
                    Note::factory($notesCount)->create([
                        'lead_id' => $lead->id
                    ]);
                }
                
                // Add activities
                $activitiesCount = rand(0, 10);
                if ($activitiesCount > 0) {
                    Activity::factory($activitiesCount)->create([
                        'lead_id' => $lead->id
                    ]);
                }
            }
        }
    }
}