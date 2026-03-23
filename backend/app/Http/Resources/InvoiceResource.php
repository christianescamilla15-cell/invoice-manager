<?php

namespace App\Http\Resources;

use App\Enums\InvoiceStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = InvoiceStatus::tryFrom($this->status);

        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status,
            'status_label' => $status?->label() ?? $this->status,
            'status_color' => $status?->color() ?? '#6B7280',
            'tax_type' => $this->tax_type,
            'issued_at' => $this->issued_at->format('Y-m-d'),
            'due_at' => $this->due_at->format('Y-m-d'),
            'subtotal' => number_format((float) $this->subtotal, 2, '.', ''),
            'tax_amount' => number_format((float) $this->tax_amount, 2, '.', ''),
            'retention_amount' => number_format((float) $this->retention_amount, 2, '.', ''),
            'total' => number_format((float) $this->total, 2, '.', ''),
            'notes' => $this->notes,
            'paid_at' => $this->paid_at?->toISOString(),
            'vendor' => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor->id,
                'business_name' => $this->vendor->business_name,
                'rfc' => $this->vendor->rfc,
            ]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => number_format((float) $item->quantity, 2, '.', ''),
                'unit_price' => number_format((float) $item->unit_price, 2, '.', ''),
                'amount' => number_format((float) $item->amount, 2, '.', ''),
            ])),
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
