<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WorkoutTest extends TestCase
{
    use RefreshDatabase;

	public function testWorkoutCanOnlyBeCreatedByAnAuthenticatedUser()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)->post('/workouts', $this->workoutData());

		$workout = Workout::first();

		$this->assertCount(1, Workout::all());
		$this->assertEquals($user->id, $workout->user_id);
		$response->assertRedirect('/workouts/' . $workout->id);
	}

	public function testWorkoutCanNotBeCreatedByGuest()
	{
		$response = $this->post('/workouts', $this->workoutData());

		$response->assertStatus(403);
		$this->assertCount(0, Workout::all());

	}

    public function testWorkoutCanBeCreated()
    {
		$user = User::factory()->create();
        $this->actingAs($user)->post('/workouts', $this->workoutData());

        $this->assertCount(1, Workout::all());
    }

    public function testWorkoutRequiresAName()
    {
		$user = User::factory()->create();
        $response = $this->actingAs($user)->post('/workouts', array_merge($this->workoutData(), ['name' => '']));
        
        $response->assertSessionHasErrors('name');
        $this->assertCount(0, Workout::all());
    }

    public function testWorkoutDescriptionIsNotRequired()
    {
		$user = User::factory()->create();
        $this->actingAs($user)->post('/workouts', array_merge($this->workoutData(), ['description' => '']));

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
