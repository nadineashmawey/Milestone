<?php

namespace App\Http\Requests;

use App\Support\ZinaDemoData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Everyone may edit their own permitted fields (ACC-10). Email, student ID and role aren't accepted here at all.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Unticked checkboxes aren't sent, so fill every category and channel with an explicit true or false.
     */
    protected function prepareForValidation(): void
    {
        $sent = (array) $this->input('notifications', []);
        $notifications = [];
        foreach (array_keys(ZinaDemoData::NOTIFICATION_CATEGORIES) as $category) {
            foreach (array_keys(ZinaDemoData::NOTIFICATION_CHANNELS) as $channel) {
                $notifications[$category][$channel] = filter_var($sent[$category][$channel] ?? false, FILTER_VALIDATE_BOOLEAN);
            }
        }

        $this->merge([
            'first_name' => trim((string) $this->input('first_name')),
            'last_name' => trim((string) $this->input('last_name')),
            'notifications' => $notifications,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $name = ['required', 'string', 'max:50', "regex:/^[\pL][\pL\s'’.-]*$/u"];

        return [
            'first_name' => $name,
            'last_name' => $name,
            'date_format' => ['required', Rule::in(array_keys(ZinaDemoData::DISPLAY_OPTIONS['date_format']))],
            'time_format' => ['required', Rule::in(array_keys(ZinaDemoData::DISPLAY_OPTIONS['time_format']))],
            'week_start' => ['required', Rule::in(array_keys(ZinaDemoData::DISPLAY_OPTIONS['week_start']))],
            'notifications' => ['required', 'array'],
            'notifications.*.*' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Enter your first name.',
            'last_name.required' => 'Enter your last name.',
            '*.max' => 'Use 50 characters or fewer.',
            'first_name.regex' => 'Use letters, spaces, hyphens or apostrophes.',
            'last_name.regex' => 'Use letters, spaces, hyphens or apostrophes.',
            '*.in' => 'Choose one of the listed options.',
        ];
    }

    /**
     * DLN-3: deadline reminders always reach you somewhere.
     *
     * @return list<callable>
     */
    public function after(): array
    {
        return [function (Validator $validator) {
            $deadlines = $this->input('notifications.deadlines', []);
            if (! ($deadlines['in_app'] ?? false) && ! ($deadlines['email'] ?? false)) {
                $validator->errors()->add('notifications.deadlines', 'Keep at least one channel on for deadline reminders, so you don’t miss a due date.');
            }
        }];
    }
}
