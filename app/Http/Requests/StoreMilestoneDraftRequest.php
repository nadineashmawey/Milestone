<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesMilestoneSubmission;
use App\Support\ZinaDemoData;
use Illuminate\Foundation\Http\FormRequest;

class StoreMilestoneDraftRequest extends FormRequest
{
    use AuthorizesMilestoneSubmission;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $file = ['required', 'file', 'extensions:'.implode(',', ZinaDemoData::DELIVERABLE_EXTENSIONS), 'max:'.ZinaDemoData::MAX_UPLOAD_KB];

        return [
            'design_document' => [...$file],
            'presentation' => [...$file],
            'repository' => ['required', 'string', 'max:255', 'url:https', 'regex:#^https://github\.com/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+/?$#'],
            'checklist' => ['required', 'array'],
            'checklist.final' => ['accepted'],
            'checklist.access' => ['accepted'],
            'checklist.approved' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'design_document.required' => 'Attach the design document.',
            'presentation.required' => 'Attach the presentation.',
            '*.file' => 'That upload didn’t arrive. Choose the file again.',
            '*.uploaded' => 'That upload didn’t arrive. Choose the file again; each file can be up to 25 MB.',
            '*.extensions' => 'Use a supported file type: PDF, Word, PowerPoint, Excel, images, ZIP, Photoshop or Packet Tracer.',
            '*.max' => 'Each file can be up to 25 MB. Compress it or split it into a ZIP.',
            'repository.required' => 'Add the link to your GitHub repository.',
            'repository.url' => 'Enter a full link that starts with https://.',
            'repository.regex' => 'Link to a GitHub repository, like https://github.com/team-1/capstone.',
            'checklist.required' => 'Confirm each item in the checklist before you submit.',
            'checklist.*.accepted' => 'Confirm this item before you submit.',
        ];
    }
}
