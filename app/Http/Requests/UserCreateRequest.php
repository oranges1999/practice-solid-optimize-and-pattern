<?php

namespace App\Http\Requests;

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
            'name' => 'required|max:20|string',
            'type' => 'required|in:1,2',
            'description' => 'required|max:255|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed',
            'avatar' => 'sometimes|mimes:jpeg,png,jpg,gif,avif'
        ];
    }
}
