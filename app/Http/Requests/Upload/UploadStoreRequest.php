<?php

namespace App\Http\Requests\Upload;

use Illuminate\Foundation\Http\FormRequest;

class UploadStoreRequest extends FormRequest
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
            'file' => 'required|mimes:csv,txt|max:51200',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a CSV or text file to upload.',
            'file.mimes' => 'The file must be a CSV or TXT file.',
            'file.max' => 'The file size must not exceed 50MB.',
        ];
    }
}
