<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\SlaCreateRequest;
use App\Models\slamanagement as ModelsSlamanagement;

class SlaManagement extends Controller
{
    public function store(SlaCreateRequest $request): JsonResponse
    {

        $department = ModelsSlamanagement::create($request->all());

        return $this->successResponse('Department created successfully', $department);
    }


}
