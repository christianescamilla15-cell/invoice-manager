<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfc',
        'business_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function setRfcAttribute(string $value): void
    {
        $this->attributes['rfc'] = strtoupper(trim($value));
    }

    public function getRfcAttribute(?string $value): ?string
    {
        return $value ? strtoupper($value) : null;
    }
}
