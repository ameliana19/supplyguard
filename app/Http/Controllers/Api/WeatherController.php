<?php

namespace App\Http\Controllers\Api;

use App\Models\WeatherData;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends BaseApiController
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->query('search');
            
            // Redesign: Hanya retrieve data dari database local cache (tidak ada auto-sync di sini)
            $weather = WeatherData::with('country')
                ->when($search, function ($query) use ($search) {
                    $query->where('city', 'like', "%{$search}%")
                          ->orWhereHas('country', function($q) use ($search) {
                              $q->where('name', 'like', "%{$search}%");
                          });
                })
                ->latest()
                ->paginate(15);

            return $this->sendResponse($weather, 'Weather data retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to retrieve weather data.', [$e->getMessage()], 500);
        }
    }

    public function sync(): JsonResponse
    {
        $result = $this->weatherService->syncAll();
        if ($result['success']) {
            return $this->sendResponse(['synced_count' => $result['success_count']], $result['message']);
        }
        return $this->sendError($result['message'], [], 500);
    }
}
