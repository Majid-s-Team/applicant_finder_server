<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserEducationController;
use App\Http\Controllers\API\UserExperienceController;
use App\Http\Controllers\API\UserExpertiseController;
use App\Http\Controllers\API\UserHonourAwardController;
use App\Http\Controllers\API\UserPortfolioController;
use App\Http\Controllers\API\UserSkillController;
use App\Http\Controllers\API\UserResumeController;
use App\Http\Controllers\API\JobController;
use App\Http\Controllers\API\JobApplicationController;
use App\Http\Controllers\API\NotificationController;


Route::prefix('auth')->group(function () {
    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('send-otp', [AuthController::class, 'sendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtpAndReset']);
    Route::post('/upload-media', [AuthController::class, 'uploadMediaPublic']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('profile/update', [AuthController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);

      Route::get('educations', [UserEducationController::class, 'index']);
    Route::post('educations', [UserEducationController::class, 'store']);
    Route::get('educations/{id}', [UserEducationController::class, 'show']);
    Route::put('educations/{id}', [UserEducationController::class, 'update']);
    Route::delete('educations/{id}', [UserEducationController::class, 'destroy']);

    //  Experience Routes
    Route::get('experiences', [UserExperienceController::class, 'index']);
    Route::post('experiences', [UserExperienceController::class, 'store']);
    Route::get('experiences/{id}', [UserExperienceController::class, 'show']);
    Route::put('experiences/{id}', [UserExperienceController::class, 'update']);
    Route::delete('experiences/{id}', [UserExperienceController::class, 'destroy']);

    //  Expertise Routes
    Route::get('expertises', [UserExpertiseController::class, 'index']);
    Route::post('expertises', [UserExpertiseController::class, 'store']);
    Route::get('expertises/{id}', [UserExpertiseController::class, 'show']);
    Route::put('expertises/{id}', [UserExpertiseController::class, 'update']);
    Route::delete('expertises/{id}', [UserExpertiseController::class, 'destroy']);

    //  Honour Award Routes
    Route::get('awards', [UserHonourAwardController::class, 'index']);
    Route::post('awards', [UserHonourAwardController::class, 'store']);
    Route::get('awards/{id}', [UserHonourAwardController::class, 'show']);
    Route::put('awards/{id}', [UserHonourAwardController::class, 'update']);
    Route::delete('awards/{id}', [UserHonourAwardController::class, 'destroy']);

    //  Portfolio Routes
    Route::get('portfolios', [UserPortfolioController::class, 'index']);
    Route::post('portfolios', [UserPortfolioController::class, 'store']);
    Route::get('portfolios/{id}', [UserPortfolioController::class, 'show']);
    Route::put('portfolios/{id}', [UserPortfolioController::class, 'update']);
    Route::delete('portfolios/{id}', [UserPortfolioController::class, 'destroy']);

    //  Skills Routes
    Route::get('skills', [UserSkillController::class, 'index']);
    Route::post('skills', [UserSkillController::class, 'store']);
    Route::get('skills/{id}', [UserSkillController::class, 'show']);
    Route::put('skills/{id}', [UserSkillController::class, 'update']);
    Route::delete('skills/{id}', [UserSkillController::class, 'destroy']);

    Route::get('/my-resume', [UserResumeController::class, 'myResume']);

    Route::post('/jobs', [JobController::class, 'store']);
    Route::get('/all-jobs', [JobController::class, 'index']);
    Route::get('/personalized-jobs', [JobController::class, 'personalized']);

    Route::get('/jobs', [JobController::class, 'myJobs']);
    Route::get('/jobs/{id}', [JobController::class, 'show']);
    Route::put('/jobs/{id}', [JobController::class, 'update']);
    Route::delete('/jobs/{id}', [JobController::class, 'destroy']);
    Route::patch('/jobs/{id}/status', [JobController::class, 'changeStatus']);

    Route::post('jobs/{id}/apply/direct', [JobApplicationController::class, 'directApply']);
    Route::post('jobs/{id}/apply/manual', [JobApplicationController::class, 'manualApply']);

    Route::get('my-applications', [JobApplicationController::class, 'myApplications']);
    Route::get('/jobs/{jobId}/applications', [JobApplicationController::class, 'jobApplications']);
    Route::patch('/job-applications/{id}/status', [JobApplicationController::class, 'updateStatus']);
    Route::post('jobs/{id}/favourite', [JobApplicationController::class, 'toggleFavourite']);
    Route::get('my-favourites', [JobApplicationController::class, 'myFavourites']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'delete']);
    Route::delete('/notifications', [NotificationController::class, 'deleteAll']);
});
