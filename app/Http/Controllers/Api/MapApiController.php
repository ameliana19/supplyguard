<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use App\Models\Port;
use App\Models\Shipment;
use App\Services\MapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapApiController extends BaseApiController
{
    protected $mapService;

    public function __construct(MapService $mapService)
    {
        $this->mapService = $mapService;
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->mapService->getMapData();
        
        if ($result['success']) {
            return $this->sendResponse($result['data'], 'Data peta berhasil diambil.');
        }
        
        return $this->sendError($result['message'], [], 500);
    }
}
