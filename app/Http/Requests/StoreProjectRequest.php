<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\Milestone;
use App\Models\Project;
use App\Support\CurrentUser;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * The Create project wizard (UC-02). "Publish" checks everything; "Save as draft" only needs a title.
 * Field names are the wizard's own (Nadine's markup), so errors land on the fields the professor typed in.
 */
class StoreProjectRequest extends FormRequest
{
    /** The wizard's single deliverable choice per milestone, as deliverable types. */
    public const DELIVERABLE_CHOICES = [
        'PDF report' => ['file'],
        'PowerPoint' => ['slides'],
        'GitHub link' => ['github'],
        'Demo video link' => ['link'],
        'Written answer' => ['file'],
        'Mixed' => ['file', 'slides', 'images', 'link'],
    ];

    public const LEFTOVER = ['Random placement' => 'auto', 'Professor decides' => 'manual'];

    public const FORMATION = ['Students choose', 'Professor assigns', 'Random'];

    public const LATE_POLICIES = ['Allow with a Late flag', 'Block after deadline'];

    public const PERMISSIONS = ['Comment only', 'Full review'];

    public function authorize(): bool
    {
        $user = CurrentUser::get();
        $project = $this->route('project');

        return $user !== null && ($project instanceof Project ? $user->can('update', $project) : $user->can('create', Project::class));
    }

    public function publishing(): bool
    {
        return $this->input('intent') !== 'draft';
    }

    protected function prepareForValidation(): void
    {
        $milestones = collect($this->input('milestones', []))->values()->map(function ($milestone) {
            $milestone = is_array($milestone) ? $milestone : [];
            if (! isset($milestone['deliverable_types']) && isset($milestone['deliverable'])) {
                $milestone['deliverable_types'] = self::DELIVERABLE_CHOICES[$milestone['deliverable']] ?? [];
            }

            return $milestone;
        })->all();

        $this->merge([
            'milestones' => $milestones,
            'criteria' => collect($this->input('criteria', []))->values()->all(),
            'goals' => collect($this->input('goals', []))->map(fn ($goal) => is_string($goal) ? trim($goal) : $goal)->filter()->values()->all(),
            'resubmit' => $this->boolean('resubmit'),
            'reminders' => $this->boolean('reminders'),
        ]);
    }

