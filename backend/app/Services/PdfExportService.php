<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfExportService
{
    public function generate(Invoice $invoice): string
    {
        $invoice->load(['vendor', 'items', 'user']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->output();
    }
}
