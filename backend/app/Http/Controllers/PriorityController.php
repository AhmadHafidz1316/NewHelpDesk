<?php

namespace App\Http\Controllers;

use App\Http\Requests\PriorityCreateRequest;
use App\Models\priority;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    public function store(PriorityCreateRequest $request)
    {
        priority::create([
            'priority_name' => $request->priority_name,
            'response_time' => $request->response_time,
            'resolution_time' => $request->resolution_time
        ]);

        return $this->successResponse('Priority created successfully', $request);
    }
    public function get()
    {
        $request = priority::all();

        return $this->successResponse('Priority get ', $request);
    }

}
