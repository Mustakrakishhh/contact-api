<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^\+?[0-9]{10,15}$/'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'comment' => ['required', 'string', 'min:3', 'max:3000'],
        ];
    }
}
