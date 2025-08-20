<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [

            'title' => 'required|string|min:5|max:150|regex:/^[a-zA-Z0-9\s\-\.,&()]+$/',
            'description' => 'required|string|min:50|max:5000',
            'status' => 'required|in:active,closed,draft',


            'applicant_deadline' => 'nullable|date|after_or_equal:today',


            'industry_id' => 'nullable|integer|exists:industries,id',

            'job_type' => 'required|in:full_time,part_time,contract,internship,freelance',
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
            'company_name' => 'required|string|min:2|max:150',
            'location' => 'required|string|min:2|max:255',


            'file_attachment' => 'nullable|url|ends_with:.pdf,.doc,.docx,.png,.jpg,.jpeg'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a job title.',
            'title.regex' => 'The job title contains invalid characters.',
            'description.min' => 'Job description must be at least 50 characters.',
            'salary_range.regex' => 'Salary format must look like: 50000-70000 USD per year.',
            'file_attachment.ends_with' => 'Attachment must be a valid document or image file.',
        ];
    }
}
