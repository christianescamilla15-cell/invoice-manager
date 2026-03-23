<?php

namespace App\Http\Controllers\Api;

use App\Contracts\InvoiceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceStatusRequest;
use App\Http\Resources\InvoiceCollection;
use App\Http\Resources\InvoiceResource;
use App\Services\InvoiceService;
use App\Services\PdfExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
        private InvoiceService $invoiceService,
        private PdfExportService $pdfExportService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'vendor_id', 'from', 'to']);
        $invoices = $this->invoiceRepository->all($filters);

        return response()->json(new InvoiceCollection($invoices));
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $items = $data['items'];
        unset($data['items']);

        $data['user_id'] = $request->user()->id;

        $invoice = $this->invoiceService->createInvoice($data, $items);

        return response()->json([
            'data' => new InvoiceResource($invoice),
            'message' => 'Factura creada correctamente.',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $invoice = $this->invoiceRepository->findById($id);

        return response()->json([
            'data' => new InvoiceResource($invoice),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        $items = $data['items'] ?? null;
        unset($data['items']);

        $invoice = $this->invoiceService->updateInvoice($id, $data, $items);

        return response()->json([
            'data' => new InvoiceResource($invoice),
            'message' => 'Factura actualizada correctamente.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->invoiceService->deleteInvoice($id);

        return response()->json([
            'message' => 'Factura eliminada correctamente.',
        ]);
    }

    public function updateStatus(UpdateInvoiceStatusRequest $request, int $id): JsonResponse
    {
        $invoice = $this->invoiceService->updateStatus($id, $request->validated()['status']);

        return response()->json([
            'data' => new InvoiceResource($invoice),
            'message' => 'Estatus actualizado correctamente.',
        ]);
    }

    public function pdf(int $id): Response
    {
        $invoice = $this->invoiceRepository->findById($id);
        $pdfContent = $this->pdfExportService->generate($invoice);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$invoice->invoice_number}.pdf\"",
        ]);
    }
}
