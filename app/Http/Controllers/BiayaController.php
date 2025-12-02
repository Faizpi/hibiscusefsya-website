<?php

namespace App\Http\Controllers;

use App\Biaya;
use App\BiayaItem;
use App\User;
use App\Kontak;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class BiayaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Biaya::with(['user', 'approver']);
        if ($user->role == 'super_admin') {
        } elseif ($user->role == 'admin') {
            $query->where(function($q) use ($user) {
                $q->where('approver_id', $user->id)
                  ->orWhere('user_id', $user->id);
            });
        } else {
            $query->where('user_id', $user->id);
        }

        $allBiaya = $query->latest()->get();

        $allBiaya->transform(function($item) {
            $dateCode = $item->created_at->format('Ymd');
            $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
            $item->custom_number = "EXP-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            return $item;
        });

        $totalBulanIni = $allBiaya->where('tgl_transaksi', '>=', Carbon::now()->startOfMonth())
            ->whereIn('status', ['Pending', 'Approved'])
            ->sum('grand_total');
            
        $total30Hari = $allBiaya->where('tgl_transaksi', '>=', Carbon::now()->subDays(30))
            ->whereIn('status', ['Pending', 'Approved'])
            ->sum('grand_total');
            
        $totalBelumDibayar = $allBiaya->where('status', 'Pending')->sum('grand_total');

        return view('biaya.index', [
            'biayas' => $allBiaya,
            'totalBulanIni' => $totalBulanIni,
            'total30Hari' => $total30Hari,
            'totalBelumDibayar' => $totalBelumDibayar,
        ]);
    }

    public function create()
    {
        $kontaks = Kontak::all();
        $approvers = User::whereIn('role', ['admin', 'super_admin'])->get();
        
        return view('biaya.create', compact('kontaks', 'approvers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'approver_id' => 'required|exists:users,id', 
            'bayar_dari' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'penerima' => 'nullable|string|max:255',
            'tax_percentage' => 'required|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,png,pdf,zip,doc,docx|max:2048',
            
            'kategori' => 'required|array|min:1',
            'total' => 'required|array|min:1',
            'kategori.*' => 'required|string|max:255',
            'total.*' => 'required|numeric|min:0',
        ]);

        // Default path null
        $path = null;

        // Pastikan folder public/storage/lampiran_biaya ada
        $publicFolder = public_path('storage/lampiran_biaya');
        if (! File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            // Pindah langsung ke public/storage/lampiran_biaya
            $file->move($publicFolder, $filename);
            $path = 'lampiran_biaya/' . $filename;
        }

        $subTotal = 0;
        foreach ($request->total as $index => $jumlah) {
            $subTotal += $jumlah ?? 0;
        }
        
        $pajakPersen = $request->tax_percentage ?? 0;
        $jumlahPajak = $subTotal * ($pajakPersen / 100);
        $grandTotal = $subTotal + $jumlahPajak;

        $countToday = Biaya::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;

        DB::beginTransaction();
        try {
            $biayaInduk = Biaya::create([
                'user_id' => Auth::id(),
                'status' => 'Pending',
                'approver_id' => $request->approver_id,
                'no_urut_harian' => $noUrut,
                'bayar_dari' => $request->bayar_dari,
                'penerima' => $request->penerima,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'cara_pembayaran' => $request->cara_pembayaran,
                'tag' => $request->tag,
                'memo' => $request->memo,
                'lampiran_path' => $path,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            foreach ($request->kategori as $index => $kategori) {
                BiayaItem::create([
                    'biaya_id' => $biayaInduk->id,
                    'kategori' => $kategori,
                    'deskripsi' => $request->deskripsi_akun[$index] ?? null,
                    'jumlah' => $request->total[$index] ?? 0,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Jika terjadi error, hapus file yang baru di-upload agar tidak orphan
            if ($path && File::exists(public_path('storage/' . $path))) {
                File::delete(public_path('storage/' . $path));
            }
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil diajukan kepada Approver.');
    }

    public function approve(Biaya $biaya)
    {
        $user = Auth::user();
        
        if ($user->role == 'user') return back()->with('error', 'Akses ditolak.');
        if ($user->role == 'admin' && $biaya->approver_id != $user->id) return back()->with('error', 'Bukan wewenang Anda.');

        $biaya->status = 'Approved';
        $biaya->save();
        return back()->with('success', 'Data biaya berhasil disetujui.');
    }

    public function cancel(Biaya $biaya)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'super_admin'])) return back()->with('error', 'Akses ditolak.');

        $biaya->status = 'Canceled';
        $biaya->save();
        return back()->with('success', 'Transaksi dibatalkan.');
    }

    public function destroy(Biaya $biaya)
    {
        $user = Auth::user();
        $canDelete = false;
        if (in_array($user->role, ['admin', 'super_admin'])) $canDelete = true;
        elseif ($biaya->user_id == $user->id && $biaya->status == 'Pending') $canDelete = true;

        if (!$canDelete) return back()->with('error', 'Akses ditolak.');
        
        if ($biaya->lampiran_path) {
            $full = public_path('storage/' . $biaya->lampiran_path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }

        $biaya->delete();
        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil dihapus.');
    }

    public function edit(Biaya $biaya)
    {
        $user = Auth::user();
        $canEdit = false;

        if (in_array($user->role, ['admin', 'super_admin'])) {
            $canEdit = true;
        } 

        elseif ($biaya->user_id == $user->id && $biaya->status == 'Pending') {
            $canEdit = true;
        }

        if (!$canEdit) {
            return redirect()->route('biaya.index')->with('error', 'Anda tidak memiliki akses untuk mengedit data ini.');
        }

        $biaya->load('items');
        $kontaks = Kontak::all();
        $approvers = User::whereIn('role', ['admin', 'super_admin'])->get();

        return view('biaya.edit', compact('biaya', 'kontaks', 'approvers'));
    }

    public function update(Request $request, Biaya $biaya)
    {
        $user = Auth::user();
        $canUpdate = false;
        if (in_array($user->role, ['admin', 'super_admin'])) {
            $canUpdate = true;
        } elseif ($biaya->user_id == $user->id && $biaya->status == 'Pending') {
            $canUpdate = true;
        }

        if (!$canUpdate) return redirect()->route('biaya.index')->with('error', 'Akses ditolak.');

        $request->validate([
            'approver_id' => 'required|exists:users,id',
            'bayar_dari' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'tax_percentage' => 'required|numeric|min:0',
            'lampiran' => 'nullable|file|mimes:jpg,png,pdf,zip,doc,docx|max:2048',
            'kategori' => 'required|array|min:1',
            'total' => 'required|array|min:1',
            'kategori.*' => 'required|string|max:255',
            'total.*' => 'required|numeric|min:0',
            'penerima' => 'nullable|string|max:255',
        ]);

        $path = $biaya->lampiran_path;

        // Pastikan folder public/storage/lampiran_biaya ada
        $publicFolder = public_path('storage/lampiran_biaya');
        if (! File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        if ($request->hasFile('lampiran')) {
            // Hapus file lama jika ada
            if ($path) {
                $old = public_path('storage/' . $path);
                if (File::exists($old)) {
                    File::delete($old);
                }
            }

            $file = $request->file('lampiran');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $file->move($publicFolder, $filename);
            $path = 'lampiran_biaya/' . $filename;
        }

        $subTotal = 0;
        foreach ($request->total as $index => $jumlah) {
            $subTotal += $jumlah ?? 0;
        }
        $pajakPersen = $request->tax_percentage ?? 0;
        $jumlahPajak = $subTotal * ($pajakPersen / 100);
        $grandTotal = $subTotal + $jumlahPajak;

        DB::beginTransaction();
        try {
            $biaya->update([
                'status' => 'Pending', 
                'approver_id' => $request->approver_id,
                'bayar_dari' => $request->bayar_dari,
                'penerima' => $request->penerima,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'cara_pembayaran' => $request->cara_pembayaran,
                'tag' => $request->tag,
                'memo' => $request->memo,
                'lampiran_path' => $path,
                'tax_percentage' => $pajakPersen,
                'grand_total' => $grandTotal,
            ]);

            $biaya->items()->delete();

            foreach ($request->kategori as $index => $kategori) {
                BiayaItem::create([
                    'biaya_id' => $biaya->id,
                    'kategori' => $kategori,
                    'deskripsi' => $request->deskripsi_akun[$index] ?? null,
                    'jumlah' => $request->total[$index] ?? 0,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // jika error, hapus file baru yang mungkin sudah diupload
            if (isset($filename) && File::exists(public_path('storage/lampiran_biaya/' . $filename))) {
                File::delete(public_path('storage/lampiran_biaya/' . $filename));
            }
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil diperbarui.');
    }

    public function show(Biaya $biaya)
    {
        $user = Auth::user();
        $allow = false;
        if ($user->role == 'super_admin') $allow = true;
        elseif ($user->role == 'admin' && $biaya->approver_id == $user->id) $allow = true;
        elseif ($biaya->user_id == $user->id) $allow = true;

        if (!$allow) return redirect()->route('biaya.index')->with('error', 'Akses ditolak.');

        $biaya->load('items', 'user', 'approver');
        $dateCode = $biaya->created_at->format('Ymd');
        $biaya->custom_number = "EXP-{$dateCode}-{$biaya->user_id}-{$biaya->no_urut_harian}";

        return view('biaya.show', compact('biaya'));
    }

    public function print(Biaya $biaya)
    {
        $user = Auth::user();
        $allow = false;
        if ($user->role == 'super_admin') $allow = true;
        elseif ($user->role == 'admin' && $biaya->approver_id == $user->id) $allow = true;
        elseif ($biaya->user_id == $user->id) $allow = true;

        if (!$allow) return redirect()->route('biaya.index')->with('error', 'Akses ditolak.');

        $biaya->load('items', 'user', 'approver');
        return view('biaya.print', compact('biaya'));
    }
}
