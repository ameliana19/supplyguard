<?php

namespace App\Http\Controllers\Api;

use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends BaseApiController
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            // Redesign: Hanya retrieve data dari database local cache (tidak ada auto-sync di sini)
            $currencies = Currency::orderBy('rate', 'desc')->get();
            return $this->sendResponse($currencies, 'Data mata uang berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil data mata uang.', [$e->getMessage()], 500);
        }
    }

    public function sync(): JsonResponse
    {
        $result = $this->currencyService->syncRates();
        if ($result['success']) {
            return $this->sendResponse(['synced_count' => $result['count']], $result['message']);
        }
        return $this->sendError($result['message'], [], 500);
    }
}
