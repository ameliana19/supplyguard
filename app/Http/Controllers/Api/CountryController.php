<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends BaseApiController
{
    protected $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->query('search');
            
            // Redesign: Hanya retrieve data dari database local cache (tidak ada auto-sync di sini)
            $countries = Country::when($search, function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orderBy('name', 'asc')
                ->paginate(20);

            return $this->sendResponse($countries, 'Data negara berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to retrieve countries.', [$e->getMessage()], 500);
        }
    }

    public function sync(): JsonResponse
    {
        $result = $this->countryService->importFromAPI();
        if ($result['success']) {
            return $this->sendResponse(['processed_count' => $result['count']], $result['message']);
        }
        return $this->sendError($result['message'], [], 500);
    }
}
