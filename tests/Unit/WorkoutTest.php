<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Workout;
use Tests\TestCase;

class WorkoutTest extends TestCase
{
    // public function testCheckIfWorkoutIsFromUser()
	// {
	// 	$user = User::factory()->create();

	// 	$workout = $user->workouts()->create($this->data());

	// 	$this->assertCount(1, $user->workouts);
	// 	$this->assertTrue($workout->owned())
	// }

	private function data()
	{
		return [
			'title' => 'new title',
			'description' => 'description of the workout',
		];
	}
}
