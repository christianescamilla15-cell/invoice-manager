<?php

namespace App\Repositories;

use App\Contracts\VendorRepositoryInterface;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;

class EloquentVendorRepository implements VendorRepositoryInterface
{
    public function all(): Collection
    {
        return Vendor::withCount('invoices')->orderBy('business_name')->get();
    }

    public function findById(int $id): Vendor
    {
        return Vendor::withCount('invoices')->findOrFail($id);
    }

    public function create(array $data): Vendor
    {
        return Vendor::create($data);
    }

    public function update(int $id, array $data): Vendor
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update($data);

        return $vendor->fresh();
    }

    public function delete(int $id): bool
    {
        $vendor = Vendor::findOrFail($id);

        return $vendor->delete();
    }
}
