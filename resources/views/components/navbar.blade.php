<nav class="navbar navbar-expand-lg navbar-light main-navbar shadow-sm sticky-top">
    <div class="container-fluid">
        <!-- Dashboard title or path descriptor -->
        <span class="navbar-brand mb-0 h1 fs-5 d-flex align-items-center gap-2">
            <i class="bi bi-shield-check text-primary"></i> Pengendalian Risiko Rantai Pasok Global
        </span>

        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="ms-auto d-flex align-items-center gap-3">
                <!-- Session User Profile -->
                @auth
                    <div class="dropdown">
                        <a class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle text-dark" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(Auth::user()->profile && Auth::user()->profile->photo)
                                <img src="{{ Auth::user()->profile->photo }}" alt="Avatar" width="32" height="32" class="rounded-circle border border-2 border-primary">
                            @else
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 600; font-size: 0.85rem;">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                </div>
                            @endif
                            <div class="d-none d-sm-block text-start">
                                <div class="fw-semibold lh-1" style="font-size: 0.85rem;">{{ Auth::user()->name }}</div>
                                <small class="text-muted" style="font-size: 0.7rem;">{{ Auth::user()->profile?->role ?? 'Administrator' }}</small>
                            </div>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.panel') }}">
                                    <i class="bi bi-person text-muted"></i> Admin Panel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('watchlist.index') }}">
                                    <i class="bi bi-star text-muted"></i> Daftar Pantauan
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="{{ route('logout') }}">
                                    <i class="bi bi-box-arrow-right"></i> Keluar
                                </a>
                            </li>
                        </ul>
                    </div>
                @else
                    <!-- Temporarily show admin user when auth is disabled -->
                    <div class="dropdown">
                        <a class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle text-dark" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: 600; font-size: 0.85rem;">
                                AD
                            </div>
                            <div class="d-none d-sm-block text-start">
                                <div class="fw-semibold lh-1" style="font-size: 0.85rem;">Admin</div>
                                <small class="text-muted" style="font-size: 0.7rem;">Administrator</small>
                            </div>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.panel') }}">
                                    <i class="bi bi-person text-muted"></i> Admin Panel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('watchlist.index') }}">
                                    <i class="bi bi-star text-muted"></i> Daftar Pantauan
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="{{ route('logout') }}">
                                    <i class="bi bi-box-arrow-right"></i> Keluar
                                </a>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>