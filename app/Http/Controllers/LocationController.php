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

        return $this->fetchUsers();
    }

    public function fetchUsers()
    {
        return response(UserResource::collection(
            User::query()
                ->select(['id', 'name', 'latitude', 'longitude'])
                ->where('connected', '=', true)
                ->orderByRaw('SQRT(POW(69.1 * (latitude - ?), 2) + POW(69.1 * (? - longitude) * COS(latitude / 57.3), 2))', [auth()->user()->latitude, auth()->user()->longitude])
                ->get()
        ));
    }
}