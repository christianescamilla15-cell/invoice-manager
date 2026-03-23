<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['sometimes', 'exists:vendors,id'],
            'tax_type' => ['sometimes', 'in:iva,iva_retention,exempt'],
            'issued_at' => ['sometimes', 'date'],
            'due_at' => ['sometimes', 'date', 'after_or_equal:issued_at'],
            'notes' => ['nullable', 'string'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'vendor_id.exists' => 'El proveedor seleccionado no existe.',
            'tax_type.in' => 'El tipo de impuesto debe ser: iva, iva_retention o exempt.',
            'due_at.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión.',
            'items.min' => 'Debe incluir al menos un concepto.',
            'items.*.description.required_with' => 'La descripción del concepto es obligatoria.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
            'items.*.unit_price.min' => 'El precio unitario debe ser mayor a 0.',
        ];
    }
}
