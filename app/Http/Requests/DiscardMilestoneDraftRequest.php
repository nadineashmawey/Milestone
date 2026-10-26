<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesMilestoneSubmission;
use Illuminate\Foundation\Http\FormRequest;

class DiscardMilestoneDraftRequest extends FormRequest
{
    use AuthorizesMilestoneSubmission;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
