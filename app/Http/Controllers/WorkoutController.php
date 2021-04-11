<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Http\Requests\WorkoutRequest;

class WorkoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'create', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WorkoutRequest $request)
    {
        $validated = $request->validated();

        $this->authorize('create', Workout::class);

		$workout = $request->user()->workouts()->create($validated);

		return redirect('/workouts/' . $workout->id);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Workout $workout)
    {
        return view('workout.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Workout $workout)
    {
        $this->authorize('update', $workout);

        return view('workout.edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(WorkoutRequest $request, Workout $workout)
    {
        $validated = $request->validated();

        $this->authorize('update', $workout);

        $workout->update($validated);

        return redirect('/workouts/' . $workout->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Workout $workout)
    {
        $this->authorize('delete', $workout);

        $workout->delete();

        return redirect('/workouts');
    }
}
