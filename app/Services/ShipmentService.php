<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\ShipmentPlanner;
use App\Models\ShipmentHistory;
use App\Models\Country;
use App\Models\Port;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    /**
     * Sync shipments from external API or compatible dataset
     */
    public function syncShipments(): array
    {
        try {
            // Memeriksa ketersediaan data dasar (Negara & Pelabuhan) sebelum sinkronisasi
            if (Country::count() === 0 || Port::count() === 0) {
                return [
                    'success' => false,
                    'message' => 'Sistem tidak dapat menyinkronkan pengiriman karena data Master Negara atau Pelabuhan belum tersedia.',
                    'count'   => 0
                ];
            }

            // Mencoba menarik data dari public API
            $response = Http::timeout(10)->get('https://raw.githubusercontent.com/ameliana19/SupplyGuard/main/shipments.json');
            
            $shipmentsData = [];
            if ($response->successful()) {
                $shipmentsData = $response->json();
            }

            // Fallback jika API eksternal gagal diakses
            if (empty($shipmentsData) || !is_array($shipmentsData)) {
                $shipmentsData = $this->getMockShipmentDataset();
                Log::info('Menggunakan Mock Shipment Dataset karena API gagal atau kosong.');
            }

            $count = 0;

            DB::transaction(function () use ($shipmentsData, &$count) {
                foreach ($shipmentsData as $data) {
                    $trackingNumber = $data['tracking_number'] ?? null;
                    if (!$trackingNumber) continue;

                    // Hubungkan dengan negara asal & tujuan
                    $originCountry = Country::where('code', $data['origin_country_code'])->first();
                    $destCountry = Country::where('code', $data['destination_country_code'])->first();

                    if (!$originCountry || !$destCountry) {
                        Log::warning("Country not found for shipment {$trackingNumber}");
                        continue;
                    }

                    // Hubungkan dengan pelabuhan asal & tujuan
                    $originPort = Port::where('port_code', $data['origin_port_code'])->first();
                    $destPort = Port::where('port_code', $data['destination_port_code'])->first();

                    // Perhitungan tanggal relatif real-time terhadap waktu hari ini
                    $departureDate = now()->addDays($data['departure_offset_days'] ?? 0);
                    $estimatedArrival = now()->addDays($data['arrival_offset_days'] ?? 0);

                    // Update atau buat data pengiriman
                    $shipment = Shipment::updateOrCreate(
                        ['tracking_number' => $trackingNumber],
                        [
                            'container_number'       => $data['container_number'] ?? 'CNT-UNKNOWN',
                            'cargo_type'             => $data['cargo_type'] ?? 'General Cargo',
                            'origin_country_id'      => $originCountry->id,
                            'destination_country_id' => $destCountry->id,
                            'origin_port_id'         => $originPort ? $originPort->id : null,
                            'destination_port_id'    => $destPort ? $destPort->id : null,
                            'estimated_departure'    => $departureDate,
                            'estimated_arrival'      => $estimatedArrival,
                            'status'                 => $data['status'] ?? 'Pending',
                        ]
                    );

                    // Update atau buat entri Planner Kalender
                    ShipmentPlanner::updateOrCreate(
                        ['shipment_id' => $shipment->id],
                        [
                            'title'       => "Pengiriman " . $shipment->cargo_type,
                            'start_date'  => $departureDate,
                            'end_date'    => $estimatedArrival,
                            'description' => "Vessel: " . ($data['vessel'] ?? '-') . "\nCarrier: " . ($data['shipping_company'] ?? '-'),
                        ]
                    );

                    // Bersihkan riwayat lama lalu simpan log perjalanan baru
                    ShipmentHistory::where('shipment_id', $shipment->id)->delete();

                    $histories = $data['histories'] ?? [];
                    foreach ($histories as $h) {
                        ShipmentHistory::create([
                            'shipment_id' => $shipment->id,
                            'status'      => $h['status'],
                            'location'    => $h['location'] . " (Vessel: " . ($data['vessel'] ?? '-') . ", Carrier: " . ($data['shipping_company'] ?? '-') . ")",
                            'notes'       => $h['notes'] ?? 'Status update.',
                            'event_time'  => now()->addDays($h['event_time_offset_days'] ?? 0),
                        ]);
                    }

                    $count++;
                }
            });

            return [
                'success' => true,
                'message' => "Berhasil menyinkronkan {$count} data pengiriman dari API.",
                'count'   => $count
            ];

        } catch (\Exception $e) {
            Log::error('ShipmentService Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menyinkronkan data pengiriman. Error: ' . $e->getMessage(),
                'count'   => 0
            ];
        }
    }

    /**
     * Dataset pengiriman utama cadangan
     */
    private function getMockShipmentDataset(): array
    {
        return [
            [
                'tracking_number' => 'SG-2026-001',
                'container_number' => 'MSKU9837492',
                'cargo_type' => 'Semiconductor Chipsets',
                'origin_country_code' => 'JP',
                'destination_country_code' => 'ID',
                'origin_port_code' => 'JPTYO',
                'destination_port_code' => 'IDJKT',
                'vessel' => 'Maersk McKinney Moller',
                'shipping_company' => 'Maersk Line',
                'status' => 'In Transit',
                'departure_offset_days' => -4,
                'arrival_offset_days' => 3,
                'histories' => [
                    ['status' => 'Pending', 'location' => 'Port of Tokyo', 'notes' => 'Container gate-in approved.', 'event_time_offset_days' => -5],
                    ['status' => 'In Transit', 'location' => 'East China Sea', 'notes' => 'Departed origin port. Sea condition: Stable.', 'event_time_offset_days' => -3],
                ]
            ],
            [
                'tracking_number' => 'SG-2026-002',
                'container_number' => 'MEDU8472930',
                'cargo_type' => 'Automotive Spare Parts',
                'origin_country_code' => 'CN',
                'destination_country_code' => 'MY',
                'origin_port_code' => 'CNSHA',
                'destination_port_code' => 'MYPKG',
                'vessel' => 'MSC Isabella',
                'shipping_company' => 'Mediterranean Shipping Company',
                'status' => 'Delayed',
                'departure_offset_days' => -6,
                'arrival_offset_days' => 5,
                'histories' => [
                    ['status' => 'Pending', 'location' => 'Port of Shanghai', 'notes' => 'Export customs cleared.', 'event_time_offset_days' => -7],
                    ['status' => 'In Transit', 'location' => 'South China Sea', 'notes' => 'Vessel departed. Route adjusted.', 'event_time_offset_days' => -5],
                    ['status' => 'Delayed', 'location' => 'South China Sea', 'notes' => 'Speed reduced due to local monsoon storm.', 'event_time_offset_days' => -1]
                ]
            ],
            [
                'tracking_number' => 'SG-2026-003',
                'container_number' => 'NYKU1983029',
                'cargo_type' => 'Medical Equipment',
                'origin_country_code' => 'US',
                'destination_country_code' => 'SG',
                'origin_port_code' => 'USLAX',
                'destination_port_code' => 'SGSIN',
                'vessel' => 'ONE Apus',
                'shipping_company' => 'Ocean Network Express',
                'status' => 'Arrived',
                'departure_offset_days' => -12,
                'arrival_offset_days' => -1,
                'histories' => [
                    ['status' => 'Pending', 'location' => 'Port of Los Angeles', 'notes' => 'Booked.', 'event_time_offset_days' => -13],
                    ['status' => 'In Transit', 'location' => 'Pacific Ocean', 'notes' => 'Cruising at 21 knots.', 'event_time_offset_days' => -8],
                    ['status' => 'Arrived', 'location' => 'Port of Singapore', 'notes' => 'Vessel berthed successfully. Cargo discharged.', 'event_time_offset_days' => -1]
                ]
            ],
        ];
    }
}
