<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Note;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateLeadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:generate 
                            {count=10000 : Number of leads to generate}
                            {--chunk=100 : Size of each processing chunk}
                            {--notes=3 : Max number of notes per lead}
                            {--activities=5 : Max number of activities per lead}
                            {--tags=3 : Max number of tags per lead}
                            {--sources=2 : Max number of sources per lead}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a large number of test leads and related data for performance testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');
        $chunkSize = (int) $this->option('chunk');
        $maxNotes = (int) $this->option('notes');
        $maxActivities = (int) $this->option('activities');
        $maxTags = (int) $this->option('tags');
        $maxSources = (int) $this->option('sources');

        // Confirm before generating a large number of leads
        if ($count > 10000 && !$this->confirm("You're about to generate $count leads. This can take a while and use significant database space. Continue?")) {
            return;
        }

        $this->info("Starting generation of $count leads...");
        $this->newLine();

        // Get IDs of available statuses, sources, and tags
        $statusIds = LeadStatus::where('active', true)->pluck('id')->toArray();
        $sourceIds = LeadSource::where('active', true)->pluck('id')->toArray();
        $tagIds = Tag::pluck('id')->toArray();

        // Create a progress bar
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        // Calculate number of chunks
        $chunks = ceil($count / $chunkSize);

        // Use status distribution - most leads are new or in early stages
        $newStatusId = LeadStatus::where('name', 'New')->first()->id ?? $statusIds[0];
        $contactedStatusId = LeadStatus::where('name', 'Contacted')->first()->id ?? $statusIds[1] ?? $statusIds[0];
        $qualifiedStatusId = LeadStatus::where('name', 'Qualified')->first()->id ?? $statusIds[2] ?? $statusIds[0];
        $wonStatusId = LeadStatus::where('name', 'Won')->first()->id ?? $statusIds[5] ?? $statusIds[0];
        $lostStatusId = LeadStatus::where('name', 'Lost')->first()->id ?? $statusIds[6] ?? $statusIds[0];

        for ($i = 0; $i < $chunks; $i++) {
            // Calculate actual chunk size (last chunk may be smaller)
            $currentChunkSize = min($chunkSize, $count - ($i * $chunkSize));
            
            DB::beginTransaction();
            
            try {
                // Create leads with weighted status distribution
                $newLeads = (int)($currentChunkSize * 0.4); // 40% new
                $contactedLeads = (int)($currentChunkSize * 0.25); // 25% contacted
                $qualifiedLeads = (int)($currentChunkSize * 0.15); // 15% qualified
                $wonLeads = (int)($currentChunkSize * 0.1); // 10% won
                $lostLeads = (int)($currentChunkSize * 0.1); // 10% lost
                
                // Adjust for rounding errors
                $total = $newLeads + $contactedLeads + $qualifiedLeads + $wonLeads + $lostLeads;
                if ($total < $currentChunkSize) {
                    $newLeads += ($currentChunkSize - $total);
                }
                
                // Generate leads
                $leads = [];
                
                if ($newLeads > 0) {
                    Lead::factory($newLeads)->create(['status_id' => $newStatusId])->each(function ($lead) use (&$leads) {
                        $leads[] = $lead;
                    });
                }
                
                if ($contactedLeads > 0) {
                    Lead::factory($contactedLeads)->create(['status_id' => $contactedStatusId])->each(function ($lead) use (&$leads) {
                        $leads[] = $lead;
                    });
                }
                
                if ($qualifiedLeads > 0) {
                    Lead::factory($qualifiedLeads)->create(['status_id' => $qualifiedStatusId])->each(function ($lead) use (&$leads) {
                        $leads[] = $lead;
                    });
                }
                
                if ($wonLeads > 0) {
                    Lead::factory($wonLeads)->create(['status_id' => $wonStatusId])->each(function ($lead) use (&$leads) {
                        $leads[] = $lead;
                    });
                }
                
                if ($lostLeads > 0) {
                    Lead::factory($lostLeads)->create(['status_id' => $lostStatusId])->each(function ($lead) use (&$leads) {
                        $leads[] = $lead;
                    });
                }
                
                // Add related data for each lead
                foreach ($leads as $lead) {
                    // Add sources
                    if (!empty($sourceIds)) {
                        $sourceCount = rand(1, min($maxSources, count($sourceIds)));
                        $selectedSources = collect($sourceIds)->random($sourceCount)->mapWithKeys(function ($id) {
                            return [$id => ['associated_at' => now()->subDays(rand(0, 180))]];
                        })->toArray();
                        $lead->sources()->attach($selectedSources);
                    }
                    
                    // Add tags
                    if (!empty($tagIds)) {
                        $tagCount = rand(0, min($maxTags, count($tagIds)));
                        if ($tagCount > 0) {
                            $selectedTags = collect($tagIds)->random($tagCount)->toArray();
                            $lead->tags()->attach($selectedTags);
                        }
                    }
                    
                    // Add notes
                    $notesCount = rand(0, $maxNotes);
                    if ($notesCount > 0) {
                        Note::factory($notesCount)->create([
                            'lead_id' => $lead->id
                        ]);
                    }
                    
                    // Add activities
                    $activitiesCount = rand(0, $maxActivities);
                    if ($activitiesCount > 0) {
                        Activity::factory($activitiesCount)->create([
                            'lead_id' => $lead->id
                        ]);
                    }
                    
                    $bar->advance();
                }
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error in chunk $i: " . $e->getMessage());
                throw $e;
            }
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Successfully generated $count leads with related data!");
    }
}