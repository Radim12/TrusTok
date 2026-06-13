<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SentimentController extends Controller
{
    public function handleSentiment(Request $request)
    {
        // 1. Validasi input dari frontend
        $request->validate([
            'product_name' => 'required|string',
        ]);

        try {
            // 2. Tembak FastAPI (Ganti URL sesuai port FastAPI kamu)
            $response = Http::timeout(30)->post('http://127.0.0.1:8000/get-metrics', [
                'product_name' => $request->product_name,
            ]);

            // 3. Jika FastAPI sukses merespon, kembalikan datanya ke Alpine.js
            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['error' => 'Gagal mengambil data dari AI engine'], 500);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Koneksi ke server FastAPI terputus: ' . $e->getMessage()], 500);
        }
    }
}