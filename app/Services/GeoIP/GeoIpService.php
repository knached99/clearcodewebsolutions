<?php

namespace App\Services\GeoIP;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use RuntimeException;

class GeoIpService
{
    protected Reader $reader;

    public function __construct()
    {
        $databasePath = config('geoip.database_path');

        if (! is_file($databasePath)) {
            throw new RuntimeException(
                "MaxMind database not found at: {$databasePath}"
            );
        }

        $this->reader = new Reader($databasePath);
    }

    public function lookup(string $ip): ?array
    {
        $ip = trim($ip);

        if ($ip === '') {
            return null;
        }

        try {
            $record = $this->reader->city($ip);

            return [
                'country_code' => $record->country->isoCode,
                'country_name' => $record->country->name,

                'state_code' => $record->mostSpecificSubdivision->isoCode,
                'state_name' => $record->mostSpecificSubdivision->name,

                'city' => $record->city->name,
                'postal_code' => $record->postal->code,

                'latitude' => $record->location->latitude,
                'longitude' => $record->location->longitude,

                'accuracy_radius' => $record->location->accuracyRadius,
            ];
        } catch (AddressNotFoundException) {
            return null;
        }
    }
}