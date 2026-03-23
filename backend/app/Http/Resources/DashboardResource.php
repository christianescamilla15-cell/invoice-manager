<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'kpis' => [
                'total_invoices' => $this->resource['kpis']['total_invoices'],
                'total_pending' => $this->resource['kpis']['total_pending'],
                'total_overdue' => $this->resource['kpis']['total_overdue'],
                'total_paid' => $this->resource['kpis']['total_paid'],
                'total_amount' => number_format($this->resource['kpis']['total_amount'], 2, '.', ''),
                'total_pending_amount' => number_format($this->resource['kpis']['total_pending_amount'], 2, '.', ''),
                'total_overdue_amount' => number_format($this->resource['kpis']['total_overdue_amount'], 2, '.', ''),
            ],
            'recent_invoices' => InvoiceResource::collection($this->resource['recent_invoices']),
            'monthly_totals' => $this->resource['monthly_totals'],
        ];
    }
}
