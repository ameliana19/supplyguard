<?php

namespace App\Http\Controllers\Api;

use App\Services\MapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MapApiController extends BaseApiController
{
    /**
     * Service to handle Map data operations.
     */
    protected $mapService;

    public function __construct(MapService $mapService)
    {
        $this->mapService = $mapService;
    }

    /**
     * Retrieve map data (countries, ports, shipments) for rendering.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $result = $this->mapService->getMapData();
            
            if (isset($result['success']) && $result['success']) {
                return $this->sendResponse($result['data'] ?? [], 'Data peta berhasil diambil.');
            }
            
            return $this->sendError($result['message'] ?? 'Gagal mengambil data peta.', [], 500);
        } catch (\Exception $e) {
            Log::error('Error MapApiController@index: ' . $e->getMessage());
            return $this->sendError('Terjadi kesalahan sistem saat memuat data peta.', [], 500);
        }
    }
}
