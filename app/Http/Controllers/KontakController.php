<?php

namespace App\Http\Controllers;

use App\Kontak;
use App\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class KontakController extends Controller
{
    /**
     * Get accessible gudang IDs for the current user.
     */
    private function getAccessibleGudangIds($user)
    {
        if ($user->role === 'super_admin') {
            return null; // null = all gudangs
        }

        $gudangIds = [];

        if ($user->role === 'admin') {
            $gudangIds = $user->gudangs->pluck('id')->toArray();
            if ($user->current_gudang_id)
                $gudangIds[] = $user->current_gudang_id;
            if ($user->gudang_id)
                $gudangIds[] = $user->gudang_id;
        } elseif ($user->role === 'spectator') {
            $gudangIds = $user->spectatorGudangs->pluck('id')->toArray();
            if ($user->current_gudang_id)
                $gudangIds[] = $user->current_gudang_id;
        } else {
            // user/sales
            $gudang = $user->getCurrentGudang();
            if ($gudang)
                $gudangIds[] = $gudang->id;
        }

        return array_unique($gudangIds);
    }

    /**
     * Scope kontak query by user's gudang access.
     * Kontaks with null gudang_id are visible to everyone (legacy data).
     */
    private function scopeByGudang($query, $user)
    {
        $gudangIds = $this->getAccessibleGudangIds($user);
        if ($gudangIds === null)
            return $query; // super_admin sees all

        $query->where(function ($q) use ($gudangIds) {
            $q->whereIn('gudang_id', $gudangIds)
                ->orWhereNull('gudang_id');
        });

        return $query;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Kontak::with('gudang');

        // Filter berdasarkan gudang user
        $this->scopeByGudang($query, $user);

        // Search by kode_kontak, nama, email, or no_telp
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_kontak', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_telp', 'like', "%{$search}%");
            });
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $kontaks */
        $kontaks = $query->orderBy('nama')->paginate(12);

        return view('kontak.index', compact('kontaks'));
    }

    public function show(Kontak $kontak)
    {
        $user = Auth::user();
        if (!$this->canAccessKontak($user, $kontak)) {
            return redirect()->route('kontak.index')->with('error', 'Akses ditolak. Kontak ini bukan milik gudang Anda.');
        }

        $kontak->load('gudang');
        return view('kontak.show', compact('kontak'));
    }

    public function print(Kontak $kontak)
    {
        $user = Auth::user();
        if (!$this->canAccessKontak($user, $kontak)) {
            return redirect()->route('kontak.index')->with('error', 'Akses ditolak.');
        }

        $kontak->load('gudang');
        return view('kontak.print', compact('kontak'));
    }

    public function downloadPdf(Kontak $kontak)
    {
        $user = Auth::user();
        if (!$this->canAccessKontak($user, $kontak)) {
            return redirect()->route('kontak.index')->with('error', 'Akses ditolak.');
        }

        $kontak->load('gudang');
        $pdf = PDF::loadView('kontak.print', compact('kontak'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('kontak-' . $kontak->kode_kontak . '.pdf');
    }

    public function create()
    {
        // Spectator tidak bisa membuat kontak baru
        if (Auth::user()->role === 'spectator') {
            return redirect()->route('kontak.index')->with('error', 'Spectator tidak memiliki akses untuk membuat data.');
        }
        return view('kontak.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Spectator tidak bisa menyimpan kontak baru
        if ($user->role === 'spectator') {
            return redirect()->route('kontak.index')->with('error', 'Spectator tidak memiliki akses untuk membuat data.');
        }

        $request->validate([
            'kode_kontak' => 'nullable|string|max:50|unique:kontaks,kode_kontak',
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:kontaks',
            'no_telp' => 'nullable|string|max:20',
            'pin' => 'nullable|string|size:6',
            'alamat' => 'nullable|string',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        $data = $request->all();

        // Auto-assign gudang_id dari gudang user saat ini
        $gudang = $user->getCurrentGudang();
        if ($gudang) {
            $data['gudang_id'] = $gudang->id;
        }

        Kontak::create($data);

        return redirect()->route('kontak.index')->with('success', 'Kontak baru berhasil ditambahkan.');
    }

    public function edit(Kontak $kontak)
    {
        $user = Auth::user();

        // Spectator tidak bisa edit
        if ($user->role === 'spectator') {
            return redirect()->route('kontak.index')->with('error', 'Spectator tidak memiliki akses untuk mengubah data.');
        }

        // Cek akses gudang
        if (!$this->canAccessKontak($user, $kontak)) {
            return redirect()->route('kontak.index')->with('error', 'Akses ditolak. Kontak ini bukan milik gudang Anda.');
        }

        $gudangs = Gudang::orderBy('nama_gudang')->get();
        $kontak->load('gudang');
        return view('kontak.edit', compact('kontak', 'gudangs'));
    }

    public function update(Request $request, Kontak $kontak)
    {
        $user = Auth::user();

        // Spectator tidak bisa update
        if ($user->role === 'spectator') {
            return redirect()->route('kontak.index')->with('error', 'Spectator tidak memiliki akses untuk mengubah data.');
        }

        // Cek akses gudang
        if (!$this->canAccessKontak($user, $kontak)) {
            return redirect()->route('kontak.index')->with('error', 'Akses ditolak. Kontak ini bukan milik gudang Anda.');
        }

        $request->validate([
            'kode_kontak' => 'nullable|string|max:50|unique:kontaks,kode_kontak,' . $kontak->id,
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:kontaks,email,' . $kontak->id,
            'no_telp' => 'nullable|string|max:20',
            'pin' => 'nullable|string|size:6',
            'alamat' => 'nullable|string',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
            'gudang_id' => 'nullable|exists:gudangs,id',
        ]);

        $data = $request->except('gudang_id');

        // Hanya super_admin yang bisa ubah gudang
        if ($user->role === 'super_admin') {
            $data['gudang_id'] = $request->gudang_id;
        }

        $kontak->update($data);

        return redirect()->route('kontak.index')->with('success', 'Kontak berhasil diperbarui.');
    }

    public function destroy(Kontak $kontak)
    {
        // Hanya super_admin yang bisa hapus kontak
        if (Auth::user()->role !== 'super_admin') {
            return redirect()->route('kontak.index')->with('error', 'Hanya Super Admin yang dapat menghapus data kontak.');
        }

        // TODO: Cek dulu apakah kontak ini dipakai di transaksi
        $kontak->delete();
        return redirect()->route('kontak.index')->with('success', 'Kontak berhasil dihapus.');
    }

    /**
     * Check if user can access a specific kontak.
     */
    private function canAccessKontak($user, $kontak)
    {
        if ($user->role === 'super_admin')
            return true;
        if ($kontak->gudang_id === null)
            return true; // legacy data visible to all

        $gudangIds = $this->getAccessibleGudangIds($user);
        return in_array($kontak->gudang_id, $gudangIds);
    }
}