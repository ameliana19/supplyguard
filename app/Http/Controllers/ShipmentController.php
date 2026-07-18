<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\ShipmentHistory;
use App\Models\Country;
use App\Models\Port;
use App\Services\ShipmentService;
use Illuminate\Support\Facades\Log;

class ShipmentController extends Controller
{
    /**
     * Service to handle external shipment synchronization.
     */
    protected $shipmentService;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    /**
     * Display a paginated list of shipments.
     */
    public function history(Request $request)
    {
        try {
            // Trigger sync untuk memperbarui database cache dari API eksternal
            $this->shipmentService->syncShipments();
        } catch (\Exception $e) {
            Log::error('Error sync shipments at history: ' . $e->getMessage());
        }

        $search = $request->search;
        
        // Optimasi: Gunakan paginate() daripada get() untuk mencegah load data berlebih
        $shipments = Shipment::with(['originCountry', 'destinationCountry', 'originPort', 'destinationPort'])
            ->when($search, function ($query) use ($search) {
                $query->where('tracking_number', 'like', "%{$search}%")
                      ->orWhere('container_number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('shipments.history', compact('shipments'));
    }

    /**
     * Show the shipment planner with required form data.
     */
    public function planner()
    {
        try {
            $this->shipmentService->syncShipments();
        } catch (\Exception $e) {
            Log::error('Error sync shipments at planner: ' . $e->getMessage());
        }
        
        // Optimasi Query: Hanya ambil kolom yang relevan untuk dropdown
        $countries = Country::select('id', 'name')->orderBy('name', 'asc')->get();
        $ports = Port::with('country:id,name')->select('id', 'port_name', 'country_id')->get();

        return view('shipments.planner', compact('countries', 'ports'));
    }

    /**
     * Store a newly planned shipment in the database.
     */
    public function store(Request $request)
    {
        // Validasi ketat
        $request->validate([
            'origin_country_id'      => 'required|exists:countries,id',
            'destination_country_id' => 'required|exists:countries,id',
            'cargo_type'             => 'required|string|max:150',
            'weight'                 => 'required|numeric|min:0',
            'estimated_departure'    => 'required|date',
            'estimated_arrival'      => 'required|date|after:estimated_departure',
        ]);

        try {
            $trackingNumber = 'SG' . strtoupper(uniqid());
            $originCountry = Country::find($request->origin_country_id);

            $shipment = Shipment::create([
                'tracking_number'        => $trackingNumber,
                'container_number'       => 'CNT' . strtoupper(uniqid()),
                'cargo_type'             => $request->cargo_type,
                'origin_country_id'      => $request->origin_country_id,
                'destination_country_id' => $request->destination_country_id,
                'estimated_departure'    => $request->estimated_departure,
                'estimated_arrival'      => $request->estimated_arrival,
                'status'                 => 'Pending',
            ]);

            ShipmentHistory::create([
                'shipment_id' => $shipment->id,
                'status'      => 'Pending',
                'location'    => $originCountry->name ?? 'Unknown',
                'notes'       => 'Pengiriman dibuat',
                'event_time'  => now(),
            ]);

            return redirect()->route('shipments.history')
                ->with('success', "Pengiriman {$trackingNumber} berhasil dibuat.");
                
        } catch (\Exception $e) {
            Log::error('Error saat membuat pengiriman: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal membuat pengiriman.');
        }
    }

    /**
     * Display a specific shipment's details and history.
     */
    public function show($id)
    {
        try {
            $shipment = Shipment::with([
                'originCountry', 
                'destinationCountry', 
                'originPort', 
                'destinationPort', 
                'histories' => function ($query) {
                    $query->orderBy('event_time', 'desc');
                }
            ])->findOrFail($id);
            
            return view('shipments.show', compact('shipment'));
            
        } catch (\Exception $e) {
            Log::error('Shipment tidak ditemukan: ' . $e->getMessage());
            return redirect()->route('shipments.history')
                ->with('error', 'Data pengiriman tidak ditemukan.');
        }
    }

    /**
     * Update the status of a shipment and log the history.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'   => 'required|in:Pending,In Transit,Arrived,Delayed,Cancelled',
            'location' => 'nullable|string|max:255',
            'notes'    => 'nullable|string|max:500',
        ]);

        try {
            $shipment = Shipment::findOrFail($id);
            $shipment->update(['status' => $request->status]);

            ShipmentHistory::create([
                'shipment_id' => $shipment->id,
                'status'      => $request->status,
                'location'    => $request->location ?? 'Unknown',
                'notes'       => $request->notes ?? 'Status update',
                'event_time'  => now(),
            ]);

            return redirect()->route('shipments.show', $id)
                ->with('success', 'Status pengiriman berhasil diperbarui.');
                
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui status pengiriman: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui status pengiriman.');
        }
    }
}
