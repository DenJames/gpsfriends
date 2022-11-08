<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationFormRequest;

use App\Http\Resources\UserResource;
use App\Models\User;


class LocationController extends Controller
{
    public function store(LocationFormRequest $request)
    {
        $user = $request->user();

        if ($user->latitude != $request->input('latitude') || $user->longitude != $request->input('longitude')) {
            $user->update($request->validated());
        }

        return response(UserResource::collection(User::query()->select(['id', 'name', 'latitude', 'longitude'])->get()));
    }
}