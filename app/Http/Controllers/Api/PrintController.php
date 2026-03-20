<?php

namespace App\Http\Controllers\Api;

use App\Biaya;
use App\Kunjungan;
use App\Pembayaran;
use App\Pembelian;
use App\PenerimaanBarang;
use App\Penjualan;
use App\Http\Controllers\BluetoothPrintController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function qrData(Request $request, $type, $id)
    {
        $model = $this->resolveModel($type, $id);
        if (!$model) {
            return response()->json(['message' => 'Tipe transaksi tidak valid.'], 404);
        }

        $user = auth()->user();
        if (!$this->canAccessTransaction($user, $model)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pathMap = [
            'penjualan' => 'penjualan',
            'pembelian' => 'pembelian',
            'biaya' => 'biaya',
            'kunjungan' => 'kunjungan',
            'pembayaran' => 'pembayaran',
            'penerimaan-barang' => 'penerimaan-barang',
        ];

        $publicPath = $pathMap[$type] ?? null;
        if (!$publicPath) {
            return response()->json(['message' => 'Tipe transaksi tidak didukung.'], 400);
        }

        return response()->json([
            'type' => $type,
            'id' => $model->id,
            'uuid' => $model->uuid,
            'invoice_url' => url('invoice/' . $publicPath . '/' . $model->uuid),
            'download_url' => url('invoice/' . $publicPath . '/' . $model->uuid . '/download'),
            'qr_payload' => url('invoice/' . $publicPath . '/' . $model->uuid),
        ]);
    }

    public function bluetoothData(Request $request, $type, $id)
    {
        $model = $this->resolveModel($type, $id);
        if (!$model) {
            return response()->json(['message' => 'Tipe transaksi tidak valid.'], 404);
        }

        $user = auth()->user();
        if (!$this->canAccessTransaction($user, $model)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $printer = new BluetoothPrintController();

        // Bluetooth JSON saat ini tersedia untuk 4 tipe transaksi ini.
        switch ($type) {
            case 'penjualan':
                return $printer->penjualanJson($model->id);
            case 'pembelian':
                return $printer->pembelianJson($model->id);
            case 'biaya':
                return $printer->biayaJson($model->id);
            case 'kunjungan':
                return $printer->kunjunganJson($model->id);
            default:
                return response()->json([
                    'message' => 'Bluetooth print belum tersedia untuk tipe ini.',
                    'supported_types' => ['penjualan', 'pembelian', 'biaya', 'kunjungan'],
                ], 400);
        }
    }

    private function resolveModel($type, $id)
    {
        switch ($type) {
            case 'penjualan':
                return Penjualan::find($id);
            case 'pembelian':
                return Pembelian::find($id);
            case 'biaya':
                return Biaya::find($id);
            case 'kunjungan':
                return Kunjungan::find($id);
            case 'pembayaran':
                return Pembayaran::find($id);
            case 'penerimaan-barang':
                return PenerimaanBarang::find($id);
            default:
                return null;
        }
    }

    private function canAccessTransaction($user, $model)
    {
        if (!$user || !$model) {
            return false;
        }

        if ($user->role === 'super_admin') {
            return true;
        }

        if (isset($model->user_id) && (int) $model->user_id === (int) $user->id) {
            return true;
        }

        if (isset($model->approver_id) && $model->approver_id && (int) $model->approver_id === (int) $user->id) {
            return true;
        }

        $gudangId = $model->gudang_id ?? null;
        if ($gudangId && in_array($user->role, ['admin', 'spectator']) && method_exists($user, 'canAccessGudang')) {
            return $user->canAccessGudang($gudangId);
        }

        return false;
    }
}
