@extends('layouts.app')

@section('title', 'Hasil Perbandingan - SupplyGuard')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.03em;">⚖️ Analisis Perbandingan</h1>
            <p class="text-muted mb-0">Perbandingan metrik secara berdampingan antara {{ $country1->country->name ?? '-' }} dan {{ $country2->country->name ?? '-' }}</p>
        </div>
        <a href="{{ route('compare.index') }}" class="btn btn-outline-dark d-flex align-items-center gap-2 hover-lift px-3 shadow-sm">
            <i class="bi bi-arrow-left"></i> Ubah Pilihan
        </a>
    </div>

    @php
        $c1 = $country1->country;
        $c2 = $country2->country;

        $w1 = $c1->weather->first();
        $w2 = $c2->weather->first();

        $e1 = $c1->economies->first();
        $e2 = $c2->economies->first();

        $curr1 = \App\Models\Currency::where('country_id', $c1->id)->first();
        $curr2 = \App\Models\Currency::where('country_id', $c2->id)->first();

        $portCount1 = $c1->ports->count();
        $portCount2 = $c2->ports->count();

        $shipments1 = $c1->originShipments->where('status', 'In Transit')->count() + $c1->destinationShipments->where('status', 'In Transit')->count();
        $shipments2 = $c2->originShipments->where('status', 'In Transit')->count() + $c2->destinationShipments->where('status', 'In Transit')->count();
    @endphp

    <div class="row g-4">
        <!-- Comparison Summary -->
        <div class="col-lg-8">
            <div class="card-premium mb-0">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-list-columns text-primary me-2"></i> Kinerja Parametrik Berdampingan</span>
                </div>
                <div class="card-premium-body p-0">
                    <div class="table-responsive">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Indikator</th>
                                    <th class="text-center">{{ $c1->name }}</th>
                                    <th class="text-center">{{ $c2->name }}</th>
                                    <th>Status Perbandingan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Risk Score -->
                                <tr class="table-warning bg-warning bg-opacity-10">
                                    <td class="fw-bold">Indeks Risiko Total (Lebih rendah lebih aman)</td>
                                    <td class="text-center fw-bold text-dark">{{ number_format($country1->total_score, 1) }}% ({{ $country1->risk_level === 'High' ? 'Risiko Tinggi' : ($country1->risk_level === 'Medium' ? 'Risiko Sedang' : 'Risiko Rendah') }})</td>
                                    <td class="text-center fw-bold text-dark">{{ number_format($country2->total_score, 1) }}% ({{ $country2->risk_level === 'High' ? 'Risiko Tinggi' : ($country2->risk_level === 'Medium' ? 'Risiko Sedang' : 'Risiko Rendah') }})</td>
                                    <td>
                                        @if($country1->total_score < $country2->total_score)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill"><i class="bi bi-shield-check"></i> {{ $c1->name }} lebih aman</span>
                                        @elseif($country2->total_score < $country1->total_score)
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill"><i class="bi bi-shield-slash"></i> {{ $c2->name }} lebih aman</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill">Seimbang</span>
                                        @endif
                                    </td>
                                </tr>
                                <!-- Weather -->
                                <tr>
                                    <td>Cuaca Saat Ini</td>
                                    <td class="text-center">
                                        @if($w1 && $w1->weather_condition && $w1->weather_condition !== '-')
                                            {{ $w1->weather_condition === 'Sunny' ? 'Cerah' : ($w1->weather_condition === 'Cloudy' ? 'Berawan' : ($w1->weather_condition === 'Rain' ? 'Hujan' : ($w1->weather_condition === 'Storm' ? 'Badai' : ($w1->weather_condition === 'Snow' ? 'Salju' : ($w1->weather_condition === 'Fog' ? 'Kabut' : $w1->weather_condition))))) }} ({{ number_format($w1->temperature, 1) }}°C, Angin: {{ number_format($w1->wind_speed, 1) }} m/s)
                                        @else
                                            <span class="text-muted small">Tidak tersedia</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($w2 && $w2->weather_condition && $w2->weather_condition !== '-')
                                            {{ $w2->weather_condition === 'Sunny' ? 'Cerah' : ($w2->weather_condition === 'Cloudy' ? 'Berawan' : ($w2->weather_condition === 'Rain' ? 'Hujan' : ($w2->weather_condition === 'Storm' ? 'Badai' : ($w2->weather_condition === 'Snow' ? 'Salju' : ($w2->weather_condition === 'Fog' ? 'Kabut' : $w2->weather_condition))))) }} ({{ number_format($w2->temperature, 1) }}°C, Angin: {{ number_format($w2->wind_speed, 1) }} m/s)
                                        @else
                                            <span class="text-muted small">Tidak tersedia</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($country1->weather_score < $country2->weather_score)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Stabilitas Iklim Lebih Baik</span>
                                        @elseif($country2->weather_score < $country1->weather_score)
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Risiko Iklim Lebih Buruk</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill">Seimbang</span>
                                        @endif
                                    </td>
                                </tr>
                                <!-- Economy -->
                                <tr>
                                    <td>Indikator Ekonomi</td>
                                    <td class="text-center">
                                        @if($e1)
                                            PDB: {{ $e1->gdp > 0 ? '$' . number_format($e1->gdp, 1) . 'B' : 'Tidak tersedia' }}, 
                                            Inflasi: {{ $e1->inflation != 0 ? number_format($e1->inflation, 1) . '%' : 'Tidak tersedia' }}, 
                                            Pengangguran: {{ $e1->unemployment > 0 ? number_format($e1->unemployment, 1) . '%' : 'Tidak tersedia' }}
                                        @else
                                            <span class="text-muted small">Tidak tersedia</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($e2)
                                            PDB: {{ $e2->gdp > 0 ? '$' . number_format($e2->gdp, 1) . 'B' : 'Tidak tersedia' }}, 
                                            Inflasi: {{ $e2->inflation != 0 ? number_format($e2->inflation, 1) . '%' : 'Tidak tersedia' }}, 
                                            Pengangguran: {{ $e2->unemployment > 0 ? number_format($e2->unemployment, 1) . '%' : 'Tidak tersedia' }}
                                        @else
                                            <span class="text-muted small">Tidak tersedia</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($country1->economy_score < $country2->economy_score)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Ekonomi Lebih Stabil</span>
                                        @elseif($country2->economy_score < $country1->economy_score)
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Risiko Ekonomi Lebih Tinggi</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill">Seimbang</span>
                                        @endif
                                    </td>
                                </tr>
                                <!-- Currency -->
                                <tr>
                                    <td>Mata Uang Lokal & Nilai Tukar</td>
                                    <td class="text-center">
                                        @if($curr1 && $curr1->rate > 0)
                                            {{ $curr1->code }} (Kurs: {{ $curr1->symbol }}{{ number_format($curr1->rate, 2) }}/USD)
                                        @else
                                            {{ $c1->currency && $c1->currency !== '-' ? $c1->currency . ' (Tidak tersedia)' : 'Tidak tersedia' }}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($curr2 && $curr2->rate > 0)
                                            {{ $curr2->code }} (Kurs: {{ $curr2->symbol }}{{ number_format($curr2->rate, 2) }}/USD)
                                        @else
                                            {{ $c2->currency && $c2->currency !== '-' ? $c2->currency . ' (Tidak tersedia)' : 'Tidak tersedia' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($country1->currency_score < $country2->currency_score)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Mata uang lebih kuat</span>
                                        @elseif($country2->currency_score < $country1->currency_score)
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Volatilitas mata uang lebih tinggi</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill">Seimbang</span>
                                        @endif
                                    </td>
                                </tr>
                                <!-- Ports -->
                                <tr>
                                    <td>Pusat Pengiriman Aktif</td>
                                    <td class="text-center">{{ $portCount1 > 0 ? $portCount1 . ' Pelabuhan terdaftar' : 'Tidak tersedia' }}</td>
                                    <td class="text-center">{{ $portCount2 > 0 ? $portCount2 . ' Pelabuhan terdaftar' : 'Tidak tersedia' }}</td>
                                    <td>
                                        @if($country1->port_score < $country2->port_score)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Kapasitas pelabuhan lebih tinggi</span>
                                        @elseif($country2->port_score < $country1->port_score)
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">Risiko kemacetan pelabuhan</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill">Seimbang</span>
                                        @endif
                                    </td>
                                </tr>
                                <!-- Shipments Activity -->
                                <tr>
                                    <td>Pengiriman Aktif (Dalam Perjalanan)</td>
                                    <td class="text-center">{{ $shipments1 > 0 ? $shipments1 . ' Kargo aktif' : 'Tidak tersedia' }}</td>
                                    <td class="text-center">{{ $shipments2 > 0 ? $shipments2 . ' Kargo aktif' : 'Tidak tersedia' }}</td>
                                    <td>
                                        @if($shipments1 > $shipments2)
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">Volume pengiriman lebih tinggi</span>
                                        @elseif($shipments2 > $shipments1)
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">Volume pengiriman lebih rendah</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill">Seimbang</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Radar/Bar Chart Comparison -->
        <div class="col-lg-4">
            <div class="card-premium mb-4">
                <div class="card-premium-header">
                    <span class="fs-6 fw-bold"><i class="bi bi-graph-up text-primary me-2"></i> Profil Risiko Perbandingan</span>
                </div>
                <div class="card-premium-body">
                    <div style="height: 250px; position: relative;">
                        <canvas id="comparisonChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- AI / Rule Recommendation -->
            <div class="card-premium bg-light mb-0">
                <div class="card-premium-header border-0 bg-transparent py-3">
                    <span class="fs-6 fw-bold text-dark"><i class="bi bi-cpu text-primary me-2"></i> Rekomendasi AI Rantai Pasok</span>
                </div>
                <div class="card-premium-body pt-0">
                    @if($country1->total_score < $country2->total_score)
                        <div class="alert alert-success border-0 mb-0 shadow-sm rounded-4">
                            <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-check-circle-fill"></i> Pilihan Utama: {{ $c1->name }}</h6>
                            <p class="small mb-0">
                                Analisis kontrol risiko secara berdampingan membuktikan bahwa <strong>{{ $c1->name }}</strong> menunjukkan ambang batas logistik yang lebih aman (tingkat risiko sebesar <strong>{{ number_format($country1->total_score, 1) }}%</strong>) dibandingkan dengan {{ $c2->name }} (tingkat risiko sebesar <strong>{{ number_format($country2->total_score, 1) }}%</strong>). Perencanaan pengiriman sebaiknya memilih rute melalui {{ $c1->name }}.
                            </p>
                        </div>
                    @elseif($country2->total_score < $country1->total_score)
                        <div class="alert alert-success border-0 mb-0 shadow-sm rounded-4">
                            <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-check-circle-fill"></i> Pilihan Utama: {{ $c2->name }}</h6>
                            <p class="small mb-0">
                                Analisis kontrol risiko secara berdampingan membuktikan bahwa <strong>{{ $c2->name }}</strong> menunjukkan ambang batas logistik yang lebih aman (tingkat risiko sebesar <strong>{{ number_format($country2->total_score, 1) }}%</strong>) dibandingkan dengan {{ $c1->name }} (tingkat risiko sebesar <strong>{{ number_format($country1->total_score, 1) }}%</strong>). Perencanaan pengiriman sebaiknya memilih rute melalui {{ $c2->name }}.
                            </p>
                        </div>
                    @else
                        <div class="alert alert-primary border-0 mb-0 shadow-sm rounded-4">
                            <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-info-circle-fill"></i> Ambang Batas Risiko Setara</h6>
                            <p class="small mb-0">
                                Kedua negara memiliki profil risiko yang identik (skor sebesar <strong>{{ number_format($country1->total_score, 1) }}%</strong>). Distribusi jadwal dan pilihan pelabuhan dapat diselesaikan menggunakan variabel kedekatan geografis dan biaya pengiriman.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('comparisonChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Iklim', 'Ekonomi', 'Mata Uang', 'Pelabuhan', 'Total Risiko'],
                datasets: [
                    {
                        label: '{{ $c1->name }}',
                        data: [
                            {{ $country1->weather_score }},
                            {{ $country1->economy_score }},
                            {{ $country1->currency_score }},
                            {{ $country1->port_score }},
                            {{ $country1->total_score }}
                        ],
                        backgroundColor: 'rgba(212, 175, 55, 0.75)',
                        borderColor: '#D4AF37',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: '{{ $c2->name }}',
                        data: [
                            {{ $country2->weather_score }},
                            {{ $country2->economy_score }},
                            {{ $country2->currency_score }},
                            {{ $country2->port_score }},
                            {{ $country2->total_score }}
                        ],
                        backgroundColor: 'rgba(31, 41, 55, 0.75)',
                        borderColor: '#1F2937',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Ambang Batas Risiko (%)'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush