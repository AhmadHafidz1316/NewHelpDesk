<?php

namespace App\Http\Controllers;

use App\Models\Subdepartment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Tickets\StoreTicketRequest;
use App\Models\Department;

class SubdepartmentController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|string|exists:departments,name',
        ]);

        Log::info('Request Data:', $validatedData);

        $department = Department::where('name', $validatedData['department_id'])->firstOrFail();
        $departmentId = $department->id;

        $subdepartment = Subdepartment::create([
            'name' => $validatedData['name'],
            'department_id' => $departmentId,
        ]);
    }
    public function get()
    {

        $department = Subdepartment::all();
        return $this->successResponse('Subdepartment get successfully', $department);
    }
    public function getcount()
    {
        $subdepartments = Subdepartment::with('department')->get();

        $count = [];

        $grouped = $subdepartments->groupBy('department.id');

        foreach ($grouped as $departmentId => $subdepartmentsInDepartment) {
            $departmentName = $subdepartmentsInDepartment->first()->department->name;
            $count[] = [
                'department_id' => $departmentId,
                'department_name' => $departmentName,
                'subdepartment_count' => $subdepartmentsInDepartment->count()
            ];
        }

        return $this->successResponse('Subdepartments retrieved successfully', $count);
    }



}
