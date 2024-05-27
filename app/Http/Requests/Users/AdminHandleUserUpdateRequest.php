<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class AdminHandleUserUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'string|min:1',
            'date_of_birth' => 'date',
            'phone' => 'string',
            'address' => 'text',
            'join_date' => 'date',
            'is_active' => 'boolean',
            'department_id' => 'numeric',
            'role_id' => 'numeric',
        ];
    }
}
