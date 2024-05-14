<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|numeric',
            'name' => 'required|string|min:1',
//            'email' => 'required|email|unique:users',
//            'password' => 'required|confirmed|min:6|regex:/^(?=.*\d)(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{6,}$/',
            'date_of_birth' => 'date',
            'avatar' => 'string',
            'phone' => 'string',
            'address' => 'text',
            'join_date' => 'date',
            'is_active' => 'boolean',
            'department_id' => 'numeric',
            'role_id' => 'numeric',
        ];
    }
}
