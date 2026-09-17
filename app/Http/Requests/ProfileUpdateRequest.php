<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users')->ignore(Auth::id())],
            'nip' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'string', 'max:255', Rule::unique('users')->ignore(Auth::id())],
            'password' => ['nullable', 'string', 'confirmed', 'min:4'],
        ];
    }

    public function authorize()
    {
        return Auth::check();
    }

    protected function prepareForValidation()
    {
        if (empty($this->password)) {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
        }
    }
}