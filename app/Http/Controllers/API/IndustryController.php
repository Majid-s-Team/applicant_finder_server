<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Industry;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;

class IndustryController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $industries = Industry::all();
        return $this->successResponse($industries, 'Industry list retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:industries,name',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $industry = Industry::create([
            'name' => $request->name,
        ]);

        return $this->successResponse($industry, 'Industry created successfully.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $industry = Industry::find($id);

        if (!$industry) {
            return $this->errorResponse('Industry not found', 404);
        }

        return $this->successResponse($industry, 'Industry details retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $industry = Industry::find($id);

        if (!$industry) {
            return $this->errorResponse('Industry not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:industries,name,' . $id,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $industry->update([
            'name' => $request->name,
        ]);

        return $this->successResponse($industry, 'Industry updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $industry = Industry::find($id);

        if (!$industry) {
            return $this->errorResponse('Industry not found', 404);
        }

        $industry->delete();

        return $this->successResponse([], 'Industry deleted successfully.');
    }
}
