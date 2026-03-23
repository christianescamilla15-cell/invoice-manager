<?php

namespace App\Contracts;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface InvoiceRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator;

    public function findById(int $id): Invoice;

    public function create(array $data): Invoice;

    public function update(int $id, array $data): Invoice;

    public function delete(int $id): bool;

    public function getOverdue(): Collection;

    public function getKpis(): array;
}
