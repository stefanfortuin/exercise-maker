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

		$this->assertCount(1, Workout::all());

        $workout = Workout::first();

		$this->assertEquals($user->id, $workout->user_id);
		$response->assertRedirect('/workouts/' . $workout->id);
	}

	public function testWorkoutCanNotBeCreatedByGuest()
	{
		$response = $this->post('/workouts', $this->workoutData());

		$response->assertForbidden();
        $this->assertGuest();
		$this->assertCount(0, Workout::all());

	}

    public function testWorkoutCanBeUpdated()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/workouts', $this->workoutData());

        $workout = Workout::first();

        $response = $this->actingAs($user)->patch('/workouts/' . $workout->id, [
            'name' => 'new title',
            'description' => 'new description',
        ]);

        $workout = Workout::first();

        $this->assertEquals('new title', $workout->name);
        $this->assertEquals('new description', $workout->description);
        
        $response->assertRedirect('/workouts/'. $workout->id);
    }

    public function testWorkoutCanOnlyBeUpdatedByOwner()
    {
        $user_owner = User::factory()->create();
        $user_other = User::factory()->create();

        $this->actingAs($user_owner)->post('/workouts', $this->workoutData());

        $this->assertCount(1, Workout::all());

        $workout = Workout::first();

        $response = $this->actingAs($user_other)->patch('/workouts/' . $workout->id, [
            'name' => 'new title',
            'description' => 'new description',
        ]);

        $response->assertForbidden();
        $this->assertEquals('new workout', Workout::first()->name);
        $this->assertEquals('description workout', Workout::first()->description);

    }

    public function testWorkoutCanBeDeleted()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/workouts', $this->workoutData());

        $workout = Workout::first();
        $this->assertCount(1, Workout::all());

        $response = $this->actingAs($user)->delete('/workouts/' . $workout->id);

        $this->assertCount(0, Workout::all());
        $response->assertRedirect('/workouts');
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
