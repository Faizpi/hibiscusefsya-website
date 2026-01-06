<?php

namespace App\Http\Controllers;

use App\Biaya;
use App\BiayaItem;
use App\User;
use App\Kontak;
use App\Services\InvoiceEmailService;
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
            // Super admin dapat melihat semua biaya
        } elseif ($user->role == 'admin') {
            // Admin dapat melihat:
            // 1. Biaya yang dia buat sendiri
            // 2. Biaya yang dia sebagai approver
            // 3. Biaya yang dibuat oleh user di gudang yang dia kelola
            $adminGudangIds = $user->gudangs->pluck('id')->toArray();
            if ($user->current_gudang_id) {
                $adminGudangIds[] = $user->current_gudang_id;
            }
            if ($user->gudang_id) {
                $adminGudangIds[] = $user->gudang_id;
            }
            $adminGudangIds = array_unique($adminGudangIds);

            // Ambil semua user_id yang berada di gudang yang dikelola admin ini
            // Fix: properly group orWhereIn to avoid returning all users
            $usersInGudang = User::where(function ($q) use ($adminGudangIds) {
                $q->whereIn('gudang_id', $adminGudangIds)
                    ->orWhereIn('current_gudang_id', $adminGudangIds);
            })->pluck('id')->toArray();

            $query->where(function ($q) use ($user, $usersInGudang) {
                $q->where('approver_id', $user->id)
                    ->orWhere('user_id', $user->id)
                    ->orWhereIn('user_id', $usersInGudang);
            });
        } elseif ($user->role == 'spectator') {
            // Spectator dapat melihat biaya di gudang yang dia akses
            $spectatorGudangIds = $user->spectatorGudangs->pluck('id')->toArray();
            if ($user->current_gudang_id) {
                $spectatorGudangIds[] = $user->current_gudang_id;
            }
            $spectatorGudangIds = array_unique($spectatorGudangIds);

            // Fix: properly group orWhereIn to avoid returning all users
            $usersInGudang = User::where(function ($q) use ($spectatorGudangIds) {
                $q->whereIn('gudang_id', $spectatorGudangIds)
                    ->orWhereIn('current_gudang_id', $spectatorGudangIds);
            })->pluck('id')->toArray();

            $query->whereIn('user_id', $usersInGudang);
        } else {
            // User biasa hanya melihat biaya miliknya sendiri
            $query->where('user_id', $user->id);
        }

        // Filter jenis_biaya jika ada
        if (request()->has('jenis') && request('jenis') != '') {
            $query->where('jenis_biaya', request('jenis'));
        }

        // Clone query untuk summary calculations (semua data)
        $summaryQuery = clone $query;
        $allForSummary = $summaryQuery->get();

        $totalBulanIni = $allForSummary->filter(function ($item) {
            return Carbon::parse($item->tgl_transaksi)->gte(Carbon::now()->startOfMonth());
        })->whereIn('status', ['Pending', 'Approved'])->sum('grand_total');

        $total30Hari = $allForSummary->filter(function ($item) {
            return Carbon::parse($item->tgl_transaksi)->gte(Carbon::now()->subDays(30));
        })->whereIn('status', ['Pending', 'Approved'])->sum('grand_total');

        $totalBelumDibayar = $allForSummary->where('status', 'Pending')->sum('grand_total');
        $totalApproved = $allForSummary->where('status', 'Approved')->sum('grand_total');
        $totalCanceled = $allForSummary->where('status', 'Canceled')->count();

        // Total biaya masuk dan keluar (approved only)
        $totalBiayaMasuk = $allForSummary->where('jenis_biaya', 'masuk')
            ->whereIn('status', ['Approved'])->sum('grand_total');
        $totalBiayaKeluar = $allForSummary->where('jenis_biaya', 'keluar')
            ->whereIn('status', ['Approved'])->sum('grand_total');

        // Paginated data untuk table display
        /** @var \Illuminate\Pagination\LengthAwarePaginator $biayas */
        $biayas = $query->latest()->paginate(20);
        $biayas->getCollection()->transform(function ($item) {
            $dateCode = $item->created_at->format('Ymd');
            $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
            $item->custom_number = "EXP-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            return $item;
        });

        return view('biaya.index', [
            'biayas' => $biayas,
            'totalBulanIni' => $totalBulanIni,
            'total30Hari' => $total30Hari,
            'totalBelumDibayar' => $totalBelumDibayar,
            'totalApproved' => $totalApproved,
            'totalCanceled' => $totalCanceled,
            'totalBiayaMasuk' => $totalBiayaMasuk,
            'totalBiayaKeluar' => $totalBiayaKeluar,
        ]);
    }

    public function create()
    {
        $user = Auth::user();

        // Spectator tidak bisa membuat transaksi
        if ($user->role === 'spectator') {
            return redirect()->route('biaya.index')->with('error', 'Spectator tidak memiliki akses untuk membuat transaksi.');
        }

        $kontaks = Kontak::all();

        // Tidak perlu lagi, approver otomatis ditentukan di backend
        // $approvers = User::whereIn('role', ['admin', 'super_admin'])->get();

        // Generate preview nomor invoice
        $countToday = Biaya::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;
        $previewNomor = Biaya::generateNomor(Auth::id(), $noUrut, Carbon::now());

        return view('biaya.create', compact('kontaks', 'previewNomor'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Spectator tidak bisa membuat transaksi
        if ($user->role === 'spectator') {
            return redirect()->route('biaya.index')->with('error', 'Spectator tidak memiliki akses untuk membuat transaksi.');
        }

        $request->validate([
            // approver_id tidak perlu lagi dari request, akan di-set otomatis
            'bayar_dari' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'penerima' => 'nullable|string|max:255',
            'tax_percentage' => 'required|numeric|min:0',
            'lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip,doc,docx|max:2048',

            'kategori' => 'required|array|min:1',
            'total' => 'required|array|min:1',
            'kategori.*' => 'required|string|max:255',
            'total.*' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();

        // Tentukan approver otomatis berdasarkan role user
        $approverId = null;
        $initialStatus = 'Pending';

        if ($user->role == 'user') {
            // Sales: cari admin gudang tempat dia bekerja
            $gudang = $user->getCurrentGudang();
            if ($gudang) {
                // Cari admin yang handle gudang ini
                $adminGudang = User::where('role', 'admin')
                    ->where(function ($q) use ($gudang) {
                        $q->where('gudang_id', $gudang->id)
                            ->orWhereHas('gudangs', function ($sub) use ($gudang) {
                                $sub->where('gudangs.id', $gudang->id);
                            });
                    })
                    ->first();

                if ($adminGudang) {
                    $approverId = $adminGudang->id;
                } else {
                    // Kalau tidak ada admin gudang, ke super admin
                    $superAdmin = User::where('role', 'super_admin')->first();
                    $approverId = $superAdmin ? $superAdmin->id : null;
                }
            } else {
                // Sales tidak punya gudang, ke super admin
                $superAdmin = User::where('role', 'super_admin')->first();
                $approverId = $superAdmin ? $superAdmin->id : null;
            }
        } elseif ($user->role == 'admin') {
            // Admin: approver ke super admin
            $superAdmin = User::where('role', 'super_admin')->first();
            $approverId = $superAdmin ? $superAdmin->id : null;
        } elseif ($user->role == 'super_admin') {
            // Super admin: langsung approved, tapi tetap harus isi approver_id
            $initialStatus = 'Approved';
            // Cari admin gudang berdasarkan gudang yang dipilih (dari item biaya)
            // Atau gunakan super_admin sendiri sebagai approver
            $approverId = $user->id;
        } else {
            // Fallback: gunakan super_admin sebagai approver
            $superAdmin = User::where('role', 'super_admin')->first();
            $approverId = $superAdmin ? $superAdmin->id : $user->id;
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

        // Generate nomor transaksi: EXP-YYYYMMDD-USERID-NOURUT
        $dateCode = Carbon::parse($request->tgl_transaksi)->format('Ymd');
        $noUrutPadded = str_pad($noUrut, 3, '0', STR_PAD_LEFT);
        $nomor = "EXP-{$dateCode}-" . Auth::id() . "-{$noUrutPadded}";

        // Upload lampiran dengan nama sesuai kode invoice
        $lampiranPaths = [];
        $publicFolder = public_path('storage/lampiran_biaya');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        // Handle multiple lampiran
        if ($request->hasFile('lampiran')) {
            $counter = 1;
            foreach ($request->file('lampiran') as $file) {
                $extension = $file->getClientOriginalExtension();
                // Format: EXP-xxx-1.jpg, EXP-xxx-2.jpg, etc
                $filename = $nomor . '-' . $counter . '.' . $extension;
                $file->move($publicFolder, $filename);
                $lampiranPaths[] = 'lampiran_biaya/' . $filename;
                $counter++;
            }
        }

        DB::beginTransaction();
        try {
            $biayaInduk = Biaya::create([
                'user_id' => Auth::id(),
                'status' => $initialStatus,
                'approver_id' => $approverId,
                'no_urut_harian' => $noUrut,
                'jenis_biaya' => $request->jenis_biaya ?? 'keluar',
                'nomor' => $nomor,
                'bayar_dari' => $request->bayar_dari,
                'penerima' => $request->penerima,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'cara_pembayaran' => $request->cara_pembayaran,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'lampiran_paths' => $lampiranPaths,
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

            // Kirim notifikasi email ke pembuat + approvers (kecuali jika sudah auto-approved)
            if ($initialStatus == 'Pending') {
                InvoiceEmailService::sendCreatedNotification($biayaInduk, 'biaya');
            } else {
                // Jika sudah auto-approved (admin/super_admin buat biaya), kirim notifikasi created saja
                InvoiceEmailService::sendCreatedNotification($biayaInduk, 'biaya');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            // Jika terjadi error, hapus file yang baru di-upload agar tidak orphan
            foreach ($lampiranPaths as $lampiranPath) {
                if (File::exists(public_path('storage/' . $lampiranPath))) {
                    File::delete(public_path('storage/' . $lampiranPath));
                }
            }
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }

        $message = ($initialStatus == 'Approved')
            ? 'Data biaya berhasil disimpan dan langsung approved.'
            : 'Data biaya berhasil diajukan untuk approval.';

        return redirect()->route('biaya.index')->with('success', $message);
    }

    public function approve(Biaya $biaya)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'super_admin']))
            return back()->with('error', 'Akses ditolak.');

        if ($biaya->status === 'Canceled') {
            return back()->with('error', 'Transaksi sudah dibatalkan, tidak bisa di-approve.');
        }

        if ($user->role === 'admin' && $biaya->status === 'Approved') {
            return back()->with('error', 'Transaksi sudah disetujui. Admin tidak bisa melakukan approve ulang.');
        }

        $biaya->status = 'Approved';
        $biaya->approver_id = $user->id;
        $biaya->save();

        // Kirim notifikasi email ke pembuat bahwa transaksi telah disetujui
        InvoiceEmailService::sendApprovedNotification($biaya, 'biaya');

        return back()->with('success', 'Data biaya berhasil disetujui.');
    }

    public function cancel(Biaya $biaya)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'super_admin']))
            return back()->with('error', 'Akses ditolak.');

        if ($biaya->status === 'Canceled') {
            return redirect()->route('biaya.index')->with('error', 'Transaksi sudah dibatalkan.');
        }

        // Hanya super_admin yang bisa cancel Approved
        if ($biaya->status === 'Approved' && $user->role !== 'super_admin') {
            return redirect()->route('biaya.index')->with('error', 'Hanya Super Admin yang dapat membatalkan transaksi yang sudah disetujui.');
        }

        $biaya->status = 'Canceled';
        $biaya->save();
        return back()->with('success', 'Transaksi dibatalkan.');
    }

    public function uncancel(Biaya $biaya)
    {
        $user = Auth::user();

        // Hanya super_admin yang bisa uncancel
        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat membatalkan pembatalan transaksi.');
        }

        if ($biaya->status !== 'Canceled') {
            return redirect()->route('biaya.index')->with('error', 'Transaksi tidak dalam status dibatalkan.');
        }

        // Tentukan approver - untuk biaya, super_admin yang melakukan uncancel jadi approver
        $approverId = $user->id;

        // Set status kembali ke Pending agar perlu di-approve ulang
        $biaya->status = 'Pending';
        $biaya->approver_id = $approverId;
        $biaya->save();

        return back()->with('success', 'Pembatalan transaksi dibatalkan. Status kembali ke Pending.');
    }

    public function deleteLampiran(Biaya $biaya, $index)
    {
        $user = Auth::user();

        // Hanya super_admin yang bisa hapus lampiran
        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Hanya Super Admin yang dapat menghapus lampiran.');
        }

        $lampiranPaths = $biaya->lampiran_paths ?? [];

        if (!isset($lampiranPaths[$index])) {
            return back()->with('error', 'Lampiran tidak ditemukan.');
        }

        // Hapus file fisik
        $filePath = public_path('storage/' . $lampiranPaths[$index]);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Hapus dari array
        unset($lampiranPaths[$index]);
        $biaya->lampiran_paths = array_values($lampiranPaths); // Re-index array
        $biaya->save();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    public function destroy(Biaya $biaya)
    {
        $user = Auth::user();
        $canDelete = false;
        if ($user->role === 'super_admin')
            $canDelete = true;

        if (!$canDelete)
            return back()->with('error', 'Akses ditolak.');

        if ($biaya->lampiran_path) {
            $full = public_path('storage/' . $biaya->lampiran_path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }

        $biaya->delete();
        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil dihapus.');
    }

    /**
     * Return HTML struk untuk di-screenshot/print sebagai image
     * Untuk iWare: Screenshot → Image Mode → Print
     */
    public function printJson(Biaya $biaya)
    {
        // HTML akan di-render jadi image oleh html2canvas di client side
        return view('biaya.struk', compact('biaya'));
    }

    public function edit(Biaya $biaya)
    {
        $user = Auth::user();

        // Only super_admin dapat mengedit
        if ($user->role !== 'super_admin') {
            return redirect()->route('biaya.index')->with('error', 'Anda tidak memiliki akses untuk mengedit data biaya.');
        }

        $biaya->load('items');
        $kontaks = Kontak::all();
        // Tidak perlu approvers, akan otomatis di backend

        return view('biaya.edit', compact('biaya', 'kontaks'));
    }

    public function update(Request $request, Biaya $biaya)
    {
        $user = Auth::user();

        // Admin tidak boleh mengedit/update
        if ($user->role === 'admin') {
            return redirect()->route('biaya.index')->with('error', 'Admin tidak diperbolehkan mengubah data biaya.');
        }
        $canUpdate = false;
        if ($user->role === 'super_admin') {
            $canUpdate = true;
        }

        if (!$canUpdate)
            return redirect()->route('biaya.index')->with('error', 'Akses ditolak.');

        $request->validate([
            // approver_id tidak perlu validasi, akan di-set otomatis
            'bayar_dari' => 'required|string',
            'tgl_transaksi' => 'required|date',
            'tax_percentage' => 'required|numeric|min:0',
            'lampiran' => 'nullable|array',
            'lampiran.*' => 'nullable|file|mimes:jpg,png,pdf,zip,doc,docx|max:2048',
            'kategori' => 'required|array|min:1',
            'total' => 'required|array|min:1',
            'kategori.*' => 'required|string|max:255',
            'total.*' => 'required|numeric|min:0',
            'penerima' => 'nullable|string|max:255',
        ]);

        // Ambil lampiran_paths yang sudah ada
        $lampiranPaths = $biaya->lampiran_paths ?? [];

        // Pastikan folder public/storage/lampiran_biaya ada
        $publicFolder = public_path('storage/lampiran_biaya');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        // Handle multiple lampiran baru (append ke existing)
        $newUploadedPaths = [];
        if ($request->hasFile('lampiran')) {
            $counter = count($lampiranPaths) + 1;
            foreach ($request->file('lampiran') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = $biaya->nomor . '-' . $counter . '.' . $extension;
                $file->move($publicFolder, $filename);
                $newPath = 'lampiran_biaya/' . $filename;
                $lampiranPaths[] = $newPath;
                $newUploadedPaths[] = $newPath;
                $counter++;
            }
        }

        $subTotal = 0;
        foreach ($request->total as $index => $jumlah) {
            $subTotal += $jumlah ?? 0;
        }
        $pajakPersen = $request->tax_percentage ?? 0;
        $jumlahPajak = $subTotal * ($pajakPersen / 100);
        $grandTotal = $subTotal + $jumlahPajak;

        // Tentukan approver otomatis berdasarkan role user (sama seperti store)
        $approverId = null;
        $initialStatus = 'Pending';

        if ($user->role == 'user') {
            $gudang = $user->getCurrentGudang();
            if ($gudang) {
                $adminGudang = User::where('role', 'admin')
                    ->where(function ($q) use ($gudang) {
                        $q->where('gudang_id', $gudang->id)
                            ->orWhereHas('gudangs', function ($sub) use ($gudang) {
                                $sub->where('gudangs.id', $gudang->id);
                            });
                    })
                    ->first();

                if ($adminGudang) {
                    $approverId = $adminGudang->id;
                } else {
                    $superAdmin = User::where('role', 'super_admin')->first();
                    $approverId = $superAdmin ? $superAdmin->id : null;
                }
            } else {
                $superAdmin = User::where('role', 'super_admin')->first();
                $approverId = $superAdmin ? $superAdmin->id : null;
            }
        } elseif ($user->role == 'admin') {
            $superAdmin = User::where('role', 'super_admin')->first();
            $approverId = $superAdmin ? $superAdmin->id : null;
        } else {
            // Super admin: langsung approved
            $initialStatus = 'Approved';
            $approverId = null;
        }

        DB::beginTransaction();
        try {
            $biaya->update([
                'status' => $initialStatus,
                'approver_id' => $approverId,
                'bayar_dari' => $request->bayar_dari,
                'penerima' => $request->penerima,
                'alamat_penagihan' => $request->alamat_penagihan,
                'tgl_transaksi' => $request->tgl_transaksi,
                'cara_pembayaran' => $request->cara_pembayaran,
                'tag' => $request->tag,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'lampiran_paths' => $lampiranPaths,
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
            foreach ($newUploadedPaths as $newPath) {
                if (File::exists(public_path('storage/' . $newPath))) {
                    File::delete(public_path('storage/' . $newPath));
                }
            }
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('biaya.index')->with('success', 'Data biaya berhasil diperbarui.');
    }

    public function show(Biaya $biaya)
    {
        $user = Auth::user();
        $allow = false;
        if ($user->role == 'super_admin')
            $allow = true;
        elseif ($user->role == 'admin' && $biaya->approver_id == $user->id)
            $allow = true;
        elseif ($biaya->user_id == $user->id)
            $allow = true;

        if (!$allow)
            return redirect()->route('biaya.index')->with('error', 'Akses ditolak.');

        $biaya->load('items', 'user', 'approver');
        $dateCode = $biaya->created_at->format('Ymd');
        $biaya->custom_number = "EXP-{$dateCode}-{$biaya->user_id}-{$biaya->no_urut_harian}";

        return view('biaya.show', compact('biaya'));
    }

    public function print(Biaya $biaya)
    {
        $user = Auth::user();
        $allow = false;
        if ($user->role == 'super_admin')
            $allow = true;
        elseif ($user->role == 'admin' && $biaya->approver_id == $user->id)
            $allow = true;
        elseif ($biaya->user_id == $user->id)
            $allow = true;

        if (!$allow)
            return redirect()->route('biaya.index')->with('error', 'Akses ditolak.');

        $biaya->load('items', 'user', 'approver');
        return view('biaya.print', compact('biaya'));
    }
}
