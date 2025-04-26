<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get random countries for the different fields (or null in some cases)
        $countries = Country::pluck('id')->toArray();
        $countryId = fake()->randomElement($countries);
        
        // Sometimes have the same country for phone and nationality/residence, sometimes different
        $useSharedCountry = fake()->boolean(70);
        $phoneCountryId = $useSharedCountry ? $countryId : fake()->randomElement($countries);
        
        // Get a random status
        $statusId = LeadStatus::pluck('id')->random();
        
        // Get a random user for created_by
        $userId = User::pluck('id')->random();
        
        // 30% chance for the business phone to exist
        $hasBusinessPhone = fake()->boolean(30);
        
        // 20% chance for the home phone to exist
        $hasHomePhone = fake()->boolean(20);
        
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'personal_phone_country_id' => $phoneCountryId,
            'personal_phone' => fake()->numerify('##########'),
            'description' => fake()->optional(0.7)->paragraph(),
            'address' => fake()->optional(0.6)->address(),
            'business_phone_country_id' => $hasBusinessPhone ? $phoneCountryId : null,
            'business_phone' => $hasBusinessPhone ? fake()->numerify('##########') : null,
            'home_phone_country_id' => $hasHomePhone ? $phoneCountryId : null,
            'home_phone' => $hasHomePhone ? fake()->numerify('##########') : null,
            'nationality_id' => fake()->optional(0.8)->randomElement($countries),
            'residence_country_id' => fake()->optional(0.8)->randomElement($countries),
            'dob' => fake()->optional(0.7)->dateTimeBetween('-70 years', '-18 years'),
            'gender' => fake()->optional(0.9)->randomElement(['male', 'female', 'other', 'prefer_not_to_say']),
            'status_id' => $statusId,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'created_by' => $userId,
            'updated_by' => fake()->boolean(30) ? $userId : null,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Lead $lead) {
            // Calculate the updated_at timestamp to be sometime after the created_at
            if ($lead->updated_by !== null) {
                $lead->updated_at = fake()->dateTimeBetween($lead->created_at, 'now');
                $lead->save();
            }
        });
    }

    /**
     * Indicate that the lead is in a new status.
     */
    public function asNew(): static
    {
        $newStatusId = LeadStatus::where('name', 'New')->first()->id ?? 
                       LeadStatus::orderBy('order')->first()->id;

        return $this->state(fn (array $attributes) => [
            'status_id' => $newStatusId,
        ]);
    }

    /**
     * Indicate that the lead is in a won status.
     */
    public function won(): static
    {
        $wonStatusId = LeadStatus::where('name', 'Won')->first()->id ?? 
                       LeadStatus::inRandomOrder()->first()->id;

        return $this->state(fn (array $attributes) => [
            'status_id' => $wonStatusId,
        ]);
    }

    /**
     * Indicate that the lead is in a lost status.
     */
    public function lost(): static
    {
        $lostStatusId = LeadStatus::where('name', 'Lost')->first()->id ?? 
                        LeadStatus::inRandomOrder()->first()->id;

        return $this->state(fn (array $attributes) => [
            'status_id' => $lostStatusId,
        ]);
    }
}