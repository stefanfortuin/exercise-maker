<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Workout;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WorkoutTest extends TestCase
{
    use RefreshDatabase;

    public function testWorkoutCanBeCreated()
    {
        $this->post('/workouts', $this->workoutData());

        $this->assertCount(1, Workout::all());
    }

    public function testWorkoutRequiresAName()
    {
        $response = $this->post('/workouts', array_merge($this->workoutData(), ['name' => '']));
        
        $response->assertSessionHasErrors('name');
        $this->assertCount(0, Workout::all());
    }

    public function testWorkoutDescriptionIsNotRequired()
    {
        $this->post('/workouts', array_merge($this->workoutData(), ['description' => '']));

        $this->assertCount(1, Workout::all());
    }

    private function workoutData()
    {
        return [
            'name' => 'new workout',
            'description' => 'description workout',
        ];
    }
}
