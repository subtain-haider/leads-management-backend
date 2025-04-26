<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $leadId = Lead::pluck('id')->random();
        $activityTypeId = ActivityType::pluck('id')->random();
        $userId = User::pluck('id')->random();
        
        // Activity date is sometime in the past year
        $activityDate = fake()->dateTimeBetween('-1 year', 'now');
        
        // Record creation happens either on the activity day or a few days after (for recording past activities)
        $createdAt = fake()->dateTimeBetween($activityDate, date('Y-m-d', strtotime('+3 days', $activityDate->getTimestamp())));
        
        return [
            'lead_id' => $leadId,
            'activity_type_id' => $activityTypeId,
            'details' => fake()->optional(0.8)->paragraph(),
            'activity_date' => $activityDate,
            'created_at' => $createdAt,
            'created_by' => $userId,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Activity $activity) {
            // Add type-specific details based on activity type
            $typeName = $activity->type?->name ?? '';
            
            // If we are making the model and dont have the relationship loaded yet
            if (empty($typeName) && $activity->activity_type_id) {
                $typeName = ActivityType::find($activity->activity_type_id)?->name ?? '';
            }
            
            // Add appropriate details based on the activity type
            switch (strtolower($typeName)) {
                case 'email':
                    $activity->details = "Subject: " . fake()->sentence() . "\n\n" . fake()->paragraph(3);
                    break;
                    
                case 'call':
                    $callDuration = fake()->numberBetween(1, 60);
                    $activity->details = "Call duration: {$callDuration} minutes\n" . fake()->paragraph();
                    break;
                    
                case 'meeting':
                    $activity->details = "Location: " . fake()->company() . "\nAttendees: " . implode(', ', fake()->words(fake()->numberBetween(2, 5))) . "\n\n" . fake()->paragraph();
                    break;
                    
                case 'task':
                    $activity->details = "Task: " . fake()->sentence() . "\nStatus: " . fake()->randomElement(['Completed', 'In Progress', 'Pending']);
                    break;
                    
                default:
            }
        });
    }
}