<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPaymentLocation;
use App\Models\Transaction;
use App\Services\GeoIP\GeoIpService;
use Illuminate\Support\Facades\Log;

class CustomerLocationService
{
    public function __construct(
        protected GeoIpService $geoIp
    ) {
    }

    /**
     * Record a customer's location from an IP address.
     *
     * Used for non-payment location captures.
     */
    public function recordFromIp(
        Customer $customer,
        string $ip
    ): ?CustomerPaymentLocation {
        $ip = trim($ip);

        if ($ip === '') {
            return null;
        }

        $location = $this->geoIp->lookup($ip);

        if ($location === null) {
            Log::warning('GeoIP lookup returned no location.', [
                'customer_id' => $customer->id,
                'ip_address' => $ip,
            ]);

            return null;
        }

        $paymentLocation = $customer->locations()->create([
            'ip_address' => $ip,
            'country_code' => $location['country_code'] ?? null,
            'country_name' => $location['country_name'] ?? null,
            'state_code' => $location['state_code'] ?? null,
            'state_name' => $location['state_name'] ?? null,
            'city' => $location['city'] ?? null,
            'postal_code' => $location['postal_code'] ?? null,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'accuracy_radius' => $location['accuracy_radius'] ?? null,
            'captured_at' => now(),
        ]);

        Log::info('Customer geographic location recorded.', [
            'customer_id' => $customer->id,
            'location_id' => $paymentLocation->id,
            'country_code' => $location['country_code'] ?? null,
            'state_code' => $location['state_code'] ?? null,
            'city' => $location['city'] ?? null,
        ]);

        return $paymentLocation;
    }

    /**
     * Record the customer's location for a specific successful payment.
     *
     * This is called from StripeWebhookService after a successful
     * transaction has been created.
     */
    public function recordForPayment(
        Customer $customer,
        Transaction $transaction,
        string $ipAddress
    ): ?CustomerPaymentLocation {
        $ipAddress = trim($ipAddress);

        if ($ipAddress === '') {
            return null;
        }

        /*
         * One payment transaction can only have one payment-location
         * record because transaction_id is unique in the database.
         */
        $existing = CustomerPaymentLocation::query()
            ->where('transaction_id', $transaction->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        /*
         * Look up the IP using GeoIP.
         */
        $location = $this->geoIp->lookup($ipAddress);

        if ($location === null) {
            Log::warning(
                'GeoIP lookup returned no location for payment.',
                [
                    'customer_id' => $customer->id,
                    'transaction_id' => $transaction->id,
                    'payment_intent_id' =>
                        $transaction->stripe_payment_intent_id,
                    'ip_address' => $ipAddress,
                ]
            );

            return null;
        }

        /*
         * Create the location and associate it with both:
         *
         * customer
         * transaction
         */
        $paymentLocation = $customer->locations()->create([
            'transaction_id' => $transaction->id,
            'ip_address' => $ipAddress,
            'country_code' => $location['country_code'] ?? null,
            'country_name' => $location['country_name'] ?? null,
            'state_code' => $location['state_code'] ?? null,
            'state_name' => $location['state_name'] ?? null,
            'city' => $location['city'] ?? null,
            'postal_code' => $location['postal_code'] ?? null,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'accuracy_radius' => $location['accuracy_radius'] ?? null,
            'captured_at' => now(),
        ]);

        Log::info(
            'Customer payment location recorded.',
            [
                'customer_id' => $customer->id,
                'transaction_id' => $transaction->id,
                'payment_intent_id' =>
                    $transaction->stripe_payment_intent_id,
                'payment_location_id' => $paymentLocation->id,
                'ip_address' => $ipAddress,
                'country_code' => $location['country_code'] ?? null,
                'state_code' => $location['state_code'] ?? null,
                'state_name' => $location['state_name'] ?? null,
                'city' => $location['city'] ?? null,
                'postal_code' => $location['postal_code'] ?? null,
                'latitude' => $location['latitude'] ?? null,
                'longitude' => $location['longitude'] ?? null,
                'accuracy_radius' =>
                    $location['accuracy_radius'] ?? null,
            ]
        );

        return $paymentLocation;
    }
}