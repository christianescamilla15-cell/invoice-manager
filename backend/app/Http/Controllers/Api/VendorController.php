<?php

namespace App\Http\Controllers\Api;

use App\Contracts\VendorRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Http\Resources\VendorResource;
use Illuminate\Http\JsonResponse;

class VendorController extends Controller
{
    public function __construct(
        private VendorRepositoryInterface $vendorRepository
    ) {}

    public function index(): JsonResponse
    {
        $vendors = $this->vendorRepository->all();

        return response()->json([
            'data' => VendorResource::collection($vendors),
        ]);
    }

    public function store(StoreVendorRequest $request): JsonResponse
    {
        $vendor = $this->vendorRepository->create($request->validated());

        return response()->json([
            'data' => new VendorResource($vendor),
            'message' => 'Proveedor creado correctamente.',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $vendor = $this->vendorRepository->findById($id);

        return response()->json([
            'data' => new VendorResource($vendor),
        ]);
    }

    public function update(UpdateVendorRequest $request, int $id): JsonResponse
    {
        $vendor = $this->vendorRepository->update($id, $request->validated());

        return response()->json([
            'data' => new VendorResource($vendor),
            'message' => 'Proveedor actualizado correctamente.',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->vendorRepository->delete($id);

        return response()->json([
            'message' => 'Proveedor eliminado correctamente.',
        ]);
    }
}
