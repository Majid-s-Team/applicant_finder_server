<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class UserResumeController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get full user resume details
     */
    public function myResume(Request $request)
    {
        $user = Auth::user(); 

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $resume = User::with([
            'profile.industry',
            'skills',
            'educations',
            'experiences',
            'academics',
            'expertises',
            'honoursAwards',
            'portfolios',
        ])->find($user->id);

        return $this->successResponse($resume, 'User resume fetched successfully');
    }
}
