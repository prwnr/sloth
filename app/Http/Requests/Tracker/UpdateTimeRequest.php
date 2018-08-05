<?php

namespace App\Http\Requests\Tracker;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateTimeRequest
 * @package App\Http\Requests
 */
class UpdateTimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'duration' => [
                'required', 'numeric'
            ],
            'start' => [
                'nullable', 'date'
            ],
        ];
    }
}
