<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManualApplyRequest extends FormRequest
{
    public function authorize()
    {
        // sirf logged-in users allow karo (agar chaho condition bhi laga sakte ho)
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|min:2|max:50',
            'last_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|min:2|max:50',
            'email' => 'required|email|max:100',
            'phone_number' => 'required|string|regex:/^[0-9\-\+\s\(\)]+$/|min:7|max:15',
            'current_job_title' => 'nullable|string|max:100',
            'current_job_salary' => 'nullable|numeric|min:0|max:10000000',
            'message' => 'nullable|string|max:1000',
            'resume_link' => 'required|url|ends_with:.pdf,.doc,.docx|max:255',
        ];
    }

    public function messages()
    {
        return [
            'first_name.regex' => 'First name may only contain letters and spaces.',
            'last_name.regex' => 'Last name may only contain letters and spaces.',
            'phone_number.regex' => 'Phone number must be valid (digits, spaces, +, - allowed).',
            'resume_link.ends_with' => 'Resume must be a PDF or Word document.',
        ];
    }
}
