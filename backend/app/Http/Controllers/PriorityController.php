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
        $priorities = priority::all();

        return $this->successResponse('Priorities retrieved successfully', $priorities);
    }

    public function update(Request $request, $id)
    {
        $priority = priority::findOrFail($id);

        $priority->update([
            'priority_name' => $request->priority_name,
            'response_time' => $request->response_time,
            'resolution_time' => $request->resolution_time
        ]);

        return $this->successResponse('Priority updated successfully', $priority);
    }

    public function delete($id)
    {
        $priority = priority::findOrFail($id);
        $priority->delete();

        return $this->successResponse('Priority deleted successfully');
    }
}


