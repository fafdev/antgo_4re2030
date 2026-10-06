<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Storeantgo_re_adminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\-]+$/', Rule::unique('antgo_re_admins', 'role')],
            'description' => ['required', 'string', 'max:255'],
        ];
    }
}
