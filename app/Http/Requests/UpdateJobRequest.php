<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|min:5|max:150|regex:/^[a-zA-Z0-9\s\-\.,&()]+$/',
            'description' => 'sometimes|required|string|min:50|max:5000',
            'applicant_deadline' => 'nullable|date|after_or_equal:today',
            'industry_id' => 'nullable|integer|exists:industries,id',
            'job_type' => 'sometimes|required|in:full_time,part_time,contract,internship,freelance',
            'required_skills' => 'nullable|array|min:1|max:20',
            'required_skills.*' => 'string|min:2|max:50',
            'salary_range' => [
                'nullable',
                'string',
                'regex:/^\d{2,6}\s*-\s*\d{2,6}\s*(USD|EUR|PKR|INR)?\s*(per\s+(month|year))?$/i'
            ],
            'career_level' => 'nullable|in:entry,mid,senior,lead,manager,director,executive',
            'experience' => 'nullable|in:fresher,1-2,3-5,5-10,10+',
            'qualification' => 'nullable|string|min:2|max:100',
            'company_name' => 'sometimes|required|string|min:2|max:150',
            'location' => 'sometimes|required|string|min:2|max:255',
            'file_attachment' => 'nullable|url|ends_with:.pdf,.doc,.docx,.png,.jpg,.jpeg'
        ];
    }
}
