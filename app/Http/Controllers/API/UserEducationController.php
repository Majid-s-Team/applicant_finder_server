<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserEducation;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class UserEducationController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $education = UserEducation::where('user_id', auth()->id())->get();
        return $this->successResponse($education, 'Education list fetched successfully');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title'       => 'required|string|max:255',
                'institute'   => 'required|string|max:255',
                'start_date'  => 'required|date',
                'end_date'    => 'nullable|date|after_or_equal:start_date',
                'year'        => 'nullable|digits:4',
                'description' => 'nullable|string'
            ]);

            // Condition 1: Duplicate check (same institute + same start & end date)
            $exists = UserEducation::where('user_id', auth()->id())
                ->where('institute', $request->institute)
                ->where('start_date', $request->start_date)
                ->where('end_date', $request->end_date)
                ->exists();

            if ($exists) {
                return $this->errorResponse('Duplicate record: Same education period already exists', 422);
            }

            // Condition 2: Year validation with start/end_date
            if ($request->year) {
                $startYear = date('Y', strtotime($request->start_date));
                $endYear   = $request->end_date ? date('Y', strtotime($request->end_date)) : null;

                if ($request->year != $startYear && ($endYear && $request->year != $endYear)) {
                    return $this->errorResponse('Year must match either start_date or end_date year', 422);
                }
            }

            $education = UserEducation::create([
                'user_id'     => auth()->id(),
                'title'       => $request->title,
                'year'        => $request->year,
                'institute'   => $request->institute,
                'start_date'  => $request->start_date,
                'end_date'    => $request->end_date,
                'description' => $request->description
            ]);

            return $this->successResponse($education, 'Education created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create education', 500, $e->getMessage());
        }
    }

    public function show($id)
    {
        $education = UserEducation::where('user_id', auth()->id())->find($id);
        if (!$education) {
            return $this->errorResponse('Education not found', 404);
        }
        return $this->successResponse($education, 'Education details fetched');
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title'       => 'required|string|max:255',
                'institute'   => 'required|string|max:255',
                'start_date'  => 'required|date',
                'end_date'    => 'nullable|date|after_or_equal:start_date',
                'year'        => 'nullable|digits:4',
                'description' => 'nullable|string'
            ]);

            $education = UserEducation::where('user_id', auth()->id())->find($id);
            if (!$education) {
                return $this->errorResponse('Education not found', 404);
            }

            // Condition 1: Prevent duplicate (excluding current record)
            $exists = UserEducation::where('user_id', auth()->id())
                ->where('institute', $request->institute)
                ->where('start_date', $request->start_date)
                ->where('end_date', $request->end_date)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return $this->errorResponse('Duplicate record: Same education period already exists', 422);
            }

            // Condition 2: Year validation
            if ($request->year) {
                $startYear = date('Y', strtotime($request->start_date));
                $endYear   = $request->end_date ? date('Y', strtotime($request->end_date)) : null;

                if ($request->year != $startYear && ($endYear && $request->year != $endYear)) {
                    return $this->errorResponse('Year must match either start_date or end_date year', 422);
                }
            }

            $education->update($request->only('title', 'year', 'institute', 'start_date', 'end_date', 'description'));

            return $this->successResponse($education, 'Education updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update education', 500, $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $education = UserEducation::where('user_id', auth()->id())->find($id);
        if (!$education) {
            return $this->errorResponse('Education not found', 404);
        }

        $education->delete();
        return $this->successResponse([], 'Education deleted successfully', 200);
    }
}
