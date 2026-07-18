<?php

namespace App\Http\Controllers\Api;

use App\Models\Shipment;
use App\Models\ShipmentHistory;
use App\Models\ShipmentPlanner;
use App\Services\ShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShipmentController extends BaseApiController
{
    protected $shipmentService;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            // OTOMATIS SINKRONISASI API
            $this->shipmentService->syncShipments();

            $search = $request->query('search');
            $status = $request->query('status');
            $cargoType = $request->query('cargo_type');
            
            $query = Shipment::with(['originCountry', 'destinationCountry', 'originPort', 'destinationPort']);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('tracking_number', 'like', "%{$search}%")
                      ->orWhere('container_number', 'like', "%{$search}%")
                      ->orWhere('cargo_type', 'like', "%{$search}%");
                });
            }

            if ($status) $query->where('status', $status);
            if ($cargoType) $query->where('cargo_type', $cargoType);

            $shipments = $query->orderBy('estimated_departure', 'desc')->paginate(10);
            return $this->sendResponse($shipments, 'Data pengiriman berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil data pengiriman.', [$e->getMessage()], 500);
        }
    }

    public function planners(Request $request): JsonResponse
    {
        try {
            // OTOMATIS SINKRONISASI API
            $this->shipmentService->syncShipments();

            $start = $request->query('start');
            $end = $request->query('end');

            $planners = ShipmentPlanner::with('shipment.originCountry', 'shipment.destinationCountry')
                ->when($start, function($q) use ($start) {
                    $q->where('start_date', '>=', $start);
                })
                ->when($end, function($q) use ($end) {
                    $q->where('end_date', '<=', $end);
                })
                ->limit(200)
                ->get();

            return $this->sendResponse($planners, 'Data perencana berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil data perencana.', [$e->getMessage()], 500);
        }
    }

    public function history(string $tracking_number): JsonResponse
    {
        try {
            // OTOMATIS SINKRONISASI API
            $this->shipmentService->syncShipments();

            $shipment = Shipment::with(['originCountry', 'destinationCountry', 'originPort', 'destinationPort', 'histories'])
                ->where('tracking_number', $tracking_number)
                ->first();

            if (!$shipment) {
                return $this->sendError('Pengiriman tidak ditemukan.', [], 404);
            }

            return $this->sendResponse($shipment, 'Riwayat pengiriman berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil riwayat.', [$e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tracking_number' => 'required|string|unique:shipments,tracking_number',
                'container_number' => 'required|string',
                'cargo_type' => 'required|string',
                'origin_country_id' => 'required|exists:countries,id',
                'destination_country_id' => 'required|exists:countries,id',
                'estimated_departure' => 'required|date',
                'estimated_arrival' => 'required|date|after_or_equal:estimated_departure',
                'title' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Error Validasi.', $validator->errors()->toArray(), 422);
            }

            return DB::transaction(function() use ($request) {
                $shipment = Shipment::create([
                    'tracking_number' => $request->tracking_number,
                    'container_number' => $request->container_number,
                    'cargo_type' => $request->cargo_type,
                    'origin_country_id' => $request->origin_country_id,
                    'destination_country_id' => $request->destination_country_id,
                    'estimated_departure' => $request->estimated_departure,
                    'estimated_arrival' => $request->estimated_arrival,
                    'status' => 'Pending'
                ]);

                ShipmentPlanner::create([
                    'shipment_id' => $shipment->id,
                    'title' => $request->title,
                    'start_date' => $request->estimated_departure,
                    'end_date' => $request->estimated_arrival,
                    'description' => $request->description,
                ]);

                ShipmentHistory::create([
                    'shipment_id' => $shipment->id,
                    'status' => 'Pending',
                    'location' => 'Origin Depot',
                    'notes' => 'Shipment planning completed.',
                    'event_time' => now()
                ]);

                return $this->sendResponse($shipment->load('originCountry', 'destinationCountry'), 'Pengiriman berhasil direncanakan.', 201);
            });
        } catch (\Exception $e) {
            return $this->sendError('Gagal membuat rencana pengiriman.', [$e->getMessage()], 500);
        }
    }

    public function addHistory(Request $request, string $id): JsonResponse
    {
        try {
            $shipment = Shipment::find($id);
            if (!$shipment) return $this->sendError('Pengiriman tidak ditemukan.', [], 404);

            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:Pending,In Transit,Arrived,Delayed,Cancelled',
                'location' => 'required|string',
                'notes' => 'nullable|string',
                'event_time' => 'required|date'
            ]);

            if ($validator->fails()) return $this->sendError('Error Validasi.', $validator->errors()->toArray(), 422);

            return DB::transaction(function() use ($request, $shipment) {
                $shipment->update(['status' => $request->status]);

                $history = ShipmentHistory::create([
                    'shipment_id' => $shipment->id,
                    'status' => $request->status,
                    'location' => $request->location,
                    'notes' => $request->notes,
                    'event_time' => $request->event_time
                ]);

                return $this->sendResponse($history, 'Riwayat pelacakan pengiriman diperbarui.');
            });
        } catch (\Exception $e) {
            return $this->sendError('Gagal memperbarui log pelacakan.', [$e->getMessage()], 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $statusCounts = Shipment::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');
            $cargoCounts = Shipment::select('cargo_type', DB::raw('count(*) as total'))->groupBy('cargo_type')->orderBy('total', 'desc')->limit(5)->pluck('total', 'cargo_type');
            $delays = ShipmentHistory::where('status', 'Delayed')->select(DB::raw('DATE_FORMAT(event_time, "%Y-%m") as month'), DB::raw('count(*) as total'))->groupBy('month')->orderBy('month', 'asc')->limit(6)->pluck('total', 'month');

            return $this->sendResponse([
                'statuses' => $statusCounts,
                'cargos' => $cargoCounts,
                'delays' => $delays
            ], 'Statistik pengiriman berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil statistik pengiriman.', [$e->getMessage()], 500);
        }
    }
}
