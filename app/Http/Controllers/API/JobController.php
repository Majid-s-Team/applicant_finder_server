<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Http\Controllers\API\NotificationController;

class JobController extends Controller
{
    use ApiResponseTrait;

    public function store(StoreJobRequest $request)
    {
        $validated = $request->validated();
        $validated['employer_id'] = Auth::id();
        $job = Job::create($validated);

        if ($job->status === 'active') {
                                    // dd('Notification sent to candidate');

            $allUsers = User::where('role', 'candidate')->pluck('id')->toArray();
            foreach ($allUsers as $userId) {
                app(NotificationController::class)->sendNotification(
                    $userId,
                    Auth::id(),
                    'new_job',
                    [
                        'message' => "A new job '{$job->title}' has been posted.",
                        'job_id' => $job->id
                    ]
                );
            }

        }

        return $this->successResponse($job, 'Job created successfully', 201);
    }

    /**
     * Employer ki apni jobs (Auth wise)
     */
    public function myJobs(Request $request)
    {
        $jobs = Job::where('employer_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->successResponse($jobs, 'Your jobs fetched successfully');
    }


    // Single Job
    public function show($id)
    {
        // $job = Job::findOrFail($id);
        $job = Job::with('employer')->findOrFail($id);
        $job->increment('view_count');
        return $this->successResponse($job, 'Job details fetched successfully');
    }

    // Update Job
    public function update(UpdateJobRequest $request, $id)
    {
        $job = Job::where('id', $id)->where('employer_id', Auth::id())->firstOrFail();
        $job->update($request->validated());

        return $this->successResponse($job, 'Job updated successfully');
    }

    // Delete Job
    public function destroy($id)
    {
        $job = Job::where('id', $id)->where('employer_id', Auth::id())->firstOrFail();
        $job->delete();

        return $this->successResponse([], 'Job deleted successfully');
    }

    // Change Status (draft/active/closed)
    public function changeStatus(Request $request, $id)
    {
        $job = Job::where('id', $id)->where('employer_id', Auth::id())->firstOrFail();
        $job->status = $request->status;
        $job->save();
        $candidates = $job->applications()->pluck('user_id')->toArray();
        foreach ($candidates as $candidateId) {
            app(NotificationController::class)->sendNotification(
                $candidateId,
                Auth::id(),
                'job_status_update',
                [
                    'message' => "The status of job '{$job->title}' has been changed to '{$job->status}'",
                    'job_id' => $job->id
                ]
            );
        }

        return $this->successResponse($job, 'Job status updated successfully');
    }

    public function index(Request $request)
    {
        $query = Job::with('employer')->where('status', 'active');

        if ($request->industry_id) {
            $query->where('industry_id', $request->industry_id);
        }
        if ($request->job_type) {
            $query->where('job_type', $request->job_type);
        }
        if ($request->salary_min && $request->salary_max) {
            $query->whereBetween('salary_range', [$request->salary_min, $request->salary_max]);
        }
        if ($request->sort == 'new') {
            $query->orderBy('created_at', 'desc');
        }

        $jobs = $query->paginate(10);

        $user = Auth::user();
        if ($user) {
            $jobs->getCollection()->transform(function ($job) use ($user) {
                $job->increment('view_count');

                $job->is_applied = $job->applications()
                    ->where('user_id', $user->id)
                    ->exists();
                return $job;
            });
        }

        return $this->successResponse($jobs, 'All active jobs fetched successfully');
    }

    public function personalized(Request $request)
    {
        $query = Job::query()->where('status', 'active');

        if ($request->industry_id) {
            $query->where('industry_id', $request->industry_id);
        }
        if ($request->job_type) {
            $query->where('job_type', $request->job_type);
        }
        if ($request->salary_min && $request->salary_max) {
            $query->whereBetween('salary_range', [$request->salary_min, $request->salary_max]);
        }
        if ($request->sort == 'new') {
            $query->orderBy('created_at', 'desc');
        }

        $user = Auth::user();
        if ($user) {
            $query->where(function ($q) use ($user) {
                // 1. Match Industry
                if ($user->profile && $user->profile->industry_id) {
                    $q->orWhere('industry_id', $user->profile->industry_id);
                }

                // 2. Match Skills
                if ($user->skills && $user->skills->count() > 0) {
                    $userSkills = $user->skills->pluck('name')->toArray();
                    foreach ($userSkills as $skill) {
                        $q->orWhereJsonContains('required_skills', $skill);
                    }
                }

                // 3. Match Experience Levels
                if ($user->experiences && $user->experiences->count() > 0) {
                    $experienceLevels = $user->experiences->pluck('experience_level')->toArray();
                    $q->orWhereIn('experience', $experienceLevels);
                }

                // 4. Match Education
                if ($user->educations && $user->educations->count() > 0) {
                    $degrees = $user->educations->pluck('title')->toArray();
                    foreach ($degrees as $degree) {
                        $q->orWhere('required_education', 'LIKE', "%$degree%");
                    }
                }
            });
        }

        $jobs = $query->paginate(10);

        if ($user) {
            $jobs->getCollection()->transform(function ($job) use ($user) {
                $job->is_applied = $job->applications()
                    ->where('user_id', $user->id)
                    ->exists();
                return $job;
            });
        }

        return $this->successResponse($jobs, 'Personalized jobs fetched successfully');
    }




}
