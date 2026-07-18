<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Geocode address to coordinates using Nominatim API
     */
    public function geocode(string $address): ?array
    {
        try {
            $response = Http::get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if (empty($data)) {
                return null;
            }

            $result = $data[0];

            return [
                'lat' => (float) $result['lat'],
                'lng' => (float) $result['lon'],
                'display_name' => $result['display_name'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('GeocodingService geocode Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Reverse geocode coordinates to address
     */
    public function reverseGeocode(float $lat, float $lng): ?array
    {
        try {
            $response = Http::get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $lat,
                'lon' => $lng,
                'format' => 'json',
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            return [
                'address' => $data['display_name'] ?? null,
                'city' => $data['address']['city'] ?? ($data['address']['town'] ?? ($data['address']['village'] ?? null)),
                'country' => $data['address']['country'] ?? null,
                'country_code' => $data['address']['country_code'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('GeocodingService reverseGeocode Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Search for places
     */
    public function search(string $query, int $limit = 5): array
    {
        try {
            $response = Http::get('https://nominatim.openstreetmap.org/search', [
                'q' => $query,
                'format' => 'json',
                'limit' => $limit,
            ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();

            return array_map(function ($result) {
                return [
                    'lat' => (float) $result['lat'],
                    'lng' => (float) $result['lon'],
                    'display_name' => $result['display_name'] ?? null,
                    'type' => $result['type'] ?? null,
                ];
            }, $data);

        } catch (\Exception $e) {
            Log::error('GeocodingService search Error: ' . $e->getMessage());
            return [];
        }
    }
}
