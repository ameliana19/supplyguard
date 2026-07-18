<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\ShipmentHistory;
use App\Models\Country;
use App\Models\Port;
use App\Services\ShipmentService;

class ShipmentController extends Controller
{
    protected $shipmentService;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    public function history(Request $request)
    {
        // Triger sync untuk memperbarui database cache
        $this->shipmentService->syncShipments();

        $search = $request->search;
        $shipments = Shipment::with(['originCountry', 'destinationCountry', 'originPort', 'destinationPort'])
            ->when($search, function ($query) use ($search) {
                $query->where('tracking_number', 'like', "%{$search}%")
                      ->orWhere('container_number', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        return view('shipments.history', compact('shipments'));
    }

    public function planner()
    {
        $this->shipmentService->syncShipments();
        $countries = Country::orderBy('name')->get();
        $ports = Port::with('country')->get();

        return view('shipments.planner', compact('countries', 'ports'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'origin_country_id' => 'required|exists:countries,id',
            'destination_country_id' => 'required|exists:countries,id',
            'cargo_type' => 'required|string|max:100',
            'weight' => 'required|numeric|min:0',
            'estimated_departure' => 'required|date',
            'estimated_arrival' => 'required|date|after:estimated_departure',
        ]);

        $trackingNumber = 'SG' . strtoupper(uniqid());
        $originCountry = Country::find($request->origin_country_id);

        $shipment = Shipment::create([
            'tracking_number' => $trackingNumber,
            'container_number' => 'CNT' . strtoupper(uniqid()),
            'cargo_type' => $request->cargo_type,
            'origin_country_id' => $request->origin_country_id,
            'destination_country_id' => $request->destination_country_id,
            'estimated_departure' => $request->estimated_departure,
            'estimated_arrival' => $request->estimated_arrival,
            'status' => 'Pending',
        ]);

        ShipmentHistory::create([
            'shipment_id' => $shipment->id,
            'status' => 'Pending',
            'location' => $originCountry->name ?? 'Unknown',
            'notes' => 'Pengiriman dibuat',
            'event_time' => now(),
        ]);

        return redirect()->route('shipments.history')->with('success', "Pengiriman {$trackingNumber} berhasil dibuat.");
    }

    public function show($id)
    {
        $shipment = Shipment::with(['originCountry', 'destinationCountry', 'originPort', 'destinationPort', 'histories'])->findOrFail($id);
        return view('shipments.show', compact('shipment'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,In Transit,Arrived,Delayed,Cancelled',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $shipment = Shipment::findOrFail($id);
        $shipment->update(['status' => $request->status]);

        ShipmentHistory::create([
            'shipment_id' => $shipment->id,
            'status' => $request->status,
            'location' => $request->location,
            'notes' => $request->notes,
            'event_time' => now(),
        ]);

        return redirect()->route('shipments.show', $id)->with('success', 'Status pengiriman berhasil diperbarui.');
    }
}
