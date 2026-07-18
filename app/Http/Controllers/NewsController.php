<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Country;
use App\Services\NewsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    /**
     * Service to handle News API operations.
     */
    protected $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

    /**
     * Display a listing of the news articles.
     */
    public function index(Request $request)
    {
        $query = Article::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('title_id', 'like', '%' . $request->search . '%')
                  ->orWhere('summary', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        // Optimasi Query: Menggunakan paginate alih-alih get() untuk mencegah overload memori
        $news = $query->latest('published_at')->paginate(15)->withQueryString();
        
        // Optimasi Query: Hanya ambil kolom yang dibutuhkan untuk dropdown
        $countries = Country::where('name', 'not like', 'Country %')
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return view('news.index', compact('news', 'countries'));
    }

    /**
     * Show the form for creating a new news article.
     */
    public function create()
    {
        return view('news.create');
    }

    /**
     * Store a newly created news article in storage.
     */
    public function store(Request $request)
    {
        // Validasi input data berita
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'category'     => 'required|string|max:100',
            'published_at' => 'required|date'
        ]);

        try {
            Article::create($validated);

            return redirect()->route('news.index')
                ->with('success', 'Berhasil menambahkan berita.');
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan berita: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan berita.');
        }
    }

    /**
     * Display the specified news article.
     */
    public function show($id)
    {
        try {
            $article = Article::findOrFail($id);
            return view('news.show', compact('article'));
        } catch (\Exception $e) {
            Log::error('Berita tidak ditemukan: ' . $e->getMessage());
            return redirect()->route('news.index')
                ->with('error', 'Berita tidak ditemukan.');
        }
    }

    /**
     * Show the form for editing the specified news article.
     */
    public function edit($id)
    {
        try {
            $item = Article::findOrFail($id);
            return view('news.edit', compact('item'));
        } catch (\Exception $e) {
            Log::error('Berita tidak ditemukan: ' . $e->getMessage());
            return redirect()->route('news.index')
                ->with('error', 'Berita tidak ditemukan.');
        }
    }

    /**
     * Update the specified news article in storage.
     */
    public function update(Request $request, $id)
    {
        // Validasi pembaruan data berita
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'category'     => 'required|string|max:100',
            'published_at' => 'required|date'
        ]);

        try {
            $article = Article::findOrFail($id);
            $article->update($validated);

            return redirect()->route('news.index')
                ->with('success', 'Berhasil memperbarui berita.');
        } catch (\Exception $e) {
            Log::error('Error saat memperbarui berita: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui berita.');
        }
    }

    /**
     * Remove the specified news article from storage.
     */
    public function destroy($id)
    {
        try {
            $article = Article::findOrFail($id);
            $article->delete();
            
            return redirect()->route('news.index')
                ->with('success', 'Berhasil menghapus berita.');
        } catch (\Exception $e) {
            Log::error('Error saat menghapus berita: ' . $e->getMessage());
            return redirect()->route('news.index')
                ->with('error', 'Gagal menghapus berita.');
        }
    }

    // ============================================
    // SYNC BERITA DARI NEWSAPI (FIXED)
    // ============================================
    
    /**
     * Synchronize news data from external News API.
     */
    public function sync()
    {
        Log::info('Sync berita via Web Controller dijalankan');

        try {
            $result = $this->newsService->syncNews();

            if (isset($result['success']) && $result['success']) {
                return redirect()->route('news.index')
                    ->with('success', $result['message'] ?? 'Berita berhasil disinkronkan.');
            }

            return redirect()->route('news.index')
                ->with('error', $result['message'] ?? 'Gagal menyinkronkan berita dari API.');

        } catch (\Exception $e) {
            Log::error('Exception saat sinkronisasi berita: ' . $e->getMessage());
            return redirect()->route('news.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghubungi News API.');
        }
    }
}