    public function rules(): array
    {
        $required = $this->publishing() ? 'required' : 'nullable';
        $professorId = CurrentUser::get()?->id;

        return [
            'intent' => ['nullable', Rule::in(['publish', 'draft'])],
            'submission_key' => ['nullable', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:200'],
            'color' => ['nullable', 'string', 'max:40'],
            'course' => [$required, 'integer', Rule::exists('courses', 'id')->where('professor_id', $professorId)],
            'semester' => ['nullable', 'string', 'max:40'],
            'description' => [$required, 'string', 'max:5000'],
            'goals' => ['array', 'max:10'],
            'goals.*' => ['string', 'max:300'],

            'milestones' => [$this->publishing() ? 'required' : 'nullable', 'array', $this->publishing() ? 'min:1' : 'min:0', 'max:12'],
            'milestones.*.name' => [$required, 'nullable', 'string', 'max:120'],
            'milestones.*.deadline' => [$required, 'nullable', 'date'],
            'milestones.*.deliverable_types' => [$this->publishing() ? 'required' : 'nullable', 'array', $this->publishing() ? 'min:1' : 'min:0'],
            'milestones.*.deliverable_types.*' => [Rule::in(array_keys(Milestone::DELIVERABLE_TYPES))],
            'milestones.*.weight' => [$required, 'nullable', 'integer', 'min:1', 'max:100'],

            'grade' => [$required, 'nullable', 'integer', 'min:1', 'max:1000'],
            'late_policy' => ['nullable', Rule::in(self::LATE_POLICIES)],
            'criteria' => ['array', 'max:20'],
            'criteria.*.name' => ['required', 'string', 'max:120'],
            'criteria.*.points' => ['required', 'integer', 'min:1', 'max:1000'],
            'resubmit' => ['boolean'],
            'reminders' => ['boolean'],

            'team_min' => [$required, 'nullable', 'integer', 'min:1', 'max:20'],
            'team_max' => [$required, 'nullable', 'integer', 'max:20', 'gte:team_min'],
            'formation' => ['nullable', Rule::in(self::FORMATION)],
            'team_deadline' => [$required, 'nullable', 'date'],
            'leftover' => [$required, 'nullable', Rule::in(array_keys(self::LEFTOVER))],
            'permission' => ['nullable', Rule::in(self::PERMISSIONS)],
        ];
    }

    /**
     * Rules that compare fields: course semester, milestone order and dates, weights and rubric totals.
     */
    public function after(): array
    {
        return [function (Validator $validator) {
            if (! $this->publishing() || $validator->errors()->isNotEmpty()) {
                return;
            }

            $course = Course::find($this->input('course'));
            $start = CarbonImmutable::parse($course->starts_on)->startOfDay();
            $end = CarbonImmutable::parse($course->ends_on)->endOfDay();
            $formation = CarbonImmutable::parse($this->input('team_deadline'))->endOfDay();
            $range = $start->format('M j').' to '.$end->format('M j, Y');

            if ($this->filled('semester') && $this->input('semester') !== $course->semester) {
                $validator->errors()->add('semester', "{$course->code} runs in {$course->semester}.");
            }
            if (! $formation->betweenIncluded($start, $end)) {
                $validator->errors()->add('team_deadline', "The team-choosing deadline must fall inside {$course->semester} ({$range}).");
            }

            $previous = null;
            foreach ($this->input('milestones') as $index => $milestone) {
                $due = CarbonImmutable::parse($milestone['deadline']);
                $label = 'M'.($index + 1);
                if (! $due->betweenIncluded($start, $end)) {
                    $validator->errors()->add("milestones.{$index}.deadline", "{$label} must be due inside {$course->semester} ({$range}).");
                } elseif ($due->lessThanOrEqualTo($formation)) {
                    $validator->errors()->add("milestones.{$index}.deadline", "{$label} must be due after the team-choosing deadline (".$formation->format('M j').').');
                } elseif ($previous && $due->lessThanOrEqualTo($previous)) {
                    $validator->errors()->add("milestones.{$index}.deadline", "{$label} must be due after M{$index}.");
                }
                $previous = $due;
            }

            $weight = collect($this->input('milestones'))->sum('weight');
            if ($weight !== 100) {
                $validator->errors()->add('milestones', "Milestone weights must total 100% (now {$weight}%).");
            }

            $criteria = collect($this->input('criteria'));
            if ($criteria->isNotEmpty() && (int) $criteria->sum('points') !== (int) $this->input('grade')) {
                $validator->errors()->add('criteria', 'Rubric points must equal the total project grade ('.$this->input('grade').'), or remove all criteria to use no rubric.');
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'course' => 'course',
            'grade' => 'total project grade',
            'team_min' => 'minimum team size',
            'team_max' => 'maximum team size',
            'team_deadline' => 'team-choosing deadline',
            'leftover' => 'what happens when the deadline passes',
            'milestones.*.name' => 'milestone name',
            'milestones.*.deadline' => 'milestone deadline',
            'milestones.*.deliverable_types' => 'deliverable type',
            'milestones.*.weight' => 'milestone weight',
            'criteria.*.name' => 'criterion name',
            'criteria.*.points' => 'criterion points',
        ];
    }

    public function messages(): array
    {
        return [
            'team_max.gte' => 'The maximum team size must be at least the minimum team size.',
            'milestones.required' => 'Add at least one milestone.',
            'milestones.min' => 'Add at least one milestone.',
            'milestones.*.deliverable_types.required' => 'Choose at least one deliverable type for this milestone.',
            'milestones.*.deliverable_types.min' => 'Choose at least one deliverable type for this milestone.',
            'grade.min' => 'The total project grade must be more than 0.',
        ];
    }
}
