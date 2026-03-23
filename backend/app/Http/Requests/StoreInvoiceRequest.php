<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['required', 'exists:vendors,id'],
            'tax_type' => ['required', 'in:iva,iva_retention,exempt'],
            'issued_at' => ['required', 'date'],
            'due_at' => ['required', 'date', 'after_or_equal:issued_at'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'vendor_id.required' => 'El proveedor es obligatorio.',
            'vendor_id.exists' => 'El proveedor seleccionado no existe.',
            'tax_type.in' => 'El tipo de impuesto debe ser: iva, iva_retention o exempt.',
            'issued_at.required' => 'La fecha de emisión es obligatoria.',
            'due_at.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de emisión.',
            'items.required' => 'Debe incluir al menos un concepto.',
            'items.min' => 'Debe incluir al menos un concepto.',
            'items.*.description.required' => 'La descripción del concepto es obligatoria.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
            'items.*.unit_price.min' => 'El precio unitario debe ser mayor a 0.',
        ];
    }
}
