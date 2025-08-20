<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserExperience;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class UserExperienceController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $experiences = UserExperience::where('user_id', auth()->id())->get();
        return $this->successResponse($experiences, 'Experience list fetched successfully');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title'       => 'required|string|max:255',
                'from_date'   => 'required|date|before_or_equal:today',
                'to_date'     => 'nullable|date|after_or_equal:from_date',
                'company'     => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_present'  => 'boolean'
            ]);

            // Condition 1: If is_present = true => to_date must be null
            if ($request->is_present && $request->to_date) {
                return $this->errorResponse('Ongoing job cannot have a to_date', 422);
            }

            // Condition 2: Only one active job at a time
            if ($request->is_present) {
                $exists = UserExperience::where('user_id', auth()->id())
                    ->where('is_present', true)
                    ->exists();

                if ($exists) {
                    return $this->errorResponse('You already have an ongoing job', 422);
                }
            }

            // Condition 3: Prevent duplicate same company + same dates
            $duplicate = UserExperience::where('user_id', auth()->id())
                ->where('company', $request->company)
                ->where('from_date', $request->from_date)
                ->where('to_date', $request->to_date)
                ->exists();

            if ($duplicate) {
                return $this->errorResponse('Duplicate record: Same experience already exists', 422);
            }

            $experience = UserExperience::create([
                'user_id'     => auth()->id(),
                'title'       => $request->title,
                'from_date'   => $request->from_date,
                'to_date'     => $request->to_date,
                'company'     => $request->company,
                'description' => $request->description,
                'is_present'  => $request->is_present ?? false,
            ]);

            return $this->successResponse($experience, 'Experience created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create experience', 500, $e->getMessage());
        }
    }

    public function show($id)
    {
        $experience = UserExperience::where('user_id', auth()->id())->find($id);

        if (!$experience) {
            return $this->errorResponse('Experience not found', 404);
        }

        return $this->successResponse($experience, 'Experience details fetched successfully');
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title'       => 'required|string|max:255',
                'from_date'   => 'required|date|before_or_equal:today',
                'to_date'     => 'nullable|date|after_or_equal:from_date',
                'company'     => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_present'  => 'boolean'
            ]);

            $experience = UserExperience::where('user_id', auth()->id())->find($id);

            if (!$experience) {
                return $this->errorResponse('Experience not found', 404);
            }

            // Condition 1: If is_present = true => to_date must be null
            if ($request->is_present && $request->to_date) {
                return $this->errorResponse('Ongoing job cannot have a to_date', 422);
            }

            // Condition 2: Only one active job (excluding current record)
            if ($request->is_present) {
                $exists = UserExperience::where('user_id', auth()->id())
                    ->where('is_present', true)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($exists) {
                    return $this->errorResponse('You already have another ongoing job', 422);
                }
            }

            // Condition 3: Prevent duplicate same company + same dates (excluding current record)
            $duplicate = UserExperience::where('user_id', auth()->id())
                ->where('company', $request->company)
                ->where('from_date', $request->from_date)
                ->where('to_date', $request->to_date)
                ->where('id', '!=', $id)
                ->exists();

            if ($duplicate) {
                return $this->errorResponse('Duplicate record: Same experience already exists', 422);
            }

            $experience->update($request->only('title','from_date','to_date','company','description','is_present'));

            return $this->successResponse($experience, 'Experience updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update experience', 500, $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $experience = UserExperience::where('user_id', auth()->id())->find($id);

        if (!$experience) {
            return $this->errorResponse('Experience not found', 404);
        }

        $experience->delete();
        return $this->successResponse([], 'Experience deleted successfully', 200);
    }
}
