<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Port;
use App\Models\Shipment;
use Illuminate\Support\Facades\Log;

class MapService
{
    public function getMapData(): array
    {
        try {
            $countriesList = Country::with('latestRiskScore')
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

            $portsList = Port::mainPorts()
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

            $shipments = Shipment::with(['originCountry', 'destinationCountry', 'originPort', 'destinationPort'])
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
                    'shipments' => $shipments,
                ]
            ];
        } catch (\Exception $e) {
            Log::error('MapService getMapData Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'data' => []];
        }
    }

    public function getCountryCoordinates(string $countryName): ?array
    {
        try {
            $country = Country::where('name', 'like', "%{$countryName}%")->first();
            // FIXED COORDINATES
            if ($country && $country->latitude && $country->longitude) {
                return ['lat' => $country->latitude, 'lng' => $country->longitude];
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
