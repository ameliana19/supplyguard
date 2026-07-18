<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Country;
use App\Services\NewsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsController extends Controller
{
    protected $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

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

        // Redesign: Hanya retrieve data dari database local cache (tidak ada auto-sync di sini)
        $news = $query->latest()->get();
        $countries = Country::where('name', 'not like', 'Country %')->orderBy('name', 'asc')->get();

        return view('news.index', compact('news', 'countries'));
    }

    public function create()
    {
        return view('news.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'category' => 'required',
            'published_at' => 'required'
        ]);

        Article::create($request->only([
            'title',
            'category',
            'published_at'
        ]));

        return redirect()->route('news.index')
            ->with('success', 'Berhasil menambah berita');
    }

    public function edit($id)
    {
        $item = Article::findOrFail($id);
        return view('news.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);
        $article->update($request->only([
            'title',
            'category',
            'published_at'
        ]));

        return redirect()->route('news.index')
            ->with('success', 'Berhasil update berita');
    }

    public function destroy($id)
    {
        Article::destroy($id);
        return redirect()->route('news.index')
            ->with('success', 'Berhasil hapus berita');
    }

    public function show($id)
    {
        $article = Article::findOrFail($id);
        return view('news.show', compact('article'));
    }

    // ============================================
    // SYNC BERITA DARI NEWSAPI (FIXED)
    // ============================================
    public function sync()
    {
        Log::info('Sync berita via Web Controller dijalankan');

        $result = $this->newsService->syncNews();

        if ($result['success']) {
            return redirect()->route('news.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('news.index')
            ->with('error', $result['message']);
    }
}