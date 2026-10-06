<?php

namespace App\Http\Requests;

use App\Models\antgo_re_date;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Updateantgo_re_dateRequest extends FormRequest
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
        /** @var antgo_re_date $date */
        $date = $this->route('date');

        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\-]+$/', Rule::unique('antgo_re_dates', 'code')->ignore($date, 'code')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'inSites' => ['sometimes', 'boolean'],
            'inBuildings' => ['sometimes', 'boolean'],
            'inProperties' => ['sometimes', 'boolean'],
            'inContracts' => ['sometimes', 'boolean'],
        ];
    }
}
