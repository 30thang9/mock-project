<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
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
            'name' => 'required|string|min:1',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6|regex:/^(?=.*\d)(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{6,}$/',
            'department_id' => 'required|numeric',
            'role_id' => 'required|numeric',
        ];
    }
}
