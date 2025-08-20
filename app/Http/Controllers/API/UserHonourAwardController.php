<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserHonourAward;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;

class UserHonourAwardController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        try {
            $awards = UserHonourAward::where('user_id', auth()->id())->get();
            return $this->successResponse($awards, 'Honour & Awards list fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch Honour & Awards', 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'award_title' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\s\-\&]+$/u'
                ],
                'year'        => 'nullable|digits:4|integer|min:1900|max:' . Carbon::now()->year,
                'description' => 'nullable|string|max:1000'
            ]);

            $userId = auth()->id();

            // Condition 1: Max 50 awards
            $count = UserHonourAward::where('user_id', $userId)->count();
            if ($count >= 50) {
                return $this->errorResponse('You cannot add more than 50 honours & awards', 422);
            }

            // Condition 2: Duplicate award title not allowed
            $exists = UserHonourAward::where('user_id', $userId)
                ->whereRaw('LOWER(award_title) = ?', [strtolower($request->award_title)])
                ->exists();

            if ($exists) {
                return $this->errorResponse('This award already exists', 422);
            }

            $award = UserHonourAward::create([
                'user_id'     => $userId,
                'award_title' => $request->award_title,
                'year'        => $request->year,
                'description' => $request->description,
            ]);

            return $this->successResponse($award, 'Honour & Award created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create Honour & Award', 500, $e->getMessage());
        }
    }

    public function show($id)
    {
        $award = UserHonourAward::where('user_id', auth()->id())->find($id);

        if (!$award) {
            return $this->errorResponse('Honour & Award not found', 404);
        }

        return $this->successResponse($award, 'Honour & Award details fetched successfully');
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'award_title' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z0-9\s\-\&]+$/u'
                ],
                'year'        => 'nullable|digits:4|integer|min:1900|max:' . Carbon::now()->year,
                'description' => 'nullable|string|max:1000'
            ]);

            $userId = auth()->id();
            $award = UserHonourAward::where('user_id', $userId)->find($id);

            if (!$award) {
                return $this->errorResponse('Honour & Award not found', 404);
            }

            // Condition: Prevent duplicate title except same record
            $exists = UserHonourAward::where('user_id', $userId)
                ->whereRaw('LOWER(award_title) = ?', [strtolower($request->award_title)])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return $this->errorResponse('This award already exists', 422);
            }

            $award->update($request->only('award_title','year','description'));

            return $this->successResponse($award, 'Honour & Award updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update Honour & Award', 500, $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $award = UserHonourAward::where('user_id', auth()->id())->find($id);

            if (!$award) {
                return $this->errorResponse('Honour & Award not found', 404);
            }

            $award->delete();
            return $this->successResponse([], 'Honour & Award deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete Honour & Award', 500, $e->getMessage());
        }
    }
}
