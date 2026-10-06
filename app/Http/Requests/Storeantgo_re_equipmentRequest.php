<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Storeantgo_re_equipmentRequest extends FormRequest
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
            'description' => ['required', 'string', 'max:255', Rule::unique('antgo_re_equipments', 'description')],
            'inSites' => ['sometimes', 'boolean'],
            'inBuildings' => ['sometimes', 'boolean'],
            'inProperties' => ['sometimes', 'boolean'],
        ];
    }
}
