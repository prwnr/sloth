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
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
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
