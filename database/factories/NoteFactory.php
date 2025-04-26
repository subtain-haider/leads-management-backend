<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Note::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $leadId = Lead::pluck('id')->random();
        $userId = User::pluck('id')->random();
        $createdAt = fake()->dateTimeBetween('-1 year', 'now');
        
        return [
            'lead_id' => $leadId,
            'content' => fake()->paragraph(fake()->numberBetween(1, 5)),
            'created_at' => $createdAt,
            'created_by' => $userId,
            'updated_by' => fake()->boolean(20) ? $userId : null,
            'updated_at' => fake()->boolean(20) ? fake()->dateTimeBetween($createdAt, 'now') : $createdAt,
        ];
    }
}