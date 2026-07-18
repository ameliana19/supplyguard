<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use App\Services\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends BaseApiController
{
    protected $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->query('search');
            $category = $request->query('category');

            $query = Article::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('title_id', 'like', "%{$search}%")
                      ->orWhere('summary', 'like', "%{$search}%");
                });
            }
            if ($category) $query->where('category', $category);

            // Redesign: Hanya retrieve data dari database local cache (tidak ada auto-sync di sini)
            // Serta kirimkan seluruh artikel dibungkus key 'data' agar local JS pagination memuat seutuhnya
            $news = $query->with('country')->orderBy('published_at', 'desc')->get();
            
            // Deduplicate on backend by unique combination of url and title to be absolutely safe
            $news = $news->unique(function ($item) {
                $title = strtolower(trim($item->title_id ?: $item->title));
                $url = strtolower(trim($item->url));
                return $url . '|' . $title;
            })->values();
            
            return $this->sendResponse(['data' => $news], 'Berita berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil berita.', [$e->getMessage()], 500);
        }
    }

    public function sync(Request $request): JsonResponse
    {
        $result = $this->newsService->syncNews();
        if ($result['success']) {
            return $this->sendResponse(['processed_count' => $result['count']], $result['message']);
        }
        return $this->sendError($result['message'], [], 500);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:250',
                'title_id' => 'nullable|string|max:250',
                'summary' => 'required|string',
                'summary_id' => 'nullable|string',
                'content' => 'nullable|string',
                'category' => 'required|string',
                'author' => 'nullable|string|max:250',
                'published_at' => 'required|date',
                'country_id' => 'required|exists:countries,id',
                'image' => 'nullable|string',
                'image_url' => 'nullable|string',
                'url' => 'nullable|string|max:1000',
            ]);

            if (empty($validated['title_id'])) {
                $validated['title_id'] = $validated['title'];
            }
            if (empty($validated['summary_id'])) {
                $validated['summary_id'] = $validated['summary'];
            }
            if (empty($validated['url'])) {
                $validated['url'] = 'https://supplyguard.com/news/user-' . uniqid();
            }

            $article = Article::create($validated);
            return $this->sendResponse($article, 'Berita berhasil ditambahkan.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal menambahkan berita.', [$e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $article = Article::with('country')->findOrFail($id);
            return $this->sendResponse($article, 'Berita berhasil ditemukan.');
        } catch (\Exception $e) {
            return $this->sendError('Berita tidak ditemukan.', [$e->getMessage()], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $article = Article::findOrFail($id);
            $validated = $request->validate([
                'title' => 'required|string|max:250',
                'title_id' => 'nullable|string|max:250',
                'summary' => 'required|string',
                'summary_id' => 'nullable|string',
                'content' => 'nullable|string',
                'category' => 'required|string',
                'author' => 'nullable|string|max:250',
                'published_at' => 'required|date',
                'country_id' => 'required|exists:countries,id',
                'image' => 'nullable|string',
                'image_url' => 'nullable|string',
                'url' => 'nullable|string|max:1000',
            ]);

            if (empty($validated['title_id'])) {
                $validated['title_id'] = $validated['title'];
            }
            if (empty($validated['summary_id'])) {
                $validated['summary_id'] = $validated['summary'];
            }

            $article->update($validated);
            return $this->sendResponse($article, 'Berita berhasil diperbarui.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal memperbarui berita.', [$e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $article = Article::findOrFail($id);
            $article->delete();
            return $this->sendResponse(null, 'Berita berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal menghapus berita.', [$e->getMessage()], 500);
        }
    }
}
