<?php

namespace App\Providers;

use App\Contracts\InvoiceRepositoryInterface;
use App\Contracts\VendorRepositoryInterface;
use App\Repositories\EloquentInvoiceRepository;
use App\Repositories\EloquentVendorRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);
        $this->app->bind(VendorRepositoryInterface::class, EloquentVendorRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
