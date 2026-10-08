<?php

namespace App\Http\Requests;

use App\Support\SpanishTaxId;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Storeantgo_re_contactRequest extends FormRequest
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
            'taxId' => [
                'required',
                'string',
                'max:20',
                Rule::unique('antgo_re_contacts', 'taxId'),
                static function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! SpanishTaxId::isValid((string) $value)) {
                        $fail(__('The :attribute must be a valid Spanish CIF, NIF or NIE.', ['attribute' => $attribute]));
                    }
                },
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'middleName' => ['nullable', 'string', 'max:255'],
            'lastName' => ['nullable', 'string', 'max:255'],
            'companyName' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['Hombre', 'Mujer', 'Other'])],
            'birthDate' => ['nullable', 'date'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobilePhone' => ['nullable', 'string', 'max:50'],
            'addresses' => ['sometimes', 'array', 'max:20'],
            'addresses.*.id' => ['prohibited'],
            'addresses.*.street' => ['required_with:addresses', 'string', 'max:255'],
            'addresses.*.number' => ['nullable', 'string', 'max:50'],
            'addresses.*.building' => ['nullable', 'string', 'max:100'],
            'addresses.*.floor' => ['nullable', 'string', 'max:50'],
            'addresses.*.door' => ['nullable', 'string', 'max:50'],
            'addresses.*.city' => ['required_with:addresses', 'string', 'max:255'],
            'addresses.*.postal_code' => ['nullable', 'string', 'max:20'],
            'addresses.*.country' => ['nullable', 'string', 'max:100'],
            'addresses.*.is_primary' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $taxId = $this->input('taxId', $this->input('tacId'));

        $this->merge([
            'taxId' => SpanishTaxId::normalize(is_string($taxId) ? $taxId : null),
        ]);
    }
}
