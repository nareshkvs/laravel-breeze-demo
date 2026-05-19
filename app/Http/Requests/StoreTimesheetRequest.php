<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTimesheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'work_date' => ['required', 'date', 'before_or_equal:today'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.project_id' => ['required', 'exists:projects,id'],
            'entries.*.description' => ['required', 'string', 'max:1000'],
            'entries.*.duration' => ['required', 'string'],
        ];
    }
}
