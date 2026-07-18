<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SupplyGuard</title>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: #F9FAFB;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            color: #111827;
        }
        .login-card {
            border: 1px solid #E5E7EB;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            background-color: #ffffff;
        }
        .login-header {
            background-color: #1F2937;
            color: #ffffff;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .login-header h3 {
            color: #ffffff !important;
        }
        .login-header p {
            color: rgba(255, 255, 255, 0.85) !important;
        }
        .login-body {
            background-color: #ffffff;
            padding: 2.5rem 2.5rem;
        }
        .login-body .btn-primary {
            background-color: #D4AF37 !important;
            border-color: #D4AF37 !important;
            color: #ffffff !important;
            border-radius: 16px !important;
        }
        .login-body .btn-primary:hover {
            background-color: #B8860B !important;
            border-color: #B8860B !important;
        }
        .form-control {
            border: 1px solid #D1D5DB !important;
            background-color: #ffffff !important;
            border-radius: 16px !important;
            color: #111827 !important;
        }
        .form-control:focus {
            border-color: #D4AF37 !important;
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.2) !important;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <h3 class="mb-1 fw-bold">📦 SupplyGuard</h3>
        <p class="small text-muted mb-0">Sistem Pemantauan Risiko</p>
    </div>
    <div class="login-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <!-- Email Address -->
            <div class="mb-4">
                <label for="email" class="form-label fw-bold small text-muted">ALAMAT EMAIL</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" id="email" class="form-control bg-light border-start-0" placeholder="admin@example.com" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="form-label fw-bold small text-muted">KATA SANDI</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" id="password" class="form-control bg-light border-start-0" placeholder="••••••••" required>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="mb-4 form-check">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label class="form-check-label small text-muted" for="remember">Ingat saya di perangkat ini</label>
            </div>

            <!-- Submit Button -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg fw-bold py-2 shadow-sm">
                    Masuk <i class="bi bi-box-arrow-in-right ms-1"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
