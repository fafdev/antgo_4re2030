<?php

namespace App\Http\Requests;

use App\Models\antgo_re_measure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Updateantgo_re_measureRequest extends FormRequest
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
        /** @var antgo_re_measure $measure */
        $measure = $this->route('measure');

        return [
            'description' => ['required', 'string', 'max:255', Rule::unique('antgo_re_measures', 'description')->ignore($measure)],
        ];
    }
}
