<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'latitude' => ['required'],
            'longitude' => ['required'],
        ];
    }
}