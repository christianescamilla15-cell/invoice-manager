<?php

namespace App\Contracts;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;

interface VendorRepositoryInterface
{
    public function all(): Collection;

    public function findById(int $id): Vendor;

    public function create(array $data): Vendor;

    public function update(int $id, array $data): Vendor;

    public function delete(int $id): bool;
}
