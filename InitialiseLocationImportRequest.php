<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitialiseLocationImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:10',
            'queue_name' => 'string|required'
        ];
    }
}
