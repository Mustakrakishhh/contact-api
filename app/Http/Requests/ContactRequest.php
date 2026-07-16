<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // разрешаем всем
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|regex:/^\+?[0-9]{10,15}$/',
            'email' => 'required|email:rfc,dns',
            'comment' => 'required|string|min:3',
        ];
    }
}
