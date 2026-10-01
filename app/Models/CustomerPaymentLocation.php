<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPaymentLocation extends Model
{
    protected $fillable = [
        'customer_id',
        'transaction_id',
        'ip_address',
        'country_code',
        'country_name',
        'state_code',
        'state_name',
        'city',
        'postal_code',
        'latitude',
        'longitude',
        'accuracy_radius',
        'captured_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'captured_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}