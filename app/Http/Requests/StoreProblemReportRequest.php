<?php

namespace App\Http\Requests;

use App\Models\ProblemReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProblemReportRequest extends FormRequest
{
    /**
     * Anyone can report a problem, signed in or not.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150'],
            'category' => ['required', 'string', Rule::in(ProblemReport::CATEGORIES)],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Enter your name.',
            'name.max' => 'Use 100 characters or fewer for your name.',
            'email.required' => 'Enter your email so we can reply.',
            'email.email' => 'Enter a valid email address, like name@university.edu.',
            'email.max' => 'Use an email address of 150 characters or fewer.',
            'category.required' => 'Choose what the problem is about.',
            'category.in' => 'Choose one of the listed topics.',
            'description.required' => 'Describe the problem in at least 20 characters.',
            'description.min' => 'Describe the problem in at least 20 characters.',
            'description.max' => 'Keep the description to 2,000 characters or fewer.',
        ];
    }
}
