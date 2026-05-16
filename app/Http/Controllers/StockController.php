<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StockController extends Controller
{
    public function index()
    {
        return view('stock');
    }

    public function predict(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            // Tembak API Python
            $response = Http::post('http://127.0.0.1:8000/predict', [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return view('stock', [
                    'dates' => json_encode($data['dates']),
                    'actual_prices' => json_encode($data['actual_prices']), // Tangkap harga asli
                    'prices' => json_encode($data['predicted_prices']),     // Tangkap harga prediksi
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ]);
            }
            // JIKA GAGAL, BACA PESAN ERROR DARI PYTHON
            $errorMessage = 'Gagal terhubung ke Machine Learning API.';
            if ($response->json() && isset($response->json()['detail'])) {
                // Tampilkan pesan asli dari Python (contoh: file tidak ditemukan)
                $errorMessage = 'Error dari ML Backend: ' . $response->json()['detail'];
            }

            return back()->with('error', $errorMessage);
        } catch (\Exception $e) {
            // Jika server Python mati sama sekali (karena belum di-run)
            return back()->with('error', 'Server Python (Uvicorn) sepertinya mati. Pastikan uvicorn sudah dijalankan.');
        }
    }
}
