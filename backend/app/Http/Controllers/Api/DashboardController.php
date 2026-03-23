<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Http\Resources\InvoiceResource;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(): JsonResponse
    {
        $data = [
            'kpis' => $this->dashboardService->getKpis(),
            'recent_invoices' => $this->dashboardService->getRecentInvoices(),
            'monthly_totals' => $this->dashboardService->getMonthlyTotals(),
        ];

        return response()->json(new DashboardResource($data));
    }

    public function overdue(): JsonResponse
    {
        $invoices = $this->dashboardService->getOverdueInvoices();

        return response()->json([
            'data' => InvoiceResource::collection($invoices),
        ]);
    }
}
