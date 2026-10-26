<?php

namespace App\Http\Requests;

use App\Support\ZinaDemoData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrototypeContextRequest extends FormRequest
{
    /**
     * The prototype controls stand in for sign-in, so anyone viewing the demo may use them.
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
            'viewer' => ['sometimes', 'string', Rule::in(['aya', 'malek'])],
            'phase' => ['sometimes', 'string', Rule::in(['locked', 'forming'])],
            'role' => ['sometimes', 'string', Rule::in(ZinaDemoData::ROLES)],
            'redirect' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
