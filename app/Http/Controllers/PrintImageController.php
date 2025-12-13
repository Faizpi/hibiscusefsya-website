<?php

namespace App\Http\Controllers;

use App\Penjualan;
use App\Pembelian;
use App\Biaya;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class PrintImageController extends Controller
{
    /**
     * Generate PNG image untuk struk penjualan
     */
    public function penjualan($id)
    {
        $penjualan = Penjualan::with(['items.produk', 'user', 'gudang'])->findOrFail($id);
        
        $filename = "struk-penjualan-{$id}.png";
        $path = storage_path("app/public/{$filename}");

        try {
            Browsershot::html(
                view('print.penjualan-image', compact('penjualan'))->render()
            )
            ->windowSize(384, 2000)
            ->deviceScaleFactor(2)  // Better quality
            ->fullPage()
            ->save($path);

            return response()->file($path, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal generate image',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PNG image untuk struk pembelian
     */
    public function pembelian($id)
    {
        $pembelian = Pembelian::with(['items.produk', 'user', 'gudang'])->findOrFail($id);
        
        $filename = "struk-pembelian-{$id}.png";
        $path = storage_path("app/public/{$filename}");

        try {
            Browsershot::html(
                view('print.pembelian-image', compact('pembelian'))->render()
            )
            ->windowSize(384, 2000)
            ->deviceScaleFactor(2)
            ->fullPage()
            ->save($path);

            return response()->file($path, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal generate image',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PNG image untuk struk biaya
     */
    public function biaya($id)
    {
        $biaya = Biaya::with(['items', 'user'])->findOrFail($id);
        
        $filename = "struk-biaya-{$id}.png";
        $path = storage_path("app/public/{$filename}");

        try {
            Browsershot::html(
                view('print.biaya-image', compact('biaya'))->render()
            )
            ->windowSize(384, 2000)
            ->deviceScaleFactor(2)
            ->fullPage()
            ->save($path);

            return response()->file($path, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal generate image',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
