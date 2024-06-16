<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PriorityUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'priority_name' => 'required|string|max:255',
            'response_time' => 'required|integer|min:1',
            'resolution_time' => 'required|integer|min:1',
        ];
    }
}
