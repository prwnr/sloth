<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class RoleRequest
 * @package App\Http\Requests
 */
class RoleRequest extends FormRequest
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
     * {@inheritdoc}
     */
    public function input($key = null, $default = null)
    {
        if ($key === 'name') {
            return str_slug(data_get(
                $this->getInputSource()->all() + $this->query->all(), 'display_name', $default
            ), '_');
        }

        return parent::input($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function all($keys = null): array
    {
        $data = parent::all($keys);
        $data['name'] = str_slug($data['display_name'] ?? '', '_');

        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required'
            ],
            'display_name' => [
                'required', 'string', 'max:255'
            ],
            'description' => [
                'required', 'string', 'max:500'
            ],
        ];
    }
}
