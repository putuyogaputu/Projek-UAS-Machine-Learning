<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediksi Saham BBCA (UAS ML)</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8/hammer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        /* Style Kustom Tambahan */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            color: #2c3e50;
        }

        /* Animasi Keren */
        .fade-in {
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pulse-icon:hover i {
            transform: scale(1.1);
            transition: transform 0.3s ease;
            display: inline-block;
        }

        /* Desain Card Utama */
        .glass-card {
            background: #ffffff;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        /* Kartu KPI (Quick Stats) */
        .stat-card {
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            border: 1px solid #e9ecef;
            border-radius: 1rem;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.1);
            border-color: #cfe2ff;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        /* Form Custom */
        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            background-color: #f8fafc;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
            border-color: #0d6efd;
            background-color: #ffffff;
        }

        /* Tombol Ciamik */
        .btn-predict {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .btn-predict:hover {
            background: linear-gradient(135deg, #0a58ca 0%, #084298 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }

        /* State saat tombol loading */
        .btn-predict.loading-state {
            background: #6c757d;
            pointer-events: none;
            box-shadow: none;
            transform: none;
        }

        .app-header .icon-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #e0ebff 0%, #cfe2ff 100%);
            color: #0d6efd;
            border-radius: 50%;
            font-size: 2rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.15);
        }
    </style>
</head>

<body>
    <div class="container mt-4 mb-5 fade-in">

        <div class="app-header text-center pulse-icon">
            <div class="icon-wrapper">
                <i class="bi bi-robot"></i>
            </div>
            <h2 class="fw-bold mb-2">AI Prediksi Saham <span class="text-primary">BBCA</span></h2>
            <p class="text-secondary">Proyek UAS Machine Learning &bull; Algoritma Regresi Cerdas</p>
            <p style="text-align: center;">Model Ini Hanya Memprediksi Saham BBCA Dalam Periode Waktu 18 Agustus 2020 - 15
                Agustus 2025</p>
        </div>

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger rounded-3 shadow-sm border-0">
            <div class="d-flex align-items-center mb-2 fw-bold">
                <i class="bi bi-x-circle-fill me-2"></i> Terdapat Kesalahan:
            </div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card glass-card p-4 mb-4">
            <form action="{{ route('predict') }}" method="POST" id="predictForm">
                @csrf
                <div class="row g-4">
                    <div class="col-md-5">
                        <label class="form-label"><i class="bi bi-calendar-event text-primary me-1"></i> Tanggal Mulai</label>
                        <input type="text" id="start_date" name="start_date" class="form-control" required value="{{ $start_date ?? '' }}" placeholder="Pilih Tanggal Mulai...">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label"><i class="bi bi-calendar-check text-primary me-1"></i> Tanggal Akhir</label>
                        <input type="text" id="end_date" name="end_date" class="form-control" required value="{{ $end_date ?? '' }}" placeholder="Pilih Tanggal Akhir...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" id="submitBtn" class="btn btn-primary btn-predict w-100 text-white shadow-sm">
                            <i class="bi bi-magic me-1"></i> Analisis ML
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if(isset($dates) && isset($prices))

        <div class="row g-4 mb-4 fade-in" style="animation-delay: 0.2s;">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">Prediksi Tertinggi</p>
                        <h5 class="fw-bold mb-0 text-success" id="maxPriceVal">Rp 0</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-graph-down-arrow"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">Prediksi Terendah</p>
                        <h5 class="fw-bold mb-0 text-danger" id="minPriceVal">Rp 0</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">Prediksi Penutupan Akhir</p>
                        <h5 class="fw-bold mb-0 text-primary" id="latestPriceVal">Rp 0</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="card glass-card p-4 mb-5 fade-in" style="animation-delay: 0.4s;">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-activity text-primary me-2"></i>Prediksi Tren Harga BBCA</h4>
                    <p class="text-muted mb-0 small">Visualisasi perbandingan harga historis dan prediksi Machine Learning.</p>
                </div>
                <div class="mt-3 mt-sm-0">
                    <button id="resetZoomBtn" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                        <i class="bi bi-arrows-angle-expand me-1"></i> Reset Ukuran
                    </button>
                </div>
            </div>

            <textarea id="rawDataDates" style="display: none;">{!! $dates !!}</textarea>
            <textarea id="rawActualPrices" style="display: none;">{!! $actual_prices ?? '[]' !!}</textarea>
            <textarea id="rawDataPrices" style="display: none;">{!! $prices !!}</textarea>

            <div class="bg-white rounded-4 border p-3 mb-4 shadow-sm" style="height: 420px; width: 100%;">
                <canvas id="stockChart"></canvas>
            </div>

            <div class="text-center mb-5">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 fw-normal rounded-pill">
                    <i class="bi bi-mouse3 me-1"></i> <i>Scroll</i> untuk Zoom | Klik + Tahan untuk Menggeser
                </span>
            </div>

            <div class="row g-4 mt-2 border-top pt-4">
                <div class="col-lg-4 col-md-6">
                    <div class="bg-white rounded-4 border p-3 h-100 shadow-sm hover-lift">
                        <h6 class="fw-bold text-center mb-1"><i class="bi bi-pie-chart-fill text-warning me-2"></i>Proporsi Tren Harian</h6>
                        <p class="text-muted text-center small mb-3">Frekuensi Prediksi Naik vs Turun</p>
                        <div style="height: 280px; width: 100%;">
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-md-6">
                    <div class="bg-white rounded-4 border p-3 h-100 shadow-sm hover-lift">
                        <h6 class="fw-bold text-center mb-1"><i class="bi bi-bar-chart-steps text-info me-2"></i>Perbandingan 10 Hari Terakhir</h6>
                        <p class="text-muted text-center small mb-3">Selisih Data Aktual vs Prediksi Algoritma</p>
                        <div style="height: 280px; width: 100%;">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="text-center text-muted mt-5 mb-4" style="font-size: 0.85rem;">
            &copy; 2024 Aplikasi Prediksi Saham ML. Dibuat dengan <i class="bi bi-heart-fill text-danger mx-1"></i> untuk pemenuhan UAS.
        </div>
    </div>

    <script>
        document.getElementById('predictForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');

            // Ubah teks dan tambahkan animasi spinner saat ditekan
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses AI...';

            // Nonaktifkan tombol secara visual agar tidak bisa diklik dua kali
            btn.classList.add('loading-state');
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#start_date", {
                altInput: true,
                altFormat: "d F Y",
                dateFormat: "Y-m-d"
            });
            flatpickr("#end_date", {
                altInput: true,
                altFormat: "d F Y",
                dateFormat: "Y-m-d"
            });
        });
    </script>

    @if(isset($dates) && isset($prices))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const strDates = document.getElementById('rawDataDates').value;
                const strActual = document.getElementById('rawActualPrices').value;
                const strPrices = document.getElementById('rawDataPrices').value;

                const chartLabels = JSON.parse(strDates);
                const chartActual = JSON.parse(strActual);
                const chartPrices = JSON.parse(strPrices);

                // 0. UPDATE KARTU RINGKASAN (KPI STATS)
                const validPrices = chartPrices.filter(p => p !== null && p > 0);
                const maxPrice = Math.max(...validPrices);
                const minPrice = Math.min(...validPrices);
                const latestPrice = validPrices[validPrices.length - 1];

                const formatRupiah = (angka) => new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0
                }).format(angka);

                document.getElementById('maxPriceVal').innerText = formatRupiah(maxPrice);
                document.getElementById('minPriceVal').innerText = formatRupiah(minPrice);
                document.getElementById('latestPriceVal').innerText = formatRupiah(latestPrice);

                // 1. GRAFIK UTAMA (LINE CHART DGN GRADIENT)
                const ctx = document.getElementById('stockChart').getContext('2d');

                let gradientBlue = ctx.createLinearGradient(0, 0, 0, 400);
                gradientBlue.addColorStop(0, 'rgba(13, 110, 253, 0.4)');
                gradientBlue.addColorStop(1, 'rgba(13, 110, 253, 0.0)');

                window.myStockChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                                label: 'Harga Aktual (Data Asli)',
                                data: chartActual,
                                borderColor: '#198754',
                                backgroundColor: 'transparent',
                                borderWidth: 2.5,
                                pointRadius: 0,
                                tension: 0.1,
                                spanGaps: true,
                                order: 1
                            },
                            {
                                label: 'Prediksi ML (Model)',
                                data: chartPrices,
                                borderColor: '#0d6efd',
                                backgroundColor: gradientBlue,
                                borderWidth: 2,
                                borderDash: [6, 4],
                                fill: true,
                                pointRadius: 0,
                                tension: 0.3,
                                order: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            tooltip: {
                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                titleColor: '#2c3e50',
                                bodyColor: '#2c3e50',
                                borderColor: '#e9ecef',
                                borderWidth: 1,
                                titleFont: {
                                    family: 'Inter',
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    family: 'Inter',
                                    size: 13
                                },
                                padding: 12,
                                cornerRadius: 8,
                                boxPadding: 6,
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
                            zoom: {
                                pan: {
                                    enabled: true,
                                    mode: 'x'
                                },
                                zoom: {
                                    wheel: {
                                        enabled: true,
                                        speed: 0.1
                                    },
                                    pinch: {
                                        enabled: true
                                    },
                                    mode: 'x',
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        family: 'Inter'
                                    },
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            },
                            y: {
                                border: {
                                    display: false
                                },
                                grid: {
                                    color: '#f1f3f5',
                                    drawBorder: false
                                },
                                ticks: {
                                    font: {
                                        family: 'Inter'
                                    }
                                }
                            }
                        }
                    }
                });

                document.getElementById('resetZoomBtn').addEventListener('click', function() {
                    window.myStockChart.resetZoom();
                });

                // 2. LOGIKA PIE & BAR CHART
                let trenNaik = 0;
                let trenTurun = 0;
                for (let i = 1; i < chartPrices.length; i++) {
                    if (chartPrices[i] !== null && chartPrices[i - 1] !== null) {
                        if (chartPrices[i] >= chartPrices[i - 1]) trenNaik++;
                        else trenTurun++;
                    }
                }

                const limit = Math.min(10, chartLabels.length);
                const lastLabels = chartLabels.slice(-limit);
                const lastActual = chartActual.slice(-limit);
                const lastPredicted = chartPrices.slice(-limit);

                // 3. RENDER PIE CHART (DOUGHNUT)
                const pieCtx = document.getElementById('pieChart').getContext('2d');
                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Bullish (Naik)', 'Bearish (Turun)'],
                        datasets: [{
                            data: [trenNaik, trenTurun],
                            backgroundColor: ['#198754', '#dc3545'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        family: 'Inter',
                                        size: 12
                                    },
                                    padding: 20
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });

                // 4. RENDER BAR CHART
                const barCtx = document.getElementById('barChart').getContext('2d');
                new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: lastLabels,
                        datasets: [{
                                label: 'Aktual',
                                data: lastActual,
                                backgroundColor: '#198754',
                                borderRadius: 6,
                                barPercentage: 0.6
                            },
                            {
                                label: 'Prediksi ML',
                                data: lastPredicted,
                                backgroundColor: '#0d6efd',
                                borderRadius: 6,
                                barPercentage: 0.6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        family: 'Inter',
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                titleColor: '#2c3e50',
                                bodyColor: '#2c3e50',
                                borderColor: '#e9ecef',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + new Intl.NumberFormat('id-ID', {
                                            style: 'currency',
                                            currency: 'IDR'
                                        }).format(context.parsed.y);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        family: 'Inter',
                                        size: 10
                                    }
                                }
                            },
                            y: {
                                grid: {
                                    color: '#f1f3f5'
                                },
                                ticks: {
                                    font: {
                                        family: 'Inter',
                                        size: 11
                                    }
                                }
                            }
                        }
                    }
                });

            } catch (error) {
                console.error("Error menggambar grafik:", error);
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @endif
</body>

</html>