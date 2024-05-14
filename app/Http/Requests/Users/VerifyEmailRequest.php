<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userId = $this->input('id');
        $user = User::find($userId);

        if (!$user || !$this->isValidHash($user)) {
            return false;
        }

        return true;
    }

    private function isValidHash($user): bool
    {
        $providedHash = $this->input('hash');
        $expectedHash = sha1($user->getEmailForVerification());

        return hash_equals($providedHash, $expectedHash);
    }

    public function fulfill()
    {
        $user = User::find($this->input('id'));
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|int',
            'hash' => 'required|string'
        ];
    }
}
