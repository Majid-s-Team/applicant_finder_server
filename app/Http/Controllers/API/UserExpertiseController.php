<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserExpertise;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class UserExpertiseController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all expertises of the authenticated user
     */
    public function index()
    {
        $expertises = UserExpertise::where('user_id', auth()->id())->get();
        return $this->successResponse($expertises, 'Expertise list fetched successfully');
    }

    /**
     * Store a new expertise
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'label'      => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\s\-\&]+$/u' // Only letters, numbers, spaces, - and &
                ],
                'percentage' => 'required|integer|min:1|max:100'
            ]);

            $userId = auth()->id();

            // Condition 1: Max 50 expertises
            $count = UserExpertise::where('user_id', $userId)->count();
            if ($count >= 50) {
                return $this->errorResponse('You cannot add more than 50 expertises', 422);
            }

            // Condition 2: Duplicate expertise not allowed
            $exists = UserExpertise::where('user_id', $userId)
                ->whereRaw('LOWER(label) = ?', [strtolower($request->label)])
                ->exists();

            if ($exists) {
                return $this->errorResponse('This expertise already exists', 422);
            }

            // Condition 3: Total percentage should not exceed 100
            $currentTotal = UserExpertise::where('user_id', $userId)->sum('percentage');
            if (($currentTotal + $request->percentage) > 100) {
                return $this->errorResponse('Total expertise percentage cannot exceed 100', 422);
            }

            $expertise = UserExpertise::create([
                'user_id'    => $userId,
                'label'      => $request->label,
                'percentage' => $request->percentage
            ]);

            return $this->successResponse($expertise, 'Expertise created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create expertise', 500, [$e->getMessage()]);
        }
    }

    /**
     * Show a single expertise
     */
    public function show($id)
    {
        $expertise = UserExpertise::where('user_id', auth()->id())->find($id);

        if (!$expertise) {
            return $this->errorResponse('Expertise not found', 404);
        }

        return $this->successResponse($expertise, 'Expertise details fetched successfully');
    }

    /**
     * Update an expertise
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'label'      => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\s\-\&]+$/u'
                ],
                'percentage' => 'required|integer|min:1|max:100'
            ]);

            $userId = auth()->id();
            $expertise = UserExpertise::where('user_id', $userId)->find($id);

            if (!$expertise) {
                return $this->errorResponse('Expertise not found', 404);
            }

            // Condition 1: Prevent duplicate labels except for the current record
            $exists = UserExpertise::where('user_id', $userId)
                ->whereRaw('LOWER(label) = ?', [strtolower($request->label)])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return $this->errorResponse('This expertise already exists', 422);
            }

            // Condition 2: Check total percentage
            $currentTotal = UserExpertise::where('user_id', $userId)
                ->where('id', '!=', $id)
                ->sum('percentage');

            if (($currentTotal + $request->percentage) > 100) {
                return $this->errorResponse('Total expertise percentage cannot exceed 100', 422);
            }

            $expertise->update($request->only('label', 'percentage'));

            return $this->successResponse($expertise, 'Expertise updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update expertise', 500, [$e->getMessage()]);
        }
    }

    /**
     * Delete an expertise
     */
    public function destroy($id)
    {
        $expertise = UserExpertise::where('user_id', auth()->id())->find($id);

        if (!$expertise) {
            return $this->errorResponse('Expertise not found', 404);
        }

        $expertise->delete();
        return $this->successResponse([], 'Expertise deleted successfully');
    }
}
