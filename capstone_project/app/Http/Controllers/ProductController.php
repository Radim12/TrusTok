<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function getProductMetrics(Request $request)
    {
        // 1. Ambil input produk dari tombol yang diklik di Laravel (misal: 'toner')
        // Nama input di request Laravel kita buat 'product'
        $productName = $request->input('product', 'toner');

        try {
            // 2. Tembak FastAPI tepat ke endpoint /get-metrics dengan method POST
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://trusttok-api-260465484877.asia-southeast2.run.app/get-metrics', [
                'product_name' => $productName // Kirim 'toner' dengan key 'product_name' sesuai FastAPI
            ]);

            // 3. Cek apakah FastAPI merespon dengan sukses
            if ($response->successful()) {
                $data = $response->json();

                // Kirim data hasil olahan FastAPI ke view Blade Laravel Anda
                return view('dashboard', [
                    'metrics' => $data,
                    'activeProduct' => $productName
                ]);
            }

            return back()->with('error', 'Gagal mengambil data dari FastAPI.');

        } catch (\Exception $e) {
            return back()->with('error', 'Koneksi ke FastAPI terputus: ' . $e->getMessage());
        }
    }
}
