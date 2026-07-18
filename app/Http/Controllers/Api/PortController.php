<?php

namespace App\Http\Controllers\Api;

use App\Models\Port;
use App\Services\PortService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortController extends BaseApiController
{
    protected $portService;

    public function __construct(PortService $portService)
    {
        $this->portService = $portService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->query('search');
            $status = $request->query('status');

            $ports = Port::mainPorts()->with('country')
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('port_name', 'like', "%{$search}%")
                          ->orWhere('port_code', 'like', "%{$search}%")
                          ->orWhere('city', 'like', "%{$search}%");
                    });
                })
                ->when($status, function ($query) use ($status) {
                    $query->where('status', $status);
                })
                ->orderBy('id', 'desc')
                ->paginate(15);

            return $this->sendResponse($ports, 'Data pelabuhan berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil data pelabuhan.', [$e->getMessage()], 500);
        }
    }

    public function sync(): JsonResponse
    {
        $result = $this->portService->syncPorts();
        if ($result['success']) {
            return $this->sendResponse(['synced_count' => $result['count']], $result['message']);
        }
        return $this->sendError($result['message'], [], 500);
    }
}
