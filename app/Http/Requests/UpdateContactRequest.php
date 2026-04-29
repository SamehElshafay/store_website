<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
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
        $contactId = $this->route('contact')->id;

        return [
            'type'          => 'required|in:sender,recipient',
            'name'          => 'required|string|max:255',
            'phone'         => [
                'required',
                'string',
                'max:20',
                Rule::unique('contacts', 'phone')
                    ->ignore($contactId)
                    ->whereNull('deleted_at')
            ],
            'address'       => 'required|string|max:500',
            'notes'         => 'nullable|string',
        ];
    }
}
