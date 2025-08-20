<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\NotificationController;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\ManualApplyRequest;
use App\Models\SavedCandidate;

class JobApplicationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Direct Apply (only if user profile is complete)
     */
    public function directApply(Request $request, $jobId)
    {
        $user = Auth::user();
        $job = Job::findOrFail($jobId);


        if ($user->role !== 'candidate') {
            return $this->errorResponse('Only candidates can apply directly', 403);
        }


        if (
            !$user->profile ||
            $user->skills->count() == 0 ||
            $user->educations->count() == 0 ||
            $user->experiences->count() == 0 ||
            $user->expertises->count() == 0
        ) {
            return $this->errorResponse('Please complete your profile before applying directly.', 400);
        }


        $alreadyApplied = JobApplication::where('job_id', $job->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyApplied) {
            return $this->errorResponse('You have already applied for this job.', 409);
        }

        $application = JobApplication::create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'first_name' => $user->profile->first_name ?? $user->name,
            'last_name' => $user->profile->last_name ?? '',
            'email' => $user->email,
            'phone_number' => $user->profile->phone ?? '',
            'current_job_title' => $user->profile->current_job_title ?? '',
            'current_job_salary' => $user->profile->current_salary ?? '',
            'message' => null,
            'resume_link' => $user->profile->resume_link ?? null,
            'apply_type' => 'direct',
        ]);
        app(NotificationController::class)->sendNotification(
            $job->employer_id,
            $user->id,
            'job_application',
            [
                'message' => "{$user->name} applied for your job '{$job->title}'",
                'job_id' => $job->id,
                'application_id' => $application->id
            ]
        );

        return $this->successResponse($application, 'Applied successfully via Direct Apply');
    }

    /**
     * Manual Apply (with form)
     */
    public function manualApply(ManualApplyRequest $request, $jobId)
    {
        $job = Job::findOrFail($jobId);

        // Prevent duplicate application
        $alreadyApplied = JobApplication::where('job_id', $job->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyApplied) {
            return $this->errorResponse('You have already applied for this job.', 409);
        }

        $application = JobApplication::create([
            'job_id' => $job->id,
            'user_id' => Auth::id(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'current_job_title' => $request->current_job_title,
            'current_job_salary' => $request->current_job_salary,
            'message' => $request->message,
            'resume_link' => $request->resume_link,
            'apply_type' => 'manual'
        ]);
        app(NotificationController::class)->sendNotification(
            $job->employer_id,
            Auth::id(),
            'job_application',
            [
                'message' => "{$request->first_name} applied for your job '{$job->title}'",
                'job_id' => $job->id,
                'application_id' => $application->id
            ]
        );

        return $this->successResponse($application, 'Applied successfully ');
    }

    /**
     * Get all my applied jobs
     */
    public function myApplications()
    {
        $applications = JobApplication::with('job', 'job.employer')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        $applications->getCollection()->transform(function ($application) {
            $application->job_title = $application->job->title ?? null;
            $application->employer_name = $application->job->employer->name ?? null;
            $application->view_count = $application->job->view_count ?? 0;
            $application->status = $application->status ?? 'none';
            return $application;
        });

        return $this->successResponse($applications, 'Your applied jobs fetched successfully');
    }

    /**
     * Add/Remove Favourite Job
     */
    public function toggleFavourite($jobId)
    {
        $user = Auth::user();
        $job = Job::findOrFail($jobId);

        if ($user->favouriteJobs()->where('job_id', $job->id)->exists()) {
            $user->favouriteJobs()->detach($job->id);
            return $this->successResponse([], 'Removed from favourites');
        } else {
            $user->favouriteJobs()->attach($job->id);
            return $this->successResponse([], 'Added to favourites');
        }
    }

    /**
     * Get all my favourite jobs
     */
    public function myFavourites()
    {
        $jobs = Auth::user()->favouriteJobs()->with('employer')->latest()->paginate(10);

        return $this->successResponse($jobs, 'Your favourite jobs fetched successfully');
    }


    public function jobApplications($jobId)
    {
        $user = auth()->user();


        $job = Job::where('id', $jobId)->where('employer_id', $user->id)->first();

        if (!$job) {
            return $this->errorResponse('Job not found or you are not the owner of this job', 404);
        }


        $applications = JobApplication::with([
            'user',
            'user.profile',
            'user.educations',
            'user.experiences',
            'user.skills',
            'user.expertises',
            'job'
        ])
            ->where('job_id', $job->id)
            ->get();

        $response = [
            'job_id' => $job->id,
            'job_title' => $job->title,
            'total_applications' => $applications->count(),
            'applications' => $applications
        ];

        return $this->successResponse($response, 'Job applications retrieved successfully');
    }
    /**
     * Update application status (Employer Only)
     */

    public function updateStatus(Request $request, $applicationId)
    {
        $user = Auth::user();

        if ($user->role !== 'employer') {
            return $this->errorResponse('Only employers can update application status', 403);
        }

        $request->validate([
            'status' => 'required|in:saved,reviewed,shortlisted,selected,none',
        ]);

        $application = JobApplication::with('job')
            ->where('id', $applicationId)
            ->first();

        if (!$application) {
            return $this->errorResponse('Application not found', 404);
        }


        if ($application->job->employer_id !== $user->id) {
            return $this->errorResponse('You are not authorized to update this application', 403);
        }


        $application->status = $request->status;
        $application->save();


        if ($request->status === 'saved') {
            SavedCandidate::firstOrCreate([
                'employer_id' => $user->id,
                'candidate_id' => $application->user_id,
                'job_id' => $application->job_id,
            ]);
        }
        if ($request->status !== 'saved') {
            app(NotificationController::class)->sendNotification(
                $application->user_id, 
                $user->id,             
                'application_status',
                [
                    'message' => "Your application for '{$application->job->title}' has been {$request->status}",
                    'job_id' => $application->job_id,
                    'application_id' => $application->id
                ]
            );
        }

        return $this->successResponse($application, 'Application status updated successfully');
    }


}
