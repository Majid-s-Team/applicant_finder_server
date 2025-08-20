<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSkill;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;

class UserSkillController extends Controller
{
    use ApiResponseTrait;

    protected $maxSkills = 50;

    public function index()
    {
        try {
            $skills = UserSkill::where('user_id', auth()->id())->get();
            return $this->successResponse($skills, 'Skills fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch skills', 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'skill_name' => 'required|string|max:255'
            ]);

            // check max skills limit
            $skillCount = UserSkill::where('user_id', auth()->id())->count();
            if ($skillCount >= $this->maxSkills) {
                return $this->errorResponse("You can add up to {$this->maxSkills} skills only", 400);
            }

            // check duplicate skill
            $exists = UserSkill::where('user_id', auth()->id())
                ->whereRaw('LOWER(skill_name) = ?', [strtolower($request->skill_name)])
                ->exists();

            if ($exists) {
                return $this->errorResponse('This skill is already added', 400);
            }

            $skill = UserSkill::create([
                'user_id' => auth()->id(),
                'skill_name' => $request->skill_name
            ]);

            return $this->successResponse($skill, 'Skill added successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to add skill', 500, $e->getMessage());
        }
    }

    public function show($id)
    {
        $skill = UserSkill::where('user_id', auth()->id())->find($id);
        if (!$skill) {
            return $this->errorResponse('Skill not found', 404);
        }
        return $this->successResponse($skill, 'Skill fetched successfully');
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'skill_name' => 'required|string|max:255'
            ]);

            $skill = UserSkill::where('user_id', auth()->id())->find($id);
            if (!$skill) {
                return $this->errorResponse('Skill not found', 404);
            }

            // check duplicate before update
            $exists = UserSkill::where('user_id', auth()->id())
                ->whereRaw('LOWER(skill_name) = ?', [strtolower($request->skill_name)])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return $this->errorResponse('This skill already exists', 400);
            }

            $skill->update($request->only('skill_name'));

            return $this->successResponse($skill, 'Skill updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update skill', 500, $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $skill = UserSkill::where('user_id', auth()->id())->find($id);
            if (!$skill) {
                return $this->errorResponse('Skill not found', 404);
            }

            $skill->delete();

            return $this->successResponse([], 'Skill deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete skill', 500, $e->getMessage());
        }
    }
}
