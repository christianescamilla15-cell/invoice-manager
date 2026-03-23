<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:draft,pending,paid,overdue,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'El estatus es obligatorio.',
            'status.in' => 'El estatus debe ser: draft, pending, paid, overdue o cancelled.',
        ];
    }
}
