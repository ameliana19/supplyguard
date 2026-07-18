@extends('layouts.app')

@section('title', 'Detail Berita')

@section('content')
<div class="container py-4">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('news.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Berita
        </a>
    </div>

    @if($article)
        <!-- Article Header -->
        <div class="row mb-4">
            <div class="col-12">
                <span class="badge bg-primary mb-2">{{ $article->category }}</span>
                <h1 class="display-5 fw-bold mb-3">{{ $article->title_id ?? $article->title }}</h1>
                <div class="d-flex flex-wrap gap-3 text-muted mb-3">
                    <div>
                        <i class="bi bi-person me-1"></i>
                        <span>{{ $article->author ?? 'Anonim' }}</span>
                    </div>
                    <div>
                        <i class="bi bi-calendar me-1"></i>
                        <span>{{ \Carbon\Carbon::parse($article->published_at)->locale('id')->translatedFormat('d F Y') }}</span>
                    </div>
                    <div>
                        <i class="bi bi-newspaper me-1"></i>
                        <span>{{ $article->source ?? 'News Network' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Article Image -->
        @if($article->image)
        <div class="row mb-4">
            <div class="col-12">
                <img src="{{ $article->image }}" alt="{{ $article->title }}" class="img-fluid rounded shadow w-100" style="max-height: 500px; object-fit: cover;">
            </div>
        </div>
        @endif

        <!-- Article Content -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <!-- Summary -->
                        @if($article->summary_id)
                        <div class="lead mb-4 text-muted">
                            {{ $article->summary_id }}
                        </div>
                        @elseif($article->summary)
                        <div class="lead mb-4 text-muted">
                            {{ $article->summary }}
                        </div>
                        @endif

                        <!-- Content -->
                        @if($article->content)
                        <div class="article-content">
                            {!! nl2br(e($article->content)) !!}
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Konten lengkap tidak tersedia. Silakan baca berita asli untuk informasi lebih lanjut.
                        </div>
                        @endif

                        <!-- Original Title (for reference) -->
                        @if($article->title_id && $article->title !== $article->title_id)
                        <div class="mt-4 pt-4 border-top">
                            <h6 class="text-muted mb-2">Judul Asli:</h6>
                            <p class="text-muted">{{ $article->title }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 mt-4">
                    @if($article->url)
                    <a href="{{ $article->url }}" target="_blank" class="btn btn-primary">
                        <i class="bi bi-box-arrow-up-right me-2"></i>Baca Berita Asli
                    </a>
                    @endif
                    <a href="{{ route('news.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0">Informasi Berita</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <small class="text-muted d-block">Kategori</small>
                                <span class="badge bg-primary">{{ $article->category }}</span>
                            </li>
                            <li class="mb-3">
                                <small class="text-muted d-block">Penulis</small>
                                <span>{{ $article->author ?? 'Anonim' }}</span>
                            </li>
                            <li class="mb-3">
                                <small class="text-muted d-block">Sumber</small>
                                <span>{{ $article->source ?? 'News Network' }}</span>
                            </li>
                            <li class="mb-3">
                                <small class="text-muted d-block">Tanggal Terbit</small>
                                <span>{{ \Carbon\Carbon::parse($article->published_at)->locale('id')->translatedFormat('d F Y') }}</span>
                            </li>
                            <li>
                                <small class="text-muted d-block">Diperbarui</small>
                                <span>{{ \Carbon\Carbon::parse($article->updated_at)->locale('id')->translatedFormat('d F Y H:i') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Related News -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0">Berita Terkait</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-0">
                            Fitur berita terkait akan segera tersedia.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Berita tidak ditemukan.
        </div>
        <a href="{{ route('news.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Berita
        </a>
    @endif
</div>

<style>
.article-content {
    line-height: 1.8;
    font-size: 1.1rem;
}

.article-content p {
    margin-bottom: 1.5rem;
}
</style>
@endsection
