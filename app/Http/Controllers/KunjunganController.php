<?php

namespace App\Http\Controllers;

use App\Kunjungan;
use App\User;
use App\Kontak;
use App\Gudang;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class KunjunganController extends Controller
{
    /**
     * Display a listing of kunjungan.
     */
    public function index()
    {
        $user = Auth::user();
        $query = Kunjungan::with(['user', 'approver', 'gudang']);

        if ($user->role == 'super_admin') {
            // Super admin lihat semua
        } elseif ($user->role == 'admin') {
            // Admin lihat data di gudang yang dia handle atau yang dia buat
            $query->where(function ($q) use ($user) {
                $q->where('approver_id', $user->id)
                    ->orWhere('user_id', $user->id);
            });
        } else {
            // User biasa hanya lihat miliknya
            $query->where('user_id', $user->id);
        }

        // Filter tujuan jika ada
        if (request()->has('tujuan') && request('tujuan') != '') {
            $query->where('tujuan', request('tujuan'));
        }

        // Clone query untuk summary calculations
        $summaryQuery = clone $query;
        $allForSummary = $summaryQuery->get();

        // Summary cards
        $totalPemeriksaanStock = $allForSummary->where('tujuan', 'Pemeriksaan Stock')
            ->whereIn('status', ['Pending', 'Approved'])->count();
        $totalPenagihan = $allForSummary->where('tujuan', 'Penagihan')
            ->whereIn('status', ['Pending', 'Approved'])->count();
        $totalPenawaran = $allForSummary->where('tujuan', 'Penawaran')
            ->whereIn('status', ['Pending', 'Approved'])->count();
        $totalCanceled = $allForSummary->where('status', 'Canceled')->count();

        // Paginated data untuk table display
        $kunjungans = $query->latest()->paginate(20);
        $kunjungans->getCollection()->transform(function ($item) {
            $dateCode = $item->created_at->format('Ymd');
            $noUrutPadded = str_pad($item->no_urut_harian, 3, '0', STR_PAD_LEFT);
            $item->custom_number = "VST-{$dateCode}-{$item->user_id}-{$noUrutPadded}";
            return $item;
        });

        return view('kunjungan.index', [
            'kunjungans' => $kunjungans,
            'totalPemeriksaanStock' => $totalPemeriksaanStock,
            'totalPenagihan' => $totalPenagihan,
            'totalPenawaran' => $totalPenawaran,
            'totalCanceled' => $totalCanceled,
        ]);
    }

    /**
     * Show the form for creating a new kunjungan.
     */
    public function create()
    {
        $kontaks = Kontak::all();
        $user = Auth::user();

        // Get user's gudang
        $gudang = $user->getCurrentGudang();

        return view('kunjungan.create', compact('kontaks', 'gudang'));
    }

    /**
     * Store a newly created kunjungan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sales_nama' => 'required|string|max:255',
            'sales_email' => 'nullable|email|max:255',
            'sales_alamat' => 'nullable|string',
            'tgl_kunjungan' => 'required|date',
            'tujuan' => 'required|in:Pemeriksaan Stock,Penagihan,Penawaran',
            'koordinat' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            'lampiran' => 'nullable|file|mimes:jpg,png,pdf,zip,doc,docx|max:2048',
        ]);

        $user = Auth::user();

        // Tentukan approver otomatis berdasarkan role user
        $approverId = null;
        $initialStatus = 'Pending';
        $gudangId = null;

        if ($user->role == 'user') {
            // Sales: cari admin gudang tempat dia bekerja
            $gudang = $user->getCurrentGudang();
            if ($gudang) {
                $gudangId = $gudang->id;
                // Cari admin yang handle gudang ini
                $adminGudang = User::where('role', 'admin')
                    ->where('current_gudang_id', $gudang->id)
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
            $gudang = $user->getCurrentGudang();
            $gudangId = $gudang ? $gudang->id : null;
            $superAdmin = User::where('role', 'super_admin')->first();
            $approverId = $superAdmin ? $superAdmin->id : null;
        } else {
            // Super admin: langsung approved, tidak perlu approver
            $initialStatus = 'Approved';
            $approverId = null;
        }

        // Handle lampiran upload
        $path = null;
        $publicFolder = public_path('storage/lampiran_kunjungan');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $file->move($publicFolder, $filename);
            $path = 'lampiran_kunjungan/' . $filename;
        }

        // Generate nomor urut
        $countToday = Kunjungan::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today())
            ->count();
        $noUrut = $countToday + 1;

        // Generate nomor kunjungan: VST-YYYYMMDD-USERID-NOURUT
        $dateCode = Carbon::now()->format('Ymd');
        $noUrutPadded = str_pad($noUrut, 3, '0', STR_PAD_LEFT);
        $nomor = "VST-{$dateCode}-" . Auth::id() . "-{$noUrutPadded}";

        DB::beginTransaction();
        try {
            $kunjungan = Kunjungan::create([
                'user_id' => Auth::id(),
                'status' => $initialStatus,
                'approver_id' => $approverId,
                'gudang_id' => $gudangId,
                'no_urut_harian' => $noUrut,
                'nomor' => $nomor,
                'sales_nama' => $request->sales_nama,
                'sales_email' => $request->sales_email,
                'sales_alamat' => $request->sales_alamat,
                'tgl_kunjungan' => $request->tgl_kunjungan,
                'tujuan' => $request->tujuan,
                'koordinat' => $request->koordinat,
                'memo' => $request->memo,
                'lampiran_path' => $path,
            ]);

            DB::commit();
            return redirect()->route('kunjungan.show', $kunjungan->id)
                ->with('success', 'Kunjungan berhasil dibuat dengan nomor ' . $nomor);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified kunjungan.
     */
    public function show(Kunjungan $kunjungan)
    {
        $kunjungan->load(['user', 'approver', 'gudang']);
        return view('kunjungan.show', compact('kunjungan'));
    }

    /**
     * Show the form for editing the specified kunjungan.
     */
    public function edit(Kunjungan $kunjungan)
    {
        $user = Auth::user();

        // Only super_admin dapat mengedit
        if ($user->role !== 'super_admin') {
            return redirect()->route('kunjungan.index')->with('error', 'Anda tidak memiliki akses untuk mengedit data kunjungan.');
        }

        $kontaks = Kontak::all();
        return view('kunjungan.edit', compact('kunjungan', 'kontaks'));
    }

    /**
     * Update the specified kunjungan.
     */
    public function update(Request $request, Kunjungan $kunjungan)
    {
        $user = Auth::user();

        // Only super_admin dapat update
        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah data kunjungan.');
        }

        $request->validate([
            'sales_nama' => 'required|string|max:255',
            'sales_email' => 'nullable|email|max:255',
            'sales_alamat' => 'nullable|string',
            'tujuan' => 'required|in:Pemeriksaan Stock,Penagihan,Penawaran',
            'memo' => 'nullable|string',
            'lampiran' => 'nullable|file|mimes:jpg,png,pdf,zip,doc,docx|max:2048',
        ]);

        // Handle lampiran upload
        $path = $kunjungan->lampiran_path;
        $publicFolder = public_path('storage/lampiran_kunjungan');
        if (!File::exists($publicFolder)) {
            File::makeDirectory($publicFolder, 0755, true);
        }

        if ($request->hasFile('lampiran')) {
            // Delete old file if exists
            if ($path && File::exists(public_path('storage/' . $path))) {
                File::delete(public_path('storage/' . $path));
            }
            $file = $request->file('lampiran');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $file->move($publicFolder, $filename);
            $path = 'lampiran_kunjungan/' . $filename;
        }

        $kunjungan->update([
            'sales_nama' => $request->sales_nama,
            'sales_email' => $request->sales_email,
            'sales_alamat' => $request->sales_alamat,
            'tujuan' => $request->tujuan,
            'koordinat' => $request->koordinat,
            'memo' => $request->memo,
            'lampiran_path' => $path,
        ]);

        return redirect()->route('kunjungan.show', $kunjungan->id)
            ->with('success', 'Kunjungan berhasil diperbarui.');
    }

    /**
     * Remove the specified kunjungan.
     */
    public function destroy(Kunjungan $kunjungan)
    {
        $user = Auth::user();

        // Only super_admin dapat hapus
        if ($user->role !== 'super_admin') {
            return back()->with('error', 'Akses ditolak.');
        }

        // Delete lampiran if exists
        if ($kunjungan->lampiran_path && File::exists(public_path('storage/' . $kunjungan->lampiran_path))) {
            File::delete(public_path('storage/' . $kunjungan->lampiran_path));
        }

        $kunjungan->delete();
        return redirect()->route('kunjungan.index')->with('success', 'Kunjungan berhasil dihapus.');
    }

    /**
     * Approve kunjungan
     */
    public function approve(Kunjungan $kunjungan)
    {
        $user = Auth::user();

        // Hanya admin yang ditunjuk atau super_admin yang bisa approve
        if ($user->role == 'super_admin' || ($user->role == 'admin' && $kunjungan->approver_id == $user->id)) {
            $kunjungan->update([
                'status' => 'Approved',
                'approver_id' => $user->id
            ]);
            return back()->with('success', 'Kunjungan berhasil disetujui.');
        }

        return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui kunjungan ini.');
    }

    /**
     * Cancel kunjungan
     */
    public function cancel(Request $request, Kunjungan $kunjungan)
    {
        $user = Auth::user();

        // Super admin bisa cancel kapan saja, admin hanya jika Pending
        if ($user->role == 'super_admin') {
            $kunjungan->update(['status' => 'Canceled']);
            return back()->with('success', 'Kunjungan berhasil dibatalkan.');
        }

        if ($user->role == 'admin' && $kunjungan->status == 'Pending') {
            $kunjungan->update(['status' => 'Canceled']);
            return back()->with('success', 'Kunjungan berhasil dibatalkan.');
        }

        return back()->with('error', 'Anda tidak memiliki akses untuk membatalkan kunjungan ini.');
    }

    /**
     * Print struk kunjungan
     */
    public function print(Kunjungan $kunjungan)
    {
        $kunjungan->load(['user', 'approver', 'gudang']);
        return view('kunjungan.print', compact('kunjungan'));
    }

    /**
     * Get kunjungan data as JSON for Bluetooth printing
     */
    public function printJson(Kunjungan $kunjungan)
    {
        $kunjungan->load(['user', 'approver', 'gudang']);

        $dateCode = $kunjungan->created_at->format('Ymd');
        $noUrut = str_pad($kunjungan->no_urut_harian, 3, '0', STR_PAD_LEFT);

        return response()->json([
            'nomor' => "VST-{$kunjungan->user_id}-{$dateCode}-{$noUrut}",
            'tanggal' => $kunjungan->tgl_kunjungan->format('d/m/Y'),
            'waktu' => $kunjungan->created_at->format('H:i'),
            'tujuan' => $kunjungan->tujuan,
            'sales_nama' => $kunjungan->sales_nama,
            'sales_email' => $kunjungan->sales_email ?? '-',
            'sales_alamat' => $kunjungan->sales_alamat ?? '-',
            'pembuat' => optional($kunjungan->user)->name ?? '-',
            'approver' => ($kunjungan->status != 'Pending' && $kunjungan->approver) ? $kunjungan->approver->name : '-',
            'gudang' => optional($kunjungan->gudang)->nama_gudang ?? '-',
            'status' => $kunjungan->status,
            'koordinat' => $kunjungan->koordinat ?? '-',
            'memo' => $kunjungan->memo ?? '-',
            'invoice_url' => url('invoice/kunjungan/' . $kunjungan->uuid)
        ]);
    }
}
