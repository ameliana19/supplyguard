<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SupplyGuard - Supply Chain Risk Intelligence Platform</title>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        base: '#000000',
                        surface: '#0A0A0A',
                        surfaceHover: '#171717',
                        borderSubtle: 'rgba(255, 255, 255, 0.1)',
                        brand: '#D4AF37', 
                        brandHover: '#B8860B',
                        brandLight: '#FDE047'
                    },
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #000000;
            color: #EDEDED;
            overflow-x: hidden;
        }
        ::selection {
            background: rgba(212, 175, 55, 0.3);
            color: white;
        }
        .text-gradient {
            background: linear-gradient(to right, #ffffff, #a3a3a3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .text-gradient-brand {
            background: linear-gradient(to right, #D4AF37, #FDE047);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .glass-nav {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .premium-card {
            background: linear-gradient(145deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.4s ease;
        }
        .premium-card:hover {
            border-color: rgba(212, 175, 55, 0.4);
            transform: translateY(-4px);
            background: linear-gradient(145deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
            box-shadow: 0 10px 40px -10px rgba(212, 175, 55, 0.15);
        }
        .mockup-window {
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: #0A0A0A;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 1), 0 0 0 1px rgba(255,255,255,0.1) inset;
        }
        .bg-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.15) 0%, rgba(0,0,0,0) 70%);
            top: -200px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 0;
            pointer-events: none;
        }
    </style>
</head>
<body class="font-sans antialiased selection:bg-brand/30 selection:text-white">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 glass-nav transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0 flex items-center gap-3 cursor-pointer">
                    <div class="w-8 h-8 rounded bg-gradient-to-br from-brand to-brandHover flex items-center justify-center shadow-lg shadow-brand/20">
                        <i class="bi bi-box-seam text-base font-bold text-black"></i>
                    </div>
                    <span class="font-bold text-lg tracking-tight text-white">SupplyGuard</span>
                </div>
                <div class="hidden md:flex space-x-8 text-sm font-medium">
                    <a href="#beranda" class="text-gray-400 hover:text-white transition-colors duration-200">Beranda</a>
                    <a href="#fitur" class="text-gray-400 hover:text-white transition-colors duration-200">Fitur</a>
                    <a href="#dashboard" class="text-gray-400 hover:text-white transition-colors duration-200">Dashboard</a>
                    <a href="#kontak" class="text-gray-400 hover:text-white transition-colors duration-200">Kontak</a>
                </div>
                <div class="hidden md:flex items-center gap-4">
                    <a href="{{ route('login') }}" class="bg-white hover:bg-gray-200 text-black px-4 py-2 rounded-md text-sm font-semibold transition-all duration-200 shadow-[0_0_15px_rgba(255,255,255,0.2)] hover:shadow-[0_0_20px_rgba(255,255,255,0.4)]">
                        Masuk <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="beranda" class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden flex flex-col items-center text-center border-b border-white/10">
        <div class="bg-glow"></div>
        <div class="max-w-5xl mx-auto px-6 relative z-10">
            <!-- Badge -->
            <div data-aos="fade-up" class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-brand/30 bg-brand/10 text-brand text-xs font-semibold tracking-wide mb-8">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-brand"></span>
                </span>
                Rancang Bangun Website Terintegrasi Multi API
            </div>
            
            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tighter mb-6 leading-[1.1]" data-aos="fade-up" data-aos-delay="100">
                <span class="text-gradient">SupplyGuard</span><br/>
                <span class="text-gradient-brand text-4xl md:text-5xl">Supply Chain Risk Intelligence Platform</span>
            </h1>
            
            <p class="mt-6 text-lg md:text-xl text-gray-400 max-w-3xl mx-auto mb-10 leading-relaxed font-light" data-aos="fade-up" data-aos-delay="200">
                SupplyGuard adalah website berbasis Framework Laravel dengan integrasi Multi API yang digunakan untuk memantau risiko rantai pasok global secara real-time. Sistem ini mengintegrasikan data negara, cuaca, ekonomi, nilai tukar mata uang, pelabuhan, berita, dan peta dunia untuk membantu analisis risiko supply chain.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="fade-up" data-aos-delay="300">
                <a href="{{ route('login') }}" class="bg-white hover:bg-gray-200 text-black px-8 py-3.5 rounded-lg text-sm font-semibold transition-all duration-300 shadow-[0_0_20px_rgba(255,255,255,0.15)] hover:shadow-[0_0_30px_rgba(255,255,255,0.3)]">
                    Masuk
                </a>
                <a href="#fitur" class="px-8 py-3.5 rounded-lg text-sm font-medium border border-white/10 hover:border-white/20 hover:bg-white/5 transition-all duration-300">
                    Lihat Fitur
                </a>
            </div>

            <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto pt-10 border-t border-white/10" data-aos="fade-up" data-aos-delay="400">
                <div>
                    <h3 class="text-2xl font-bold text-white tracking-tighter">250+</h3>
                    <p class="text-gray-400 mt-1 text-sm">Negara Dipantau</p>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-white tracking-tighter">6</h3>
                    <p class="text-gray-400 mt-1 text-sm">Integrasi API</p>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-white tracking-tighter">Real-Time</h3>
                    <p class="text-gray-400 mt-1 text-sm">Monitoring</p>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-white tracking-tighter">4</h3>
                    <p class="text-gray-400 mt-1 text-sm">Kategori Risiko</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Platform Features -->
    <section id="fitur" class="py-24 bg-surface relative">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="mb-16 text-center" data-aos="fade-up">
                <h2 class="text-brand font-semibold tracking-widest uppercase text-xs mb-3">Fitur Utama</h2>
                <h3 class="text-3xl md:text-4xl font-bold tracking-tight text-gradient inline-block">Sistem Pemantauan Risiko Rantai Pasok</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="premium-card p-8 rounded-2xl group" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-12 h-12 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center mb-6 group-hover:bg-brand/10 group-hover:border-brand/30 transition-all duration-300">
                        <i class="bi bi-shield-check text-xl text-gray-400 group-hover:text-brand transition-colors"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-white mb-2 tracking-tight">Risk Score Engine</h4>
                    <p class="text-sm text-gray-400 leading-relaxed">Analisis tingkat risiko setiap negara secara otomatis.</p>
                </div>
                <!-- Card 2 -->
                <div class="premium-card p-8 rounded-2xl group" data-aos="fade-up" data-aos-delay="150">
                    <div class="w-12 h-12 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center mb-6 group-hover:bg-brand/10 group-hover:border-brand/30 transition-all duration-300">
                        <i class="bi bi-cloud-lightning-rain text-xl text-gray-400 group-hover:text-brand transition-colors"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-white mb-2 tracking-tight">Weather Monitoring</h4>
                    <p class="text-sm text-gray-400 leading-relaxed">Pemantauan cuaca global secara real-time.</p>
                </div>
                <!-- Card 3 -->
                <div class="premium-card p-8 rounded-2xl group" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-12 h-12 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center mb-6 group-hover:bg-brand/10 group-hover:border-brand/30 transition-all duration-300">
                        <i class="bi bi-graph-up-arrow text-xl text-gray-400 group-hover:text-brand transition-colors"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-white mb-2 tracking-tight">Economic Intelligence</h4>
                    <p class="text-sm text-gray-400 leading-relaxed">Informasi GDP, inflasi, populasi, dan indikator ekonomi.</p>
                </div>
                <!-- Card 4 -->
                <div class="premium-card p-8 rounded-2xl group" data-aos="fade-up" data-aos-delay="250">
                    <div class="w-12 h-12 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center mb-6 group-hover:bg-brand/10 group-hover:border-brand/30 transition-all duration-300">
                        <i class="bi bi-currency-exchange text-xl text-gray-400 group-hover:text-brand transition-colors"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-white mb-2 tracking-tight">Currency Tracking</h4>
                    <p class="text-sm text-gray-400 leading-relaxed">Pemantauan nilai tukar mata uang internasional.</p>
                </div>
                <!-- Card 5 -->
                <div class="premium-card p-8 rounded-2xl group" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-12 h-12 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center mb-6 group-hover:bg-brand/10 group-hover:border-brand/30 transition-all duration-300">
                        <i class="bi bi-water text-xl text-gray-400 group-hover:text-brand transition-colors"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-white mb-2 tracking-tight">Port Intelligence</h4>
                    <p class="text-sm text-gray-400 leading-relaxed">Informasi pelabuhan dan aktivitas logistik.</p>
                </div>
                <!-- Card 6 -->
                <div class="premium-card p-8 rounded-2xl group" data-aos="fade-up" data-aos-delay="350">
                    <div class="w-12 h-12 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center mb-6 group-hover:bg-brand/10 group-hover:border-brand/30 transition-all duration-300">
                        <i class="bi bi-newspaper text-xl text-gray-400 group-hover:text-brand transition-colors"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-white mb-2 tracking-tight">News Monitoring</h4>
                    <p class="text-sm text-gray-400 leading-relaxed">Berita global yang berkaitan dengan supply chain.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Live APIs -->
    <section class="py-24 bg-base relative border-t border-white/5">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6" data-aos="fade-up">
                <div>
                    <h2 class="text-brand font-semibold tracking-widest uppercase text-xs mb-3">Integrasi API</h2>
                    <h3 class="text-3xl font-bold tracking-tight text-white">Sumber Data Real-Time</h3>
                </div>
                <p class="text-gray-400 max-w-md text-sm md:text-right">Sistem ini mengumpulkan data dari berbagai penyedia layanan pihak ketiga yang terpercaya.</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-6">
                <!-- API Cards -->
                <div class="premium-card p-6 md:p-8 rounded-xl text-center relative overflow-hidden group" data-aos="fade-up" data-aos-delay="100">
                    <div class="absolute top-4 right-4 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 shadow-[0_0_8px_#22c55e] animate-pulse"></span>
                        <span class="text-[9px] font-bold text-gray-400 tracking-wider">LIVE</span>
                    </div>
                    <i class="bi bi-globe-americas text-4xl md:text-5xl text-gray-500 mb-5 block group-hover:text-white transition-colors duration-300"></i>
                    <h4 class="font-semibold text-white tracking-tight">REST Countries</h4>
                </div>
                <div class="premium-card p-6 md:p-8 rounded-xl text-center relative overflow-hidden group" data-aos="fade-up" data-aos-delay="150">
                    <div class="absolute top-4 right-4 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 shadow-[0_0_8px_#22c55e] animate-pulse"></span>
                        <span class="text-[9px] font-bold text-gray-400 tracking-wider">LIVE</span>
                    </div>
                    <i class="bi bi-cloud-sun text-4xl md:text-5xl text-gray-500 mb-5 block group-hover:text-white transition-colors duration-300"></i>
                    <h4 class="font-semibold text-white tracking-tight">Open-Meteo</h4>
                </div>
                <div class="premium-card p-6 md:p-8 rounded-xl text-center relative overflow-hidden group" data-aos="fade-up" data-aos-delay="200">
                    <div class="absolute top-4 right-4 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 shadow-[0_0_8px_#22c55e] animate-pulse"></span>
                        <span class="text-[9px] font-bold text-gray-400 tracking-wider">LIVE</span>
                    </div>
                    <i class="bi bi-bank text-4xl md:text-5xl text-gray-500 mb-5 block group-hover:text-white transition-colors duration-300"></i>
                    <h4 class="font-semibold text-white tracking-tight">World Bank API</h4>
                </div>
                <div class="premium-card p-6 md:p-8 rounded-xl text-center relative overflow-hidden group" data-aos="fade-up" data-aos-delay="250">
                    <div class="absolute top-4 right-4 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 shadow-[0_0_8px_#22c55e] animate-pulse"></span>
                        <span class="text-[9px] font-bold text-gray-400 tracking-wider">LIVE</span>
                    </div>
                    <i class="bi bi-currency-dollar text-4xl md:text-5xl text-gray-500 mb-5 block group-hover:text-white transition-colors duration-300"></i>
                    <h4 class="font-semibold text-white tracking-tight">ExchangeRate API</h4>
                </div>
                <div class="premium-card p-6 md:p-8 rounded-xl text-center relative overflow-hidden group" data-aos="fade-up" data-aos-delay="300">
                    <div class="absolute top-4 right-4 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 shadow-[0_0_8px_#22c55e] animate-pulse"></span>
                        <span class="text-[9px] font-bold text-gray-400 tracking-wider">LIVE</span>
                    </div>
                    <i class="bi bi-journal-text text-4xl md:text-5xl text-gray-500 mb-5 block group-hover:text-white transition-colors duration-300"></i>
                    <h4 class="font-semibold text-white tracking-tight">GNews API</h4>
                </div>
                <div class="premium-card p-6 md:p-8 rounded-xl text-center relative overflow-hidden group" data-aos="fade-up" data-aos-delay="350">
                    <div class="absolute top-4 right-4 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 shadow-[0_0_8px_#22c55e] animate-pulse"></span>
                        <span class="text-[9px] font-bold text-gray-400 tracking-wider">LIVE</span>
                    </div>
                    <i class="bi bi-map text-4xl md:text-5xl text-gray-500 mb-5 block group-hover:text-white transition-colors duration-300"></i>
                    <h4 class="font-semibold text-white tracking-tight">OpenStreetMap</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section with Counter Animation -->
    <section class="py-20 border-y border-white/5 bg-surface">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 md:gap-4 text-center divide-x-0 md:divide-x divide-white/10">
                <div data-aos="zoom-in" data-aos-delay="100" class="p-4">
                    <h3 class="text-4xl md:text-5xl font-extrabold text-white mb-2 tracking-tighter"><span class="counter" data-target="250">0</span>+</h3>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Negara</p>
                </div>
                <div data-aos="zoom-in" data-aos-delay="150" class="p-4">
                    <h3 class="text-4xl md:text-5xl font-extrabold text-white mb-2 tracking-tighter"><span class="counter" data-target="100">0</span>+</h3>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Pelabuhan</p>
                </div>
                <div data-aos="zoom-in" data-aos-delay="200" class="p-4">
                    <h3 class="text-4xl md:text-5xl font-extrabold text-white mb-2 tracking-tighter"><span class="counter" data-target="150">0</span>+</h3>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Berita</p>
                </div>
                <div data-aos="zoom-in" data-aos-delay="250" class="p-4">
                    <h3 class="text-4xl md:text-5xl font-extrabold text-white mb-2 tracking-tighter"><span class="counter" data-target="6">0</span></h3>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Integrasi API</p>
                </div>
                <div data-aos="zoom-in" data-aos-delay="300" class="p-4 col-span-2 md:col-span-1">
                    <h3 class="text-4xl md:text-5xl font-extrabold text-white mb-2 tracking-tighter"><span class="counter" data-target="4">0</span></h3>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Kategori Risiko</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Dashboard Preview Mockup Section -->
    <section id="dashboard" class="py-24 bg-base relative border-b border-white/5 overflow-hidden">
        <!-- Glow effect behind mockup -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[500px] bg-brand/10 rounded-full blur-[120px] pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-5xl font-bold tracking-tight mb-4 text-gradient">Dashboard Preview</h2>
                <p class="text-gray-400 text-lg max-w-2xl mx-auto font-light">Tampilan sistem SupplyGuard untuk pemantauan data analitik rantai pasok.</p>
            </div>
            
            <!-- MacOS Style Mockup -->
            <div class="mockup-window rounded-xl overflow-hidden group" data-aos="fade-up" data-aos-delay="200">
                <!-- Window Header -->
                <div class="bg-[#121212] px-4 py-3 border-b border-white/5 flex items-center justify-between">
                    <div class="flex gap-2">
                        <div class="w-3 h-3 rounded-full bg-[#FF5F56] border border-[#E0443E]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#FFBD2E] border border-[#DEA123]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#27C93F] border border-[#1AAB29]"></div>
                    </div>
                    <div class="text-[11px] font-medium text-gray-500 bg-white/5 px-3 py-1 rounded-md flex items-center gap-1.5 border border-white/5">
                        <i class="bi bi-lock-fill"></i> localhost/supplyguard
                    </div>
                    <div class="w-12"></div> <!-- Spacer for flex centering -->
                </div>
                
                <!-- Mockup Content (Placeholder for actual screenshot) -->
                <div class="relative aspect-video bg-[#0A0A0A] flex flex-col items-center justify-center p-8 overflow-hidden group-hover:scale-[1.01] transition-transform duration-700">
                    <!-- Grid background pattern -->
                    <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
                    
                    <div class="relative z-10 text-center premium-card p-10 rounded-2xl border border-white/10 shadow-2xl backdrop-blur-xl">
                        <div class="w-16 h-16 bg-brand/10 border border-brand/20 rounded-full flex items-center justify-center mx-auto mb-5">
                            <i class="bi bi-display text-2xl text-brand"></i>
                        </div>
                        <h4 class="text-xl font-bold text-white mb-2">Tampilan Dashboard</h4>
                        <p class="text-gray-400 text-sm max-w-sm mx-auto">Area penempatan screenshot dari antarmuka dashboard admin dan pengguna.</p>
                        <div class="mt-6 flex justify-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-brand/50 animate-pulse"></span>
                            <span class="w-2 h-2 rounded-full bg-brand/50 animate-pulse delay-75"></span>
                            <span class="w-2 h-2 rounded-full bg-brand/50 animate-pulse delay-150"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-32 bg-base relative overflow-hidden border-b border-white/5">
        <div class="absolute inset-0 flex justify-center items-center pointer-events-none opacity-50">
            <div class="w-[800px] h-[300px] bg-brand/20 rounded-[100%] filter blur-[100px]"></div>
        </div>
        <div class="max-w-3xl mx-auto px-6 text-center relative z-10" data-aos="fade-up">
            <h2 class="text-4xl md:text-5xl font-bold tracking-tight mb-6 text-white">Siap Memantau Risiko Rantai Pasok Global?</h2>
            <p class="text-gray-400 text-lg mb-10 font-light">Masuk ke SupplyGuard dan manfaatkan informasi real-time dari berbagai sumber data global.</p>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-200 text-black px-10 py-4 rounded-lg text-sm font-semibold transition-all duration-300 shadow-[0_0_20px_rgba(255,255,255,0.1)] hover:shadow-[0_0_30px_rgba(255,255,255,0.3)]">
                Masuk Sekarang <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer id="kontak" class="bg-base pt-16 pb-8 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-12 md:gap-8 mb-16">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-6">
                        <i class="bi bi-box-seam text-brand text-xl"></i>
                        <span class="font-bold text-lg tracking-tight text-white">SupplyGuard</span>
                    </div>
                    <p class="text-gray-500 text-sm leading-relaxed max-w-sm">Supply Chain Risk Intelligence Platform.</p>
                </div>
                <div>
                    <h4 class="text-white text-sm font-semibold mb-5">Menu Utama</h4>
                    <ul class="space-y-3 text-gray-500 text-sm">
                        <li><a href="#beranda" class="hover:text-white transition-colors duration-200">Beranda</a></li>
                        <li><a href="#fitur" class="hover:text-white transition-colors duration-200">Fitur</a></li>
                        <li><a href="#dashboard" class="hover:text-white transition-colors duration-200">Dashboard</a></li>
                        <li><a href="#kontak" class="hover:text-white transition-colors duration-200">Kontak</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white text-sm font-semibold mb-5">Sistem</h4>
                    <ul class="space-y-3 text-gray-500 text-sm">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors duration-200">Masuk ke Sistem</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-gray-600">
                <p>&copy; {{ date('Y') }} SupplyGuard. Tugas Akhir.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white transition-colors duration-200"><i class="bi bi-github"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize Animate On Scroll (AOS)
        AOS.init({
            once: true,
            offset: 40,
            duration: 700,
            easing: 'ease-out-cubic',
        });

        // Dynamic Navbar Glassmorphism
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 20) {
                nav.classList.add('shadow-lg', 'bg-black/80');
                nav.classList.remove('bg-black/60');
            } else {
                nav.classList.remove('shadow-lg', 'bg-black/80');
                nav.classList.add('bg-black/60');
            }
        });

        // Counter Animation Logic
        const counters = document.querySelectorAll('.counter');
        const speed = 150; // The lower the faster

        const animateCounters = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = +counter.getAttribute('data-target');
                    
                    const updateCount = () => {
                        const count = +counter.innerText;
                        const increment = target / speed;

                        if (count < target) {
                            counter.innerText = Math.ceil(count + increment);
                            setTimeout(updateCount, 15);
                        } else {
                            counter.innerText = target;
                        }
                    };

                    updateCount();
                    observer.unobserve(counter); // Only run once
                }
            });
        };

        const counterObserver = new IntersectionObserver(animateCounters, {
            threshold: 0.5
        });

        counters.forEach(counter => counterObserver.observe(counter));
    </script>
</body>
</html>
