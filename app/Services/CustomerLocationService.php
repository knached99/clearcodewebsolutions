<?php

namespace App\Services;

use App\Models\Customer;
use App\Services\GeoIP\GeoIpService;
use Illuminate\Support\Facades\Log;

class CustomerLocationService
{
    public function __construct(
        protected GeoIpService $geoIp
    ) {
    }

    public function recordFromIp(
        Customer $customer,
        string $ip
    ): void {
        $location = $this->geoIp->lookup($ip);

        if ($location === null) {
            return;
        }

        $customer->locations()->create([
            'ip_address' => $ip,

            'country_code' => $location['country_code'],
            'country_name' => $location['country_name'],

            'state_code' => $location['state_code'],
            'state_name' => $location['state_name'],

            'city' => $location['city'],
            'postal_code' => $location['postal_code'],

            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],

            'accuracy_radius' => $location['accuracy_radius'],

            'source' => 'checkout',

            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        Log::info('Customer geographic location recorded.', [
            'customer_id' => $customer->id,
            'country_code' => $location['country_code'],
            'state_code' => $location['state_code'],
            'city' => $location['city'],
        ]);
    }
}