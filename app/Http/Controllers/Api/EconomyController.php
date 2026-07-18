<?php

namespace App\Http\Controllers\Api;

use App\Models\Economy;
use App\Services\EconomyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EconomyController extends BaseApiController
{
    protected $economyService;

    public function __construct(EconomyService $economyService)
    {
        $this->economyService = $economyService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->query('search');
            $year = $request->query('year');

            if ($year) {
                $query = Economy::with('country')
                    ->where('year', $year);
            } else {
                $subquery = DB::table('economic_data')
                    ->select('country_id', DB::raw('MAX(year) as max_year'))
                    ->groupBy('country_id');

                $query = Economy::joinSub($subquery, 'latest_economy', function ($join) {
                    $join->on('economic_data.country_id', '=', 'latest_economy.country_id')
                         ->on('economic_data.year', '=', 'latest_economy.max_year');
                })
                ->with('country')
                ->select('economic_data.*');
            }

            if ($search) {
                $query->whereHas('country', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $economies = $query->orderBy('gdp', 'desc')->paginate(15);
            return $this->sendResponse($economies, 'Data ekonomi berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil data ekonomi.', [$e->getMessage()], 500);
        }
    }

    public function sync(): JsonResponse
    {
        $result = $this->economyService->syncAll();
        if ($result['success']) {
            return $this->sendResponse(['synced_countries_count' => $result['success_count']], $result['message']);
        }
        
        // Fallback: Jika API gagal, gunakan data lokal tanpa melempar error halaman
        return $this->sendResponse(
            ['synced_countries_count' => 0],
            'Gagal menyinkronkan data terbaru dari API Bank Dunia. Sistem menggunakan data lokal yang sudah ada.'
        );
    }
}
