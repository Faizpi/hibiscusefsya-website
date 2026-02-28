<?php

namespace App\Http\Controllers;

use App\Kontak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class KontakController extends Controller
{
    // Pastikan hanya admin dan spectator yang bisa akses
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = Kontak::query();

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
        return view('kontak.show', compact('kontak'));
    }

    public function print(Kontak $kontak)
    {
        return view('kontak.print', compact('kontak'));
    }

    public function downloadPdf(Kontak $kontak)
    {
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
        // Spectator tidak bisa menyimpan kontak baru
        if (Auth::user()->role === 'spectator') {
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

        Kontak::create($request->all());

        return redirect()->route('kontak.index')->with('success', 'Kontak baru berhasil ditambahkan.');
    }

    public function edit(Kontak $kontak)
    {
        // Hanya super_admin yang bisa edit kontak
        if (Auth::user()->role !== 'super_admin') {
            return redirect()->route('kontak.index')->with('error', 'Hanya Super Admin yang dapat mengubah data kontak.');
        }
        return view('kontak.edit', compact('kontak'));
    }

    public function update(Request $request, Kontak $kontak)
    {
        // Hanya super_admin yang bisa update kontak
        if (Auth::user()->role !== 'super_admin') {
            return redirect()->route('kontak.index')->with('error', 'Hanya Super Admin yang dapat mengubah data kontak.');
        }

        $request->validate([
            'kode_kontak' => 'nullable|string|max:50|unique:kontaks,kode_kontak,' . $kontak->id,
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:kontaks,email,' . $kontak->id,
            'no_telp' => 'nullable|string|max:20',
            'pin' => 'nullable|string|size:6',
            'alamat' => 'nullable|string',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
        ]);

        $kontak->update($request->all());

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
}