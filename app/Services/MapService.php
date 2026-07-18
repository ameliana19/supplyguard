<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Port;
use App\Models\Shipment;
use Illuminate\Support\Facades\Log;

class MapService
{
    /**
     * Get map data optimized for rendering.
     */
    public function getMapData(): array
    {
        try {
            // Optimasi: Hanya ambil kolom yang dibutuhkan, hindari load seluruh tabel
            $countriesList = Country::with('latestRiskScore:country_id,risk_level,total_score')
                ->select('id', 'name', 'capital', 'latitude', 'longitude')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();
            
            $countries = [];
            $seenCountryCoords = [];
            foreach ($countriesList as $country) {
                $riskScore = $country->latestRiskScore;
                $coordKey = $country->latitude . ',' . $country->longitude;
                if (!in_array($coordKey, $seenCountryCoords)) {
                    $seenCountryCoords[] = $coordKey;
                    $countries[] = [
                        'id' => $country->id,
                        'name' => $country->name,
                        'capital' => $country->capital,
                        'lat' => $country->latitude,
                        'lng' => $country->longitude,
                        'risk_level' => $riskScore ? $riskScore->risk_level : 'Low',
                        'risk_score' => $riskScore ? $riskScore->total_score : 0,
                    ];
                }
            }

            // Optimasi: Hanya ambil kolom yang dibutuhkan dan batasi relasi negara ke nama saja
            $portsList = Port::mainPorts()
                ->with('country:id,name')
                ->select('id', 'port_name', 'port_code', 'city', 'country_id', 'latitude', 'longitude', 'capacity', 'status')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();
            
            $ports = [];
            $seenPortCoords = [];
            foreach ($portsList as $port) {
                $coordKey = $port->latitude . ',' . $port->longitude;
                if (!in_array($coordKey, $seenPortCoords) && !in_array($coordKey, $seenCountryCoords)) {
                    $seenPortCoords[] = $coordKey;
                    $ports[] = [
                        'id' => $port->id,
                        'name' => $port->port_name,
                        'code' => $port->port_code,
                        'city' => $port->city,
                        'country' => $port->country ? $port->country->name : 'Unknown',
                        'lat' => $port->latitude,
                        'lng' => $port->longitude,
                        'capacity' => $port->capacity,
                        'status' => $port->status,
                    ];
                }
                
                if (count($ports) >= 150) {
                    break;
                }
            }

            // Optimasi: Hanya load shipment yang diperlukan dengan select yang spesifik pada relasi
            $shipments = Shipment::with([
                    'originCountry:id,name,latitude,longitude', 
                    'destinationCountry:id,name,latitude,longitude', 
                    'originPort:id,port_name,latitude,longitude', 
                    'destinationPort:id,port_name,latitude,longitude'
                ])
                ->select('id', 'tracking_number', 'cargo_type', 'status', 'origin_country_id', 'destination_country_id', 'origin_port_id', 'destination_port_id')
                ->where('status', '!=', 'Cancelled')
                ->get()
                ->map(function ($shipment) {
                    $originLat = $shipment->origin_port ? $shipment->origin_port->latitude : ($shipment->origin_country ? $shipment->origin_country->latitude : null);
                    $originLng = $shipment->origin_port ? $shipment->origin_port->longitude : ($shipment->origin_country ? $shipment->origin_country->longitude : null);
                    $originName = $shipment->origin_port ? $shipment->origin_port->port_name : ($shipment->origin_country ? $shipment->origin_country->name : '');

                    $destLat = $shipment->destination_port ? $shipment->destination_port->latitude : ($shipment->destination_country ? $shipment->destination_country->latitude : null);
                    $destLng = $shipment->destination_port ? $shipment->destination_port->longitude : ($shipment->destination_country ? $shipment->destination_country->longitude : null);
                    $destName = $shipment->destination_port ? $shipment->destination_port->port_name : ($shipment->destination_country ? $shipment->destination_country->name : '');

                    return [
                        'id' => $shipment->id,
                        'tracking_number' => $shipment->tracking_number,
                        'cargo_type' => $shipment->cargo_type,
                        'status' => $shipment->status,
                        'origin' => ['name' => $originName, 'lat' => $originLat, 'lng' => $originLng],
                        'destination' => ['name' => $destName, 'lat' => $destLat, 'lng' => $destLng],
                    ];
                })->filter(function ($shipment) {
                    return $shipment['origin']['lat'] && $shipment['origin']['lng'] &&
                           $shipment['destination']['lat'] && $shipment['destination']['lng'];
                });

            return [
                'success' => true,
                'data' => [
                    'countries' => $countries,
                    'ports' => $ports,
                    'shipments' => array_values($shipments->toArray()), // Reset array index
                ]
            ];
        } catch (\Exception $e) {
            Log::error('MapService getMapData Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat memproses data peta.', 'data' => []];
        }
    }

    /**
     * Get basic coordinates for a specific country by name.
     */
    public function getCountryCoordinates(string $countryName): ?array
    {
        try {
            // Optimasi: Hanya get kolom lat dan lng, gunakan query yang efisien
            $country = Country::select('latitude', 'longitude')
                ->where('name', 'like', "%{$countryName}%")
                ->first();
                
            if ($country && $country->latitude && $country->longitude) {
                return ['lat' => $country->latitude, 'lng' => $country->longitude];
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error("Error getCountryCoordinates ({$countryName}): " . $e->getMessage());
            return null;
        }
    }
}
