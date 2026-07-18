<div class="sidebar d-flex flex-column">
    <div class="sidebar-logo">
        <i class="bi bi-shield-shaded text-primary fs-4"></i>
        <span>SupplyGuard</span>
    </div>

    <div class="sidebar-menu">
        <span class="sidebar-section-title">Ringkasan</span>
        <a href="{{ route('dashboard') }}" class="sidebar-item {{ Route::is('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        <span class="sidebar-section-title">Intelijen Utama</span>
        <a href="{{ route('countries.index') }}" class="sidebar-item {{ Request::is('countries*') ? 'active' : '' }}">
            <i class="bi bi-globe"></i>
            <span>Negara</span>
        </a>
        <a href="{{ route('weather.index') }}" class="sidebar-item {{ Request::is('weather*') ? 'active' : '' }}">
            <i class="bi bi-cloud-sun-fill"></i>
            <span>Cuaca</span>
        </a>
        <a href="{{ route('currency.index') }}" class="sidebar-item {{ Request::is('currency*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i>
            <span>Mata Uang</span>
        </a>
        <a href="{{ route('economy.index') }}" class="sidebar-item {{ Request::is('economy*') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow"></i>
            <span>Ekonomi</span>
        </a>

        <span class="sidebar-section-title">Logistik & Risiko</span>
        <a href="{{ route('ports.index') }}" class="sidebar-item {{ Request::is('ports*') ? 'active' : '' }}">
            <i class="bi bi-tsunami"></i>
            <span>Pelabuhan</span>
        </a>
        <a href="{{ route('risk-score.index') }}" class="sidebar-item {{ Request::is('risk-score*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Tingkat Risiko</span>
        </a>
        <a href="{{ route('compare.index') }}" class="sidebar-item {{ Request::is('compare*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i>
            <span>Perbandingan Negara</span>
        </a>

        <span class="sidebar-section-title">Pemantauan</span>
        <a href="{{ route('watchlist.index') }}" class="sidebar-item {{ Request::is('watchlist*') ? 'active' : '' }}">
            <i class="bi bi-star-fill"></i>
            <span>Daftar Pantauan</span>
        </a>
        <a href="{{ route('news.index') }}" class="sidebar-item {{ Request::is('news*') ? 'active' : '' }}">
            <i class="bi bi-newspaper"></i>
            <span>Berita</span>
        </a>

        <span class="sidebar-section-title">Peta & Perencanaan</span>
        <a href="{{ route('map.index') }}" class="sidebar-item {{ Route::is('map.index') ? 'active' : '' }}">
            <i class="bi bi-map-fill"></i>
            <span>Peta Dunia</span>
        </a>
        @if(Auth::user() && Auth::user()->role === 'administrator')
        <a href="{{ route('shipments.planner') }}" class="sidebar-item {{ Route::is('shipments.planner') ? 'active' : '' }}">
            <i class="bi bi-calendar-event"></i>
            <span>Perencanaan Pengiriman</span>
        </a>
        <a href="{{ route('shipments.history') }}" class="sidebar-item {{ Route::is('shipments.history') ? 'active' : '' }}">
            <i class="bi bi-truck"></i>
            <span>Riwayat Pengiriman</span>
        </a>
        @endif

        <span class="sidebar-section-title">Akun</span>
        @if(Auth::user() && Auth::user()->role === 'administrator')
        <a href="{{ route('admin.panel') }}" class="sidebar-item {{ Route::is('admin.panel') ? 'active' : '' }}">
            <i class="fas fa-user-shield"></i>
            <span>Admin Panel</span>
        </a>
        @endif
        <a href="{{ route('logout') }}" class="sidebar-item text-danger">
            <i class="bi bi-box-arrow-left"></i>
            <span>Keluar</span>
        </a>
    </div>
</div>