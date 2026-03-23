<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vendorId = $this->route('vendor');

        return [
            'rfc' => ['sometimes', 'string', 'max:13', Rule::unique('vendors', 'rfc')->ignore($vendorId)],
            'business_name' => ['sometimes', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['nullable', 'string', 'max:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'rfc.unique' => 'Este RFC ya está registrado.',
            'rfc.max' => 'El RFC no puede tener más de 13 caracteres.',
            'business_name.required' => 'La razón social es obligatoria.',
        ];
    }
}
