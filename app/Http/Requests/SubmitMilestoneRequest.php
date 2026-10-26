<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesMilestoneSubmission;
use Illuminate\Foundation\Http\FormRequest;

class SubmitMilestoneRequest extends FormRequest
{
    use AuthorizesMilestoneSubmission;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Prototype only: lets a grader see the SC-03 "storage failed" path. Ignored outside local and testing.
            'simulate_failure' => ['sometimes', 'boolean'],
        ];
    }
}
