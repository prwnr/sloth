<?php

namespace App\Http\Requests;

use App\Models\TodoTask;
use App\Repositories\TodoTaskRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class TodoTaskAuthorizationRequest
 * @package App\Http\Requests
 */
class TodoTaskAuthorizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $repository = new TodoTaskRepository(new TodoTask());
        $todo = $repository->find($this->route('todo'));
        if ($todo->member->id !== Auth::user()->member()->id) {
            return false;
        }

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
            //
        ];
    }
}
