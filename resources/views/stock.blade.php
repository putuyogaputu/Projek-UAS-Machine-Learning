<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediksi Saham BBCA (UAS ML)</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8/hammer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Aplikasi Prediksi Harga Saham BBCA</h2>

        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card shadow-sm p-4 mb-4">
            <form action="{{ route('predict') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-5">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="text" id="start_date" name="start_date" class="form-control" required value="{{ $start_date ?? '' }}" placeholder="Pilih Tanggal Mulai...">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="text" id="end_date" name="end_date" class="form-control" required value="{{ $end_date ?? '' }}" placeholder="Pilih Tanggal Akhir...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Prediksi</button>
                    </div>
                </div>
            </form>
        </div>

        @if(isset($dates) && isset($prices))
        <div class="card shadow-sm p-4 mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Hasil Prediksi Tren</h4>
                <button id="resetZoomBtn" class="btn btn-sm btn-outline-secondary">Reset Zoom</button>
            </div>

            <textarea id="rawDataDates" style="display: none;">{!! $dates !!}</textarea>
            <textarea id="rawActualPrices" style="display: none;">{!! $actual_prices ?? '[]' !!}</textarea>
            <textarea id="rawDataPrices" style="display: none;">{!! $prices !!}</textarea>

            <div style="height: 400px; width: 100%;">
                <canvas id="stockChart"></canvas>
            </div>

            <p class="text-muted text-center mt-3 mb-0" style="font-size: 0.85rem;">
                <i>*Gunakan *scroll mouse* (roda mouse) untuk memperbesar/memperkecil, dan klik+tahan untuk menggeser grafik.</i>
            </p>
        </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#start_date", {
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d"
            });
            flatpickr("#end_date", {
                altInput: true,
                altFormat: "d/m/Y",
                dateFormat: "Y-m-d"
            });
        });
    </script>

    @if(isset($dates) && isset($prices))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // 1. Ambil data dari textarea
                const strDates = document.getElementById('rawDataDates').value;
                const strActual = document.getElementById('rawActualPrices').value;
                const strPrices = document.getElementById('rawDataPrices').value;

                // 2. Parse JSON ke Array
                const chartLabels = JSON.parse(strDates);
                const chartActual = JSON.parse(strActual);
                const chartPrices = JSON.parse(strPrices);

                const ctx = document.getElementById('stockChart').getContext('2d');

                // 3. Render Grafik dengan 2 Datasets
                window.myStockChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                                label: 'Harga Asli (Dataset)',
                                data: chartActual,
                                borderColor: '#198754', // Hijau
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                pointRadius: 0,
                                tension: 0.1,
                                spanGaps: true // Penting: Menyambung garis untuk hari libur (bursa tutup)
                            },
                            {
                                label: 'Tren Prediksi (ML)',
                                data: chartPrices,
                                borderColor: '#0d6efd', // Biru
                                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                borderWidth: 2,
                                borderDash: [5, 5], // Garis putus-putus
                                fill: true,
                                pointRadius: 0,
                                tension: 0.1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('id-ID', {
                                                style: 'currency',
                                                currency: 'IDR'
                                            }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            },
                            // KONFIGURASI ZOOM PLUGIN
                            zoom: {
                                pan: {
                                    enabled: true,
                                    mode: 'x',
                                },
                                zoom: {
                                    wheel: {
                                        enabled: true,
                                    },
                                    pinch: {
                                        enabled: true
                                    },
                                    mode: 'x',
                                }
                            }
                        }
                    }
                });

                // Event listener untuk tombol Reset Zoom
                document.getElementById('resetZoomBtn').addEventListener('click', function() {
                    window.myStockChart.resetZoom();
                });

            } catch (error) {
                console.error("Error menggambar grafik:", error);
                alert("Gagal menggambar grafik. Error: " + error.message);
            }
        });
    </script>
    @endif
</body>

</html>