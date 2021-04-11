<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Support\Facades\Auth;
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

    public function testOnlySavedWorkoutsCanBeShown()
    {
        $response = $this->get('/workouts/123');

        $response->assertNotFound();
    }

	public function testWorkoutCanNotBeCreatedByGuest()
	{
		$response = $this->post('/workouts', $this->workoutData());

		$response->assertRedirect('/login');
        $this->assertGuest();
		$this->assertCount(0, Workout::all());

	}

    public function testWorkoutCanBeUpdated()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/workouts', $this->workoutData());

        $workout = Workout::first();

        $response = $this->actingAs($user)->patch('/workouts/' . $workout->id, [
            'title' => 'new title',
            'description' => 'new description',
        ]);

        $workout = Workout::first();

        $this->assertEquals('new title', $workout->title);
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

        Auth::logout();

        $response = $this->actingAs($user_other)->patch('/workouts/' . $workout->id, [
            'title' => 'new title',
            'description' => 'new description',
        ]);

        $response->assertForbidden();
        $this->assertEquals('new workout', Workout::first()->title);
        $this->assertEquals('description workout', Workout::first()->description);

    }

    public function testWorkoutCanBeDeleted()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/workouts', $this->workoutData());

        $workout = Workout::first();
        $this->assertCount(1, Workout::all());

        $response = $this->actingAs($user)->delete('/workouts/' . $workout->id);

        $this->assertCount(0, Workout::all());
        $response->assertRedirect('/workouts');
    }

    public function testWorkoutCanOnlyBeDeletedByOwner()
    {
        $user_owner = User::factory()->create();
        $user_other = User::factory()->create();

        $this->actingAs($user_owner)->post('/workouts', $this->workoutData());

        $workout = Workout::first();
        $this->assertCount(1, Workout::all());

        Auth::logout();

        $response = $this->actingAs($user_other)->delete('/workouts/' . $workout->id);

        $this->assertCount(1, Workout::all());
        $response->assertForbidden();
    }

    public function testWorkoutCanOnlyBeEditedByOwner()
    {
        $user_owner = User::factory()->create();
        $user_other = User::factory()->create();

        $this->actingAs($user_owner)->post('/workouts', $this->workoutData());
        
        $workout = Workout::first();

        $response = $this->actingAs($user_owner)->get('/workouts/' . $workout->id .'/edit');
        $response->assertViewIs('workout.edit');

        Auth::logout();

        $response = $this->actingAs($user_other)->get('/workouts/' . $workout->id .'/edit');

        $response->assertForbidden();
    }

    public function testWorkoutRequiresAName()
    {
		$user = User::factory()->create();
        $response = $this->actingAs($user)->post('/workouts', array_merge($this->workoutData(), ['title' => '']));
        
        $response->assertSessionHasErrors('title');
        $this->assertCount(0, Workout::all());
    }

    public function testWorkoutDescriptionIsNotRequired()
    {
		$user = User::factory()->create();
        $this->actingAs($user)->post('/workouts', array_merge($this->workoutData(), ['description' => '']));

        $this->assertCount(1, Workout::all());
    }

	public function testCheckIfWorkoutIsFromUser()
	{
		 $user = User::factory()->create();
		 $guest = User::factory()->create();

        $this->actingAs($user)->post('/workouts', $this->workoutData());

		$workout = Workout::first();

		$this->assertTrue($workout->mine());

		Auth::logout();

		$this->assertFalse($workout->mine());

		$this->actingAs($guest);

		$this->assertFalse($workout->mine());
	}

    private function workoutData()
    {
        return [
            'title' => 'new workout',
            'description' => 'description workout',
        ];
    }
}
