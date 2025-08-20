<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\UploadMediaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait, UploadMediaTrait;

    /**
     * Signup (Employee / Candidate)
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required_without:phone_number|email|unique:users,email',
            'phone_number' => 'required_without:email|unique:users,phone_number',
            'password' => 'required|confirmed|min:6',
            'accept_terms' => 'required|boolean|in:1',
            'role' => 'required|in:employer,candidate'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'accept_terms' => $request->accept_terms,
            'role' => $request->role
        ]);

        $user->assignRole($request->role);

        return $this->successResponse($user, 'Signup successful', 201);
    }

    /**
     * Login by Email or Phone
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'phone_number', 'password');

        if (isset($credentials['email']) && Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $user = Auth::user();
        } elseif (isset($credentials['phone_number']) && Auth::attempt(['phone_number' => $credentials['phone_number'], 'password' => $credentials['password']])) {
            $user = Auth::user();
        } else {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => $user
        ], 'Login successful');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Send OTP for Forgot Password
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone_number|email|exists:users,email',
            'phone_number' => 'required_without:email|exists:users,phone_number'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $user = User::where('email', $request->email)
            ->orWhere('phone_number', $request->phone_number)
            ->first();

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        return $this->successResponse(['otp' => $otp], 'OTP sent successfully');
    }

    /**
     * Verify OTP and Reset Password
     */
    public function verifyOtpAndReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|numeric',
            'password' => 'required|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $user = User::where('otp', $request->otp)
            ->where('otp_expires_at', '>', Carbon::now())
            ->first();

        if (!$user) {
            return $this->errorResponse('Invalid or expired OTP', 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'otp' => null,
            'otp_expires_at' => null
        ]);

        return $this->successResponse([], 'Password reset successful');
    }

    /**
     * Change Password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        if (!Hash::check($request->old_password, $request->user()->password)) {
            return $this->errorResponse('Old password incorrect', 400);
        }

        $request->user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->successResponse([], 'Password changed successfully');
    }

    /**
     * Get Profile
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load('profile','profile.industry');
        return $this->successResponse($user, 'Profile fetched successfully');
    }
    /**
     * Upload profile related media (image/video/cv)
     */
    public function uploadMediaPublic(Request $request)
    {
        $fileKey = $request->input('file_key');

        if (!$fileKey) {
            return $this->errorResponse('file_key is required', 400);
        }

        $result = $this->uploadMedia($request, $fileKey);

        if ($result['error']) {
            return $this->errorResponse(
                $result['message'] ?? 'Validation error',
                422,
                $result['errors'] ?? []
            );
        }

        return $this->successResponse(
            ['url' => $result['url']],
            'File uploaded successfully',
            200
        );
    }

    /**
     * Update Profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|unique:users,phone_number,' . $user->id,
            'website' => 'nullable|url',
            'founded_date' => 'nullable|date',
            'sector' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'profile_image' => 'nullable|string',
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|date',
            'public_private_profile' => 'nullable|in:public,private',
            'profile_url' => 'nullable|string|unique:user_profiles,profile_url,' . optional($user->profile)->id,
            'job_title' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric',
            'industry_id' => 'nullable|exists:industries,id',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $user->update($request->only([
            'first_name',
            'last_name',
            'email',
            'phone_number'
        ]));

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'website',
                'founded_date',
                'sector',
                'address',
                'gender',
                'dob',
                'public_private_profile',
                'profile_url',
                'job_title',
                'salary',
                'industry_id',
                'description'
            ])
        );

        return $this->successResponse($user, 'Profile updated successfully');
    }
}
