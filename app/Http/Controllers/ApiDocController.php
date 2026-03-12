<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiDocController extends Controller
{
    public function index()
    {
        return view('api-docs.index', ['docs' => $this->getApiSpec()]);
    }

    public function json()
    {
        return response()->json($this->getApiSpec(), 200, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function download()
    {
        $json = json_encode($this->getApiSpec(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="hibiscus-efsya-api-v1.json"',
        ]);
    }

    public function downloadPostman()
    {
        $spec = $this->getApiSpec();
        $baseUrl = rtrim(url('/'), '/');

        $collection = [
            'info' => [
                'name' => $spec['info']['title'],
                '_postman_id' => 'hibiscus-efsya-api-v1',
                'description' => $spec['info']['description'],
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => [],
            'variable' => [
                ['key' => 'base_url', 'value' => $baseUrl, 'type' => 'string'],
                ['key' => 'token', 'value' => '', 'type' => 'string'],
            ],
        ];

        foreach ($spec['endpoints'] as $group) {
            $folder = ['name' => $group['group'], 'item' => []];
            foreach ($group['items'] as $ep) {
                $folder['item'][] = $this->buildPostmanItem($ep);
            }
            $collection['item'][] = $folder;
        }

        $json = json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="hibiscus-efsya-api-v1-postman.json"',
        ]);
    }

    private function buildPostmanItem(array $ep)
    {
        $method = $ep['method'];
        $hasFileUpload = false;
        foreach ($ep['body'] ?? [] as $field) {
            if (($field['type'] ?? '') === 'file') {
                $hasFileUpload = true;
                break;
            }
        }

        $headers = [];
        if ($ep['auth'] ?? true) {
            $headers[] = ['key' => 'Authorization', 'value' => 'Bearer {{token}}', 'type' => 'text'];
        }
        if (!$hasFileUpload && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $headers[] = ['key' => 'Content-Type', 'value' => 'application/json', 'type' => 'text'];
        }
        $headers[] = ['key' => 'Accept', 'value' => 'application/json', 'type' => 'text'];

        // Build description with body schema (wajib/opsional)
        $description = $ep['description'];
        if (!empty($ep['roles'])) {
            $description .= "\n\nRoles: " . implode(', ', $ep['roles']);
        }
        if (!empty($ep['body'])) {
            $description .= "\n\n**Request Body:**\n";
            foreach ($ep['body'] as $field) {
                if (strpos($field['name'], '(') !== false) {
                    continue;
                }
                $req = ($field['required'] ?? false) ? '[WAJIB]' : '[opsional]';
                $description .= "\n- `{$field['name']}` ({$field['type']}) {$req}: {$field['description']}";
            }
        }

        $path = $ep['path'];
        $pathParts = array_values(array_filter(explode('/', ltrim($path, '/'))));
        $urlObj = [
            'raw' => '{{base_url}}' . $path,
            'host' => ['{{base_url}}'],
            'path' => $pathParts,
        ];

        if (!empty($ep['params'])) {
            $queryArr = [];
            foreach ($ep['params'] as $p) {
                $queryArr[] = [
                    'key' => $p['name'],
                    'value' => '',
                    'description' => (($p['required'] ?? false) ? '[WAJIB] ' : '[opsional] ') . ($p['description'] ?? ''),
                    'disabled' => !($p['required'] ?? false),
                ];
            }
            $urlObj['query'] = $queryArr;
        }

        $request = [
            'method' => $method,
            'header' => $headers,
            'url' => $urlObj,
            'description' => $description,
        ];

        if (!empty($ep['body']) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            if ($hasFileUpload) {
                $formdata = [];
                foreach ($ep['body'] as $field) {
                    $desc = (($field['required'] ?? false) ? '[WAJIB] ' : '[opsional] ') . ($field['description'] ?? '');
                    if (($field['type'] ?? '') === 'file') {
                        $formdata[] = ['key' => $field['name'], 'type' => 'file', 'src' => '', 'description' => $desc];
                    } else {
                        $val = $this->getExampleValue($field);
                        $strVal = is_bool($val) ? ($val ? 'true' : 'false') : (string) $val;
                        $formdata[] = ['key' => $field['name'], 'value' => $strVal, 'type' => 'text', 'description' => $desc];
                    }
                }
                $request['body'] = ['mode' => 'formdata', 'formdata' => $formdata];
            } else {
                $bodyObj = $this->buildExampleBody($ep['body']);
                $request['body'] = [
                    'mode' => 'raw',
                    'raw' => json_encode($bodyObj, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                    'options' => ['raw' => ['language' => 'json']],
                ];
            }
        }

        return ['name' => $ep['title'], 'request' => $request, 'response' => []];
    }

    private function buildExampleBody(array $bodyFields)
    {
        $result = [];
        $itemFields = [];

        foreach ($bodyFields as $field) {
            $name = $field['name'];
            // Skip placeholder entries like "(sama seperti POST)"
            if (strpos($name, '(') !== false) {
                continue;
            }
            // Skip file fields
            if (($field['type'] ?? '') === 'file' || strpos($name, 'lampiran') !== false) {
                continue;
            }
            // Handle items[].field pattern
            if (preg_match('/^items\[\]\.(.+)$/', $name, $m)) {
                $itemFields[$m[1]] = $this->getExampleValue($field);
                continue;
            }
            // Skip bare array notation
            if (strpos($name, '[]') !== false) {
                continue;
            }
            $result[$name] = $this->getExampleValue($field);
        }

        if (!empty($itemFields)) {
            $result['items'] = [$itemFields];
        }

        return $result;
    }

    private function getExampleValue(array $field)
    {
        $type = $field['type'] ?? 'string';
        $name = $field['name'] ?? '';

        if ($type === 'integer' || $type === 'int') {
            return strpos($name, '_id') !== false ? 1 : 0;
        }
        if ($type === 'number' || $type === 'float') {
            return 0;
        }
        if ($type === 'boolean') {
            return false;
        }
        if (strpos($type, 'Y-m-d') !== false || strpos($name, 'tgl') !== false || strpos($name, 'expired_date') !== false) {
            return date('Y-m-d');
        }
        return '';
    }

    private function getApiSpec()
    {
        return [
            'info' => [
                'title' => 'Hibiscus Efsya Sales API',
                'version' => '1.0.0',
                'description' => 'REST API untuk aplikasi mobile Hibiscus Efsya Sales - Point of Sale & Inventory Management System',
                'base_url' => url('/api/v1'),
            ],
            'authentication' => [
                'type' => 'Bearer Token',
                'description' => 'Semua endpoint (kecuali login) membutuhkan header Authorization: Bearer {token}. Token didapat dari endpoint login dan berlaku 30 hari.',
                'header' => 'Authorization: Bearer {token}',
            ],
            'roles' => [
                ['role' => 'super_admin', 'description' => 'Akses penuh ke semua data dan fitur'],
                ['role' => 'admin', 'description' => 'Akses ke data gudang yang dikelola, bisa approve transaksi'],
                ['role' => 'spectator', 'description' => 'Read-only access ke data gudang yang diassign'],
                ['role' => 'user', 'description' => 'Akses ke transaksi milik sendiri, assigned ke satu gudang'],
            ],
            'common_responses' => [
                '401' => ['message' => 'Unauthenticated / Token invalid atau expired'],
                '403' => ['message' => 'Forbidden / Tidak memiliki akses'],
                '404' => ['message' => 'Data tidak ditemukan'],
                '422' => ['message' => 'Validation error / Data tidak valid'],
                '500' => ['message' => 'Internal server error'],
            ],
            'endpoints' => $this->getEndpoints(),
        ];
    }

    private function getEndpoints()
    {
        return [
            // ===================== AUTH =====================
            [
                'group' => 'Authentication',
                'icon' => 'fa-key',
                'color' => '#ef4444',
                'items' => [
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/login',
                        'title' => 'Login',
                        'description' => 'Login dan dapatkan API token untuk autentikasi selanjutnya.',
                        'auth' => false,
                        'body' => [
                            ['name' => 'email', 'type' => 'string', 'required' => true, 'description' => 'Email user terdaftar'],
                            ['name' => 'password', 'type' => 'string', 'required' => true, 'description' => 'Password user'],
                            ['name' => 'device_name', 'type' => 'string', 'required' => false, 'description' => 'Nama device (default: "mobile")'],
                        ],
                        'response' => '{"message":"Login berhasil.","token":"xxx...","user":{"id":1,"name":"Admin","email":"admin@test.com","role":"admin","alamat":null,"no_telp":null,"gudang_id":1,"current_gudang_id":1}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/logout',
                        'title' => 'Logout',
                        'description' => 'Revoke token saat ini.',
                        'auth' => true,
                        'body' => [],
                        'response' => '{"message":"Logout berhasil."}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/profile',
                        'title' => 'Get Profile',
                        'description' => 'Ambil data profil user beserta gudang aktif.',
                        'auth' => true,
                        'body' => [],
                        'response' => '{"user":{"id":1,"name":"Admin","email":"admin@test.com","role":"admin",...},"gudang":{"id":1,"nama_gudang":"Gudang Utama","alamat_gudang":"..."}}',
                    ],
                    [
                        'method' => 'PUT',
                        'path' => '/api/v1/profile',
                        'title' => 'Update Profile',
                        'description' => 'Update data profil user (nama, alamat, no telp).',
                        'auth' => true,
                        'body' => [
                            ['name' => 'name', 'type' => 'string', 'required' => true, 'description' => 'Nama lengkap'],
                            ['name' => 'alamat', 'type' => 'string', 'required' => false, 'description' => 'Alamat'],
                            ['name' => 'no_telp', 'type' => 'string', 'required' => false, 'description' => 'Nomor telepon'],
                        ],
                        'response' => '{"message":"Profil berhasil diupdate.","user":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/change-password',
                        'title' => 'Ganti Password',
                        'description' => 'Ganti password user.',
                        'auth' => true,
                        'body' => [
                            ['name' => 'current_password', 'type' => 'string', 'required' => true, 'description' => 'Password lama'],
                            ['name' => 'new_password', 'type' => 'string', 'required' => true, 'description' => 'Password baru (min 8 karakter)'],
                            ['name' => 'new_password_confirmation', 'type' => 'string', 'required' => true, 'description' => 'Konfirmasi password baru'],
                        ],
                        'response' => '{"message":"Password berhasil diubah."}',
                    ],
                ],
            ],
            // ===================== DASHBOARD =====================
            [
                'group' => 'Dashboard',
                'icon' => 'fa-tachometer-alt',
                'color' => '#3b82f6',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/dashboard',
                        'title' => 'Dashboard Summary',
                        'description' => 'Data ringkasan dashboard: total penjualan/pembelian/biaya bulan ini, pending approval, recent transactions. Data difilter sesuai role user.',
                        'auth' => true,
                        'params' => [],
                        'response' => '{"penjualan_bulan_ini":15,"total_penjualan_bulan_ini":50000000,"pembelian_bulan_ini":8,"total_pembelian_bulan_ini":30000000,"biaya_bulan_ini":5000000,"pending_approval":3,"recent_penjualan":[...]}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/dashboard/daily-report',
                        'title' => 'Laporan Harian',
                        'description' => 'Laporan harian lengkap semua aktivitas user: penjualan, pembelian, biaya, kunjungan. Data difilter per tanggal dan user yang login. Cocok untuk generate laporan harian sales di mobile.',
                        'auth' => true,
                        'params' => [
                            ['name' => 'date', 'type' => 'date', 'required' => false, 'description' => 'Tanggal laporan (YYYY-MM-DD). Default: hari ini'],
                        ],
                        'response' => '{"date":"2026-03-10","sales_name":"Ahmad","summary":{"total_penjualan":5,"nilai_penjualan":15000000,"total_pembelian":2,"nilai_pembelian":8000000,"total_biaya":1,"nilai_biaya":500000,"total_kunjungan":3,"total_aktivitas":11},"penjualans":[{"id":1,"nomor":"INV-...","pelanggan":"...","grand_total":3000000,"status":"Approved","koordinat":"-6.2,106.8","lampiran_paths":["lampiran_penjualan/INV-xxx-1.jpg"],...}],"pembelians":[...],"biayas":[...],"kunjungans":[...]}',
                    ],
                ],
            ],
            // ===================== GUDANG =====================
            [
                'group' => 'Gudang (Warehouse)',
                'icon' => 'fa-warehouse',
                'color' => '#3B82F6',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/gudang',
                        'title' => 'List Gudang',
                        'description' => 'Daftar gudang yang bisa diakses user. Super admin lihat semua, admin/spectator lihat gudang assign, user lihat gudang sendiri.',
                        'auth' => true,
                        'response' => '[{"id":1,"nama_gudang":"Gudang Utama","alamat_gudang":"Jl. Contoh No.1"}]',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/gudang/switch',
                        'title' => 'Switch Gudang',
                        'description' => 'Ganti gudang aktif (untuk admin/spectator multi-gudang).',
                        'auth' => true,
                        'body' => [
                            ['name' => 'gudang_id', 'type' => 'integer', 'required' => true, 'description' => 'ID gudang tujuan'],
                        ],
                        'response' => '{"message":"Gudang berhasil diganti.","current_gudang":{...}}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/gudang/stok',
                        'title' => 'Stok Gudang',
                        'description' => 'Lihat stok produk di gudang aktif. Role: admin, spectator, super_admin.',
                        'auth' => true,
                        'params' => [
                            ['name' => 'gudang_id', 'type' => 'integer', 'required' => false, 'description' => 'Filter gudang (super_admin only)'],
                        ],
                        'response' => '[{"id":1,"gudang_id":1,"produk_id":1,"stok":100,"stok_penjualan":80,"stok_gratis":10,"stok_sample":10,"produk":{"id":1,"nama_produk":"Produk A"},"gudang":{"id":1,"nama_gudang":"Gudang Utama"}}]',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/gudang/stok-log',
                        'title' => 'Stok Log',
                        'description' => 'Riwayat perubahan stok di gudang. Paginated.',
                        'auth' => true,
                        'params' => [
                            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah per halaman (default: 50)'],
                        ],
                        'response' => '{"data":[{"id":1,"stok_sebelum":100,"stok_sesudah":110,"selisih":10,"keterangan":"...","produk":{...},"gudang":{...},"user":{...}}],"current_page":1,...}',
                    ],
                ],
            ],
            // ===================== PRODUK =====================
            [
                'group' => 'Produk',
                'icon' => 'fa-box',
                'color' => '#f59e0b',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/produk',
                        'title' => 'List Produk',
                        'description' => 'Daftar produk. Difilter sesuai gudang user. Paginated.',
                        'auth' => true,
                        'params' => [
                            ['name' => 'search', 'type' => 'string', 'required' => false, 'description' => 'Cari berdasar nama/kode produk'],
                            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah per halaman (default: 50)'],
                        ],
                        'response' => '{"data":[{"id":1,"nama_produk":"Produk A","item_code":"PRD001","harga":10000,"harga_grosir":8000,"satuan":"Pcs","deskripsi":null}],"current_page":1,...}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/produk/{id}',
                        'title' => 'Detail Produk',
                        'description' => 'Detail produk beserta stok di semua gudang.',
                        'auth' => true,
                        'response' => '{"id":1,"nama_produk":"Produk A","item_code":"PRD001","harga":10000,...,"stok_di_gudang":[{"gudang_id":1,"stok":100,...,"gudang":{"id":1,"nama_gudang":"..."}}]}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/produk',
                        'title' => 'Tambah Produk',
                        'description' => 'Buat produk baru. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'body' => [
                            ['name' => 'nama_produk', 'type' => 'string', 'required' => true, 'description' => 'Nama produk'],
                            ['name' => 'item_code', 'type' => 'string', 'required' => false, 'description' => 'Kode produk (unik)'],
                            ['name' => 'harga', 'type' => 'number', 'required' => true, 'description' => 'Harga satuan'],
                            ['name' => 'harga_grosir', 'type' => 'number', 'required' => false, 'description' => 'Harga grosir'],
                            ['name' => 'satuan', 'type' => 'string', 'required' => true, 'description' => 'Satuan: Pcs, Lusin, atau Karton'],
                            ['name' => 'deskripsi', 'type' => 'string', 'required' => false, 'description' => 'Deskripsi produk'],
                        ],
                        'response' => '{"message":"Produk berhasil dibuat.","data":{...}}',
                    ],
                    [
                        'method' => 'PUT',
                        'path' => '/api/v1/produk/{id}',
                        'title' => 'Update Produk',
                        'description' => 'Edit data produk. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'body' => [
                            ['name' => 'nama_produk', 'type' => 'string', 'required' => true, 'description' => 'Nama produk'],
                            ['name' => 'item_code', 'type' => 'string', 'required' => false, 'description' => 'Kode produk (unik)'],
                            ['name' => 'harga', 'type' => 'number', 'required' => true, 'description' => 'Harga satuan'],
                            ['name' => 'harga_grosir', 'type' => 'number', 'required' => false, 'description' => 'Harga grosir'],
                            ['name' => 'satuan', 'type' => 'string', 'required' => true, 'description' => 'Satuan: Pcs, Lusin, atau Karton'],
                            ['name' => 'deskripsi', 'type' => 'string', 'required' => false, 'description' => 'Deskripsi produk'],
                        ],
                        'response' => '{"message":"Produk berhasil diupdate.","data":{...}}',
                    ],
                    [
                        'method' => 'DELETE',
                        'path' => '/api/v1/produk/{id}',
                        'title' => 'Hapus Produk',
                        'description' => 'Hapus produk. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'response' => '{"message":"Produk berhasil dihapus."}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/produk/stok/{gudangId}',
                        'title' => 'Stok by Gudang',
                        'description' => 'Stok semua produk di gudang tertentu.',
                        'auth' => true,
                        'response' => '[{"id":1,"gudang_id":1,"produk_id":1,"stok":100,"stok_penjualan":80,...,"produk":{"id":1,"nama_produk":"...","harga":10000,...}}]',
                    ],
                ],
            ],
            // ===================== KONTAK =====================
            [
                'group' => 'Kontak',
                'icon' => 'fa-address-book',
                'color' => '#06b6d4',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/kontak',
                        'title' => 'List Kontak',
                        'description' => 'Daftar kontak (pelanggan/supplier). Difilter sesuai gudang user. Paginated.',
                        'auth' => true,
                        'params' => [
                            ['name' => 'search', 'type' => 'string', 'required' => false, 'description' => 'Cari berdasar nama/kode/no_telp'],
                            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah per halaman (default: 50)'],
                        ],
                        'response' => '{"data":[{"id":1,"kode_kontak":"KT001","nama":"PT ABC","email":"abc@test.com","no_telp":"0812...","alamat":"...","diskon_persen":5}],...}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/kontak/{id}',
                        'title' => 'Detail Kontak',
                        'description' => 'Detail data kontak.',
                        'auth' => true,
                        'response' => '{"id":1,"kode_kontak":"KT001","nama":"PT ABC",...}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/kontak',
                        'title' => 'Tambah Kontak',
                        'description' => 'Buat kontak baru.',
                        'auth' => true,
                        'body' => [
                            ['name' => 'nama', 'type' => 'string', 'required' => true, 'description' => 'Nama kontak'],
                            ['name' => 'no_telp', 'type' => 'string', 'required' => false, 'description' => 'Nomor telepon'],
                            ['name' => 'email', 'type' => 'string', 'required' => false, 'description' => 'Email'],
                            ['name' => 'alamat', 'type' => 'string', 'required' => false, 'description' => 'Alamat'],
                            ['name' => 'diskon_persen', 'type' => 'number', 'required' => false, 'description' => 'Diskon default (%)'],
                            ['name' => 'gudang_id', 'type' => 'integer', 'required' => false, 'description' => 'Assign ke gudang tertentu'],
                        ],
                        'response' => '{"message":"Kontak berhasil dibuat.","data":{...}}',
                    ],
                    [
                        'method' => 'PUT',
                        'path' => '/api/v1/kontak/{id}',
                        'title' => 'Update Kontak',
                        'description' => 'Edit data kontak.',
                        'auth' => true,
                        'body' => [
                            ['name' => 'nama', 'type' => 'string', 'required' => true, 'description' => 'Nama kontak'],
                            ['name' => 'no_telp', 'type' => 'string', 'required' => false, 'description' => 'Nomor telepon'],
                            ['name' => 'email', 'type' => 'string', 'required' => false, 'description' => 'Email'],
                            ['name' => 'alamat', 'type' => 'string', 'required' => false, 'description' => 'Alamat'],
                            ['name' => 'diskon_persen', 'type' => 'number', 'required' => false, 'description' => 'Diskon default (%)'],
                        ],
                        'response' => '{"message":"Kontak berhasil diupdate.","data":{...}}',
                    ],
                    [
                        'method' => 'DELETE',
                        'path' => '/api/v1/kontak/{id}',
                        'title' => 'Hapus Kontak',
                        'description' => 'Hapus data kontak.',
                        'auth' => true,
                        'response' => '{"message":"Kontak berhasil dihapus."}',
                    ],
                ],
            ],
            // ===================== PENJUALAN =====================
            [
                'group' => 'Penjualan (Sales)',
                'icon' => 'fa-shopping-cart',
                'color' => '#10b981',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/penjualan',
                        'title' => 'List Penjualan',
                        'description' => 'Daftar transaksi penjualan. Difilter sesuai role dan gudang. Paginated.',
                        'auth' => true,
                        'params' => [
                            ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filter: Pending, Approved, Lunas, Canceled'],
                            ['name' => 'search', 'type' => 'string', 'required' => false, 'description' => 'Cari berdasar nomor/pelanggan'],
                            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah per halaman (default: 20)'],
                        ],
                        'response' => '{"data":[{"id":1,"nomor":"INV-20260301-1-001","pelanggan":"PT ABC","grand_total":1500000,"status":"Approved",...,"user":{"id":1,"name":"Admin"},"gudang":{"id":1,"nama_gudang":"..."},"approver":{"id":2,"name":"..."}}],...}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/penjualan/{id}',
                        'title' => 'Detail Penjualan',
                        'description' => 'Detail transaksi penjualan beserta items.',
                        'auth' => true,
                        'response' => '{"id":1,"nomor":"INV-...","pelanggan":"...","items":[{"id":1,"produk_id":1,"nama_produk":"Produk A","kuantitas":10,"harga_satuan":10000,"diskon":0,"batch_number":"B001","expired_date":"2026-12-31","total":100000,"produk":{...}}],...}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/penjualan',
                        'title' => 'Buat Penjualan',
                        'description' => 'Buat transaksi penjualan baru. Stok akan divalidasi otomatis. Nomor invoice auto-generated.',
                        'auth' => true,
                        'roles' => ['user', 'admin', 'super_admin'],
                        'body' => [
                            ['name' => 'pelanggan', 'type' => 'string', 'required' => true, 'description' => 'Nama pelanggan'],
                            ['name' => 'tgl_transaksi', 'type' => 'date', 'required' => true, 'description' => 'Tanggal transaksi (YYYY-MM-DD)'],
                            ['name' => 'syarat_pembayaran', 'type' => 'string', 'required' => true, 'description' => 'Cash, Net 7, Net 14, Net 30, Net 60'],
                            ['name' => 'gudang_id', 'type' => 'integer', 'required' => true, 'description' => 'ID gudang'],
                            ['name' => 'tax_percentage', 'type' => 'number', 'required' => true, 'description' => 'Persentase pajak'],
                            ['name' => 'diskon_akhir', 'type' => 'number', 'required' => false, 'description' => 'Diskon akhir (Rp)'],
                            ['name' => 'email', 'type' => 'string', 'required' => false, 'description' => 'Email pelanggan'],
                            ['name' => 'alamat_penagihan', 'type' => 'string', 'required' => false, 'description' => 'Alamat penagihan'],
                            ['name' => 'no_referensi', 'type' => 'string', 'required' => false, 'description' => 'No referensi/PO'],
                            ['name' => 'tag', 'type' => 'string', 'required' => false, 'description' => 'Tag/label'],
                            ['name' => 'koordinat', 'type' => 'string', 'required' => false, 'description' => 'Koordinat GPS (lat,lng)'],
                            ['name' => 'memo', 'type' => 'string', 'required' => false, 'description' => 'Catatan/memo'],
                            ['name' => 'items', 'type' => 'array', 'required' => true, 'description' => 'Array item penjualan'],
                            ['name' => 'items[].produk_id', 'type' => 'integer', 'required' => true, 'description' => 'ID produk'],
                            ['name' => 'items[].kuantitas', 'type' => 'number', 'required' => true, 'description' => 'Jumlah (min: 1)'],
                            ['name' => 'items[].harga_satuan', 'type' => 'number', 'required' => true, 'description' => 'Harga per unit'],
                            ['name' => 'items[].diskon', 'type' => 'number', 'required' => false, 'description' => 'Diskon item (%)'],
                            ['name' => 'items[].batch_number', 'type' => 'string', 'required' => false, 'description' => 'Nomor batch produk'],
                            ['name' => 'items[].expired_date', 'type' => 'string (Y-m-d)', 'required' => false, 'description' => 'Tanggal kadaluarsa'],
                            ['name' => 'items[].deskripsi', 'type' => 'string', 'required' => false, 'description' => 'Deskripsi item'],
                            ['name' => 'items[].unit', 'type' => 'string', 'required' => false, 'description' => 'Unit/satuan custom'],
                            ['name' => 'lampiran[]', 'type' => 'file', 'required' => false, 'description' => 'File lampiran (multipart/form-data). Max 2MB/file. Format: jpg,jpeg,png,pdf,zip,doc,docx'],
                        ],
                        'response' => '{"message":"Penjualan berhasil dibuat.","data":{"id":1,"nomor":"INV-...","status":"Pending",...,"items":[...]}}',
                    ],
                    [
                        'method' => 'PUT',
                        'path' => '/api/v1/penjualan/{id}',
                        'title' => 'Update Penjualan',
                        'description' => 'Edit transaksi penjualan. Role: super_admin. Body sama dengan Buat Penjualan. Lampiran baru di-append ke existing.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'body' => [
                            ['name' => '(sama seperti POST)', 'type' => '-', 'required' => false, 'description' => 'Semua field sama seperti Buat Penjualan'],
                        ],
                        'response' => '{"message":"Penjualan berhasil diperbarui.","data":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/penjualan/{id}/approve',
                        'title' => 'Approve Penjualan',
                        'description' => 'Setujui transaksi penjualan. Role: admin, super_admin.',
                        'auth' => true,
                        'roles' => ['admin', 'super_admin'],
                        'response' => '{"message":"Penjualan berhasil di-approve.","data":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/penjualan/{id}/cancel',
                        'title' => 'Cancel Penjualan',
                        'description' => 'Batalkan transaksi penjualan. User hanya bisa cancel milik sendiri.',
                        'auth' => true,
                        'response' => '{"message":"Penjualan berhasil dibatalkan."}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/penjualan/{id}/uncancel',
                        'title' => 'Uncancel Penjualan',
                        'description' => 'Kembalikan transaksi yang dibatalkan ke status Pending. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'response' => '{"message":"Transaksi berhasil di-uncancel. Status kembali ke Pending.","data":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/penjualan/{id}/mark-paid',
                        'title' => 'Tandai Lunas',
                        'description' => 'Tandai penjualan yang sudah Approved menjadi Lunas. Role: admin, super_admin.',
                        'auth' => true,
                        'roles' => ['admin', 'super_admin'],
                        'response' => '{"message":"Penjualan ditandai LUNAS.","data":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/penjualan/{id}/unmark-paid',
                        'title' => 'Batalkan Lunas',
                        'description' => 'Kembalikan status Lunas ke Approved. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'response' => '{"message":"Status penjualan dikembalikan ke Approved.","data":{...}}',
                    ],
                ],
            ],
            // ===================== PEMBELIAN =====================
            [
                'group' => 'Pembelian (Purchase)',
                'icon' => 'fa-truck',
                'color' => '#6366f1',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/pembelian',
                        'title' => 'List Pembelian',
                        'description' => 'Daftar transaksi pembelian. Difilter sesuai role dan gudang. Paginated.',
                        'auth' => true,
                        'params' => [
                            ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filter: Pending, Approved, Canceled'],
                            ['name' => 'search', 'type' => 'string', 'required' => false, 'description' => 'Cari berdasar nomor'],
                            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah per halaman (default: 20)'],
                        ],
                        'response' => '{"data":[{"id":1,"nomor":"PR-20260301-1-001","gudang_id":1,"grand_total":5000000,"status":"Approved",...}],...}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/pembelian/{id}',
                        'title' => 'Detail Pembelian',
                        'description' => 'Detail transaksi pembelian beserta items.',
                        'auth' => true,
                        'response' => '{"id":1,"nomor":"PR-...","items":[{"produk_id":1,"nama_produk":"...","kuantitas":50,"harga_satuan":5000,...}],...}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/pembelian',
                        'title' => 'Buat Pembelian',
                        'description' => 'Buat transaksi pembelian baru.',
                        'auth' => true,
                        'roles' => ['user', 'admin', 'super_admin'],
                        'body' => [
                            ['name' => 'tgl_transaksi', 'type' => 'date', 'required' => true, 'description' => 'Tanggal transaksi (YYYY-MM-DD)'],
                            ['name' => 'syarat_pembayaran', 'type' => 'string', 'required' => true, 'description' => 'Syarat pembayaran'],
                            ['name' => 'gudang_id', 'type' => 'integer', 'required' => true, 'description' => 'ID gudang'],
                            ['name' => 'tax_percentage', 'type' => 'number', 'required' => true, 'description' => 'Persentase pajak'],
                            ['name' => 'diskon_akhir', 'type' => 'number', 'required' => false, 'description' => 'Diskon akhir (Rp)'],
                            ['name' => 'tgl_jatuh_tempo', 'type' => 'date', 'required' => false, 'description' => 'Tanggal jatuh tempo'],
                            ['name' => 'urgensi', 'type' => 'string', 'required' => true, 'description' => 'Level urgensi (required)'],
                            ['name' => 'tahun_anggaran', 'type' => 'string', 'required' => false, 'description' => 'Tahun anggaran'],
                            ['name' => 'tag', 'type' => 'string', 'required' => false, 'description' => 'Tag/label'],
                            ['name' => 'koordinat', 'type' => 'string', 'required' => false, 'description' => 'Koordinat GPS'],
                            ['name' => 'memo', 'type' => 'string', 'required' => false, 'description' => 'Catatan'],
                            ['name' => 'items', 'type' => 'array', 'required' => true, 'description' => 'Array item pembelian'],
                            ['name' => 'items[].produk_id', 'type' => 'integer', 'required' => true, 'description' => 'ID produk'],
                            ['name' => 'items[].kuantitas', 'type' => 'number', 'required' => true, 'description' => 'Jumlah'],
                            ['name' => 'items[].harga_satuan', 'type' => 'number', 'required' => true, 'description' => 'Harga per unit'],
                            ['name' => 'items[].diskon', 'type' => 'number', 'required' => false, 'description' => 'Diskon item (%)'],
                            ['name' => 'items[].deskripsi', 'type' => 'string', 'required' => false, 'description' => 'Deskripsi item'],
                            ['name' => 'items[].unit', 'type' => 'string', 'required' => false, 'description' => 'Unit/satuan custom'],
                            ['name' => 'lampiran[]', 'type' => 'file', 'required' => false, 'description' => 'File lampiran (multipart/form-data). Max 2MB/file. Format: jpg,jpeg,png,pdf,zip,doc,docx'],
                        ],
                        'response' => '{"message":"Pembelian berhasil dibuat.","data":{"id":1,"nomor":"PR-...","items":[...],...}}',
                    ],
                    [
                        'method' => 'PUT',
                        'path' => '/api/v1/pembelian/{id}',
                        'title' => 'Update Pembelian',
                        'description' => 'Edit transaksi pembelian. Role: super_admin. Body sama dengan Buat Pembelian. Lampiran baru di-append ke existing.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'body' => [
                            ['name' => '(sama seperti POST)', 'type' => '-', 'required' => false, 'description' => 'Semua field sama seperti Buat Pembelian'],
                        ],
                        'response' => '{"message":"Pembelian berhasil diperbarui.","data":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/pembelian/{id}/approve',
                        'title' => 'Approve Pembelian',
                        'description' => 'Setujui transaksi pembelian. Role: admin, super_admin.',
                        'auth' => true,
                        'roles' => ['admin', 'super_admin'],
                        'response' => '{"message":"Pembelian berhasil di-approve.","data":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/pembelian/{id}/cancel',
                        'title' => 'Cancel Pembelian',
                        'description' => 'Batalkan transaksi pembelian.',
                        'auth' => true,
                        'response' => '{"message":"Pembelian berhasil dibatalkan."}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/pembelian/{id}/uncancel',
                        'title' => 'Uncancel Pembelian',
                        'description' => 'Kembalikan pembelian yang dibatalkan ke status Pending. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'response' => '{"message":"Pembelian berhasil di-uncancel.","data":{...}}',
                    ],
                ],
            ],
            // ===================== BIAYA =====================
            [
                'group' => 'Biaya (Expenses)',
                'icon' => 'fa-file-invoice-dollar',
                'color' => '#60A5FA',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/biaya',
                        'title' => 'List Biaya',
                        'description' => 'Daftar transaksi biaya. Super admin lihat semua, user lihat milik sendiri. Paginated.',
                        'auth' => true,
                        'params' => [
                            ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filter: Pending, Approved, Canceled'],
                            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah per halaman (default: 20)'],
                        ],
                        'response' => '{"data":[{"id":1,"nomor":"EXP-...","jenis_biaya":"Operasional","grand_total":500000,"status":"Approved",...}],...}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/biaya/{id}',
                        'title' => 'Detail Biaya',
                        'description' => 'Detail transaksi biaya beserta items.',
                        'auth' => true,
                        'response' => '{"id":1,"nomor":"EXP-...","items":[{"deskripsi":"Transport","jumlah":100000}],...}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/biaya',
                        'title' => 'Buat Biaya',
                        'description' => 'Buat transaksi biaya baru.',
                        'auth' => true,
                        'roles' => ['user', 'admin', 'super_admin'],
                        'body' => [
                            ['name' => 'jenis_biaya', 'type' => 'string', 'required' => true, 'description' => 'Jenis biaya'],
                            ['name' => 'tgl_transaksi', 'type' => 'date', 'required' => true, 'description' => 'Tanggal transaksi (YYYY-MM-DD)'],
                            ['name' => 'cara_pembayaran', 'type' => 'string', 'required' => true, 'description' => 'Cara pembayaran'],
                            ['name' => 'bayar_dari', 'type' => 'string', 'required' => false, 'description' => 'Sumber pembayaran'],
                            ['name' => 'penerima', 'type' => 'string', 'required' => false, 'description' => 'Penerima'],
                            ['name' => 'alamat_penagihan', 'type' => 'string', 'required' => false, 'description' => 'Alamat'],
                            ['name' => 'tag', 'type' => 'string', 'required' => false, 'description' => 'Tag/label'],
                            ['name' => 'koordinat', 'type' => 'string', 'required' => false, 'description' => 'Koordinat GPS'],
                            ['name' => 'memo', 'type' => 'string', 'required' => false, 'description' => 'Catatan'],
                            ['name' => 'tax_percentage', 'type' => 'number', 'required' => false, 'description' => 'Persentase pajak (default: 0)'],
                            ['name' => 'items', 'type' => 'array', 'required' => true, 'description' => 'Array item biaya'],
                            ['name' => 'items[].deskripsi', 'type' => 'string', 'required' => true, 'description' => 'Deskripsi biaya'],
                            ['name' => 'items[].jumlah', 'type' => 'number', 'required' => true, 'description' => 'Jumlah (Rp)'],
                            ['name' => 'items[].kategori', 'type' => 'string', 'required' => true, 'description' => 'Kategori biaya'],
                            ['name' => 'lampiran[]', 'type' => 'file', 'required' => false, 'description' => 'File lampiran (multipart/form-data). Max 2MB/file. Format: jpg,jpeg,png,pdf,zip,doc,docx'],
                        ],
                        'response' => '{"message":"Biaya berhasil dibuat.","data":{"id":1,"nomor":"EXP-...","items":[...],...}}',
                    ],
                    [
                        'method' => 'PUT',
                        'path' => '/api/v1/biaya/{id}',
                        'title' => 'Update Biaya',
                        'description' => 'Edit transaksi biaya. Role: super_admin. Body sama dengan Buat Biaya. Lampiran baru di-append ke existing.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'body' => [
                            ['name' => '(sama seperti POST)', 'type' => '-', 'required' => false, 'description' => 'Semua field sama seperti Buat Biaya'],
                        ],
                        'response' => '{"message":"Biaya berhasil diperbarui.","data":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/biaya/{id}/approve',
                        'title' => 'Approve Biaya',
                        'description' => 'Setujui transaksi biaya. Role: admin, super_admin.',
                        'auth' => true,
                        'roles' => ['admin', 'super_admin'],
                        'response' => '{"message":"Biaya berhasil di-approve.","data":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/biaya/{id}/cancel',
                        'title' => 'Cancel Biaya',
                        'description' => 'Batalkan transaksi biaya. Approved hanya bisa dicancel super_admin.',
                        'auth' => true,
                        'roles' => ['admin', 'super_admin'],
                        'response' => '{"message":"Biaya berhasil dibatalkan."}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/biaya/{id}/uncancel',
                        'title' => 'Uncancel Biaya',
                        'description' => 'Kembalikan biaya yang dibatalkan ke status Pending. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'response' => '{"message":"Biaya berhasil di-uncancel.","data":{...}}',
                    ],
                ],
            ],
            // ===================== KUNJUNGAN =====================
            [
                'group' => 'Kunjungan (Visit)',
                'icon' => 'fa-map-marker-alt',
                'color' => '#14b8a6',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/kunjungan',
                        'title' => 'List Kunjungan',
                        'description' => 'Daftar kunjungan. Difilter sesuai role dan gudang. Paginated.',
                        'auth' => true,
                        'params' => [
                            ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filter: Pending, Approved, Canceled'],
                            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah per halaman (default: 20)'],
                        ],
                        'response' => '{"data":[{"id":1,"nomor":"VST-...","tujuan":"Follow up order","tgl_kunjungan":"2026-03-01","status":"Approved",...}],...}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/kunjungan/{id}',
                        'title' => 'Detail Kunjungan',
                        'description' => 'Detail kunjungan beserta items produk yang dibawa.',
                        'auth' => true,
                        'response' => '{"id":1,"nomor":"VST-...","tujuan":"...","kontak":{"id":1,"nama":"..."},"items":[{"produk_id":1,"nama_produk":"...","kuantitas":5,...}],...}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/kunjungan',
                        'title' => 'Buat Kunjungan',
                        'description' => 'Buat catatan kunjungan baru.',
                        'auth' => true,
                        'roles' => ['user', 'admin', 'super_admin'],
                        'body' => [
                            ['name' => 'kontak_id', 'type' => 'integer', 'required' => true, 'description' => 'ID kontak yang dikunjungi'],
                            ['name' => 'gudang_id', 'type' => 'integer', 'required' => true, 'description' => 'ID gudang'],
                            ['name' => 'tgl_kunjungan', 'type' => 'date', 'required' => true, 'description' => 'Tanggal kunjungan (YYYY-MM-DD)'],
                            ['name' => 'tujuan', 'type' => 'string', 'required' => true, 'description' => 'Tujuan kunjungan'],
                            ['name' => 'koordinat', 'type' => 'string', 'required' => false, 'description' => 'Koordinat GPS'],
                            ['name' => 'memo', 'type' => 'string', 'required' => false, 'description' => 'Catatan'],
                            ['name' => 'items', 'type' => 'array', 'required' => false, 'description' => 'Produk yang dibawa (optional)'],
                            ['name' => 'items[].produk_id', 'type' => 'integer', 'required' => true, 'description' => 'ID produk'],
                            ['name' => 'items[].kuantitas', 'type' => 'number', 'required' => true, 'description' => 'Jumlah'],
                            ['name' => 'items[].tipe_stok', 'type' => 'string', 'required' => false, 'description' => 'Tipe: stok, gratis, sample'],
                            ['name' => 'items[].keterangan', 'type' => 'string', 'required' => false, 'description' => 'Keterangan item'],
                            ['name' => 'lampiran[]', 'type' => 'file', 'required' => false, 'description' => 'File lampiran (multipart/form-data). Max 2MB/file. Format: jpg,jpeg,png,pdf,zip,doc,docx'],
                        ],
                        'response' => '{"message":"Kunjungan berhasil dibuat.","data":{"id":1,"nomor":"VST-...",...}}',
                    ],
                    [
                        'method' => 'PUT',
                        'path' => '/api/v1/kunjungan/{id}',
                        'title' => 'Update Kunjungan',
                        'description' => 'Edit data kunjungan. Role: super_admin. Body sama dengan Buat Kunjungan. Lampiran baru di-append ke existing.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'body' => [
                            ['name' => '(sama seperti POST)', 'type' => '-', 'required' => false, 'description' => 'Semua field sama seperti Buat Kunjungan'],
                        ],
                        'response' => '{"message":"Kunjungan berhasil diperbarui.","data":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/kunjungan/{id}/approve',
                        'title' => 'Approve Kunjungan',
                        'description' => 'Setujui kunjungan. Role: admin, super_admin.',
                        'auth' => true,
                        'roles' => ['admin', 'super_admin'],
                        'response' => '{"message":"Kunjungan berhasil di-approve.","data":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/kunjungan/{id}/cancel',
                        'title' => 'Cancel Kunjungan',
                        'description' => 'Batalkan kunjungan.',
                        'auth' => true,
                        'roles' => ['admin', 'super_admin'],
                        'response' => '{"message":"Kunjungan berhasil dibatalkan."}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/kunjungan/{id}/uncancel',
                        'title' => 'Uncancel Kunjungan',
                        'description' => 'Kembalikan kunjungan yang dibatalkan ke status Pending. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'response' => '{"message":"Kunjungan berhasil di-uncancel.","data":{...}}',
                    ],
                ],
            ],
            // ===================== PEMBAYARAN =====================
            [
                'group' => 'Pembayaran (Payment)',
                'icon' => 'fa-money-bill-wave',
                'color' => '#22c55e',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/pembayaran',
                        'title' => 'List Pembayaran',
                        'description' => 'Daftar pembayaran. Difilter sesuai role dan gudang. Paginated.',
                        'auth' => true,
                        'params' => [
                            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah per halaman (default: 20)'],
                        ],
                        'response' => '{"data":[{"id":1,"nomor":"PAY-...","jumlah_bayar":500000,"metode_pembayaran":"Transfer","status":"Approved",...,"penjualan":{"id":1,"nomor":"INV-...",...}}],...}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/pembayaran/{id}',
                        'title' => 'Detail Pembayaran',
                        'description' => 'Detail pembayaran beserta data penjualan terkait.',
                        'auth' => true,
                        'response' => '{"id":1,"nomor":"PAY-...","penjualan":{"id":1,"nomor":"INV-...","items":[...]},...}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/pembayaran',
                        'title' => 'Buat Pembayaran',
                        'description' => 'Buat pembayaran untuk invoice penjualan.',
                        'auth' => true,
                        'body' => [
                            ['name' => 'penjualan_id', 'type' => 'integer', 'required' => true, 'description' => 'ID penjualan (invoice)'],
                            ['name' => 'tgl_pembayaran', 'type' => 'date', 'required' => true, 'description' => 'Tanggal pembayaran (YYYY-MM-DD)'],
                            ['name' => 'metode_pembayaran', 'type' => 'string', 'required' => true, 'description' => 'Metode: Transfer, Cash, dll'],
                            ['name' => 'jumlah_bayar', 'type' => 'number', 'required' => true, 'description' => 'Jumlah pembayaran (min: 1)'],
                            ['name' => 'keterangan', 'type' => 'string', 'required' => false, 'description' => 'Keterangan'],
                        ],
                        'response' => '{"message":"Pembayaran berhasil dibuat.","data":{"id":1,"nomor":"PAY-...",...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/pembayaran/{id}/approve',
                        'title' => 'Approve Pembayaran',
                        'description' => 'Setujui pembayaran. Jika total bayar >= grand_total, status penjualan menjadi Lunas. Role: admin, super_admin.',
                        'auth' => true,
                        'roles' => ['admin', 'super_admin'],
                        'response' => '{"message":"Pembayaran berhasil di-approve.","data":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/pembayaran/{id}/cancel',
                        'title' => 'Cancel Pembayaran',
                        'description' => 'Batalkan pembayaran. Approved hanya bisa dicancel super_admin. Jika penjualan Lunas, dikembalikan ke Approved.',
                        'auth' => true,
                        'roles' => ['admin', 'super_admin'],
                        'response' => '{"message":"Pembayaran berhasil dibatalkan."}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/pembayaran/{id}/uncancel',
                        'title' => 'Uncancel Pembayaran',
                        'description' => 'Kembalikan pembayaran yang dibatalkan ke status Pending. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'response' => '{"message":"Pembayaran berhasil di-uncancel.","data":{...}}',
                    ],
                ],
            ],
            // ===================== PENERIMAAN BARANG =====================
            [
                'group' => 'Penerimaan Barang (Goods Receipt)',
                'icon' => 'fa-dolly',
                'color' => '#f97316',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/penerimaan-barang',
                        'title' => 'List Penerimaan',
                        'description' => 'Daftar penerimaan barang. Difilter sesuai role dan gudang. Paginated.',
                        'auth' => true,
                        'params' => [
                            ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filter: Pending, Approved, Canceled'],
                            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah per halaman (default: 20)'],
                        ],
                        'response' => '{"data":[{"id":1,"nomor":"RCV-...","tgl_penerimaan":"2026-03-01","status":"Approved",...}],...}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/penerimaan-barang/{id}',
                        'title' => 'Detail Penerimaan',
                        'description' => 'Detail penerimaan barang beserta items (qty diterima, qty reject, batch, expired).',
                        'auth' => true,
                        'response' => '{"id":1,"nomor":"RCV-...","items":[{"produk_id":1,"qty_diterima":50,"qty_reject":2,"tipe_stok":"penjualan","batch_number":"B001","expired_date":"2027-01-01",...}],...}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/penerimaan-barang',
                        'title' => 'Buat Penerimaan',
                        'description' => 'Buat penerimaan barang dari pembelian. Jika super_admin, langsung Approved dan stok otomatis bertambah.',
                        'auth' => true,
                        'roles' => ['user', 'admin', 'super_admin'],
                        'body' => [
                            ['name' => 'gudang_id', 'type' => 'integer', 'required' => true, 'description' => 'ID gudang'],
                            ['name' => 'pembelian_id', 'type' => 'integer', 'required' => true, 'description' => 'ID pembelian terkait'],
                            ['name' => 'tgl_penerimaan', 'type' => 'date', 'required' => true, 'description' => 'Tanggal penerimaan (YYYY-MM-DD)'],
                            ['name' => 'no_surat_jalan', 'type' => 'string', 'required' => false, 'description' => 'Nomor surat jalan'],
                            ['name' => 'keterangan', 'type' => 'string', 'required' => false, 'description' => 'Keterangan'],
                            ['name' => 'items', 'type' => 'array', 'required' => true, 'description' => 'Array item penerimaan'],
                            ['name' => 'items[].produk_id', 'type' => 'integer', 'required' => true, 'description' => 'ID produk'],
                            ['name' => 'items[].qty_diterima', 'type' => 'integer', 'required' => true, 'description' => 'Jumlah barang diterima'],
                            ['name' => 'items[].qty_reject', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah barang ditolak'],
                            ['name' => 'items[].tipe_stok', 'type' => 'string', 'required' => false, 'description' => 'Tipe: penjualan, gratis, sample'],
                            ['name' => 'items[].batch_number', 'type' => 'string', 'required' => false, 'description' => 'Nomor batch'],
                            ['name' => 'items[].expired_date', 'type' => 'date', 'required' => false, 'description' => 'Tanggal kadaluarsa'],
                        ],
                        'response' => '{"message":"Penerimaan barang berhasil dibuat.","data":{"id":1,"nomor":"RCV-...","items":[...],...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/penerimaan-barang/{id}/approve',
                        'title' => 'Approve Penerimaan',
                        'description' => 'Setujui penerimaan barang. Stok otomatis bertambah sesuai qty_diterima. Role: admin, super_admin.',
                        'auth' => true,
                        'roles' => ['admin', 'super_admin'],
                        'response' => '{"message":"Penerimaan barang berhasil di-approve dan stok ditambahkan.","data":{...}}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/penerimaan-barang/{id}/cancel',
                        'title' => 'Cancel Penerimaan',
                        'description' => 'Batalkan penerimaan barang. Jika sudah Approved, stok dikurangi kembali (super_admin only).',
                        'auth' => true,
                        'roles' => ['admin', 'super_admin'],
                        'response' => '{"message":"Penerimaan barang berhasil dibatalkan."}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/penerimaan-barang/pembelian-by-gudang/{gudangId}',
                        'title' => 'Pembelian by Gudang',
                        'description' => 'Daftar pembelian yang masih memiliki item belum diterima di gudang tertentu.',
                        'auth' => true,
                        'response' => '[{"id":1,"nomor":"PR-...","tgl_transaksi":"2026-03-01","status":"Approved","total_items":5}]',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/penerimaan-barang/pembelian-detail/{id}',
                        'title' => 'Detail Pembelian untuk Penerimaan',
                        'description' => 'Detail item pembelian beserta qty yang sudah diterima dan sisa yang harus diterima.',
                        'auth' => true,
                        'response' => '{"id":1,"nomor":"PR-...","items":[{"produk_id":1,"nama_produk":"...","qty_pesan":100,"qty_diterima":50,"qty_sisa":50,"satuan":"Pcs"}]}',
                    ],
                ],
            ],
            // ===================== STOK =====================
            [
                'group' => 'Stok (Inventory)',
                'icon' => 'fa-boxes',
                'color' => '#a855f7',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/stok',
                        'title' => 'List Stok per Gudang',
                        'description' => 'Data stok semua gudang beserta produknya. Role: admin, spectator, super_admin.',
                        'auth' => true,
                        'roles' => ['admin', 'spectator', 'super_admin'],
                        'response' => '[{"id":1,"nama_gudang":"Gudang Utama","produk_stok":[{"produk_id":1,"stok":100,"stok_penjualan":80,"stok_gratis":10,"stok_sample":10,"produk":{...}}]}]',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/stok',
                        'title' => 'Adjust Stok Manual',
                        'description' => 'Ubah stok produk secara manual. Perubahan akan dicatat di log. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'body' => [
                            ['name' => 'gudang_id', 'type' => 'integer', 'required' => true, 'description' => 'ID gudang'],
                            ['name' => 'produk_id', 'type' => 'integer', 'required' => true, 'description' => 'ID produk'],
                            ['name' => 'stok_penjualan', 'type' => 'integer', 'required' => true, 'description' => 'Stok penjualan baru'],
                            ['name' => 'stok_gratis', 'type' => 'integer', 'required' => true, 'description' => 'Stok gratis baru'],
                            ['name' => 'stok_sample', 'type' => 'integer', 'required' => true, 'description' => 'Stok sample baru'],
                            ['name' => 'keterangan', 'type' => 'string', 'required' => false, 'description' => 'Alasan perubahan'],
                        ],
                        'response' => '{"message":"Stok berhasil diperbarui.","data":{...}}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/stok/log',
                        'title' => 'Riwayat Stok',
                        'description' => 'Riwayat perubahan stok. Bisa difilter per gudang, produk, dan tanggal. Role: admin, super_admin.',
                        'auth' => true,
                        'roles' => ['admin', 'super_admin'],
                        'params' => [
                            ['name' => 'gudang_id', 'type' => 'integer', 'required' => false, 'description' => 'Filter gudang'],
                            ['name' => 'produk_id', 'type' => 'integer', 'required' => false, 'description' => 'Filter produk'],
                            ['name' => 'tanggal_dari', 'type' => 'date', 'required' => false, 'description' => 'Filter dari tanggal (YYYY-MM-DD)'],
                            ['name' => 'tanggal_sampai', 'type' => 'date', 'required' => false, 'description' => 'Filter sampai tanggal (YYYY-MM-DD)'],
                            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah per halaman (default: 50)'],
                        ],
                        'response' => '{"data":[{"id":1,"produk_nama":"...","gudang_nama":"...","user_nama":"...","stok_sebelum":100,"stok_sesudah":110,"selisih":10,"keterangan":"..."}],...}',
                    ],
                ],
            ],
            // ===================== USER MANAGEMENT =====================
            [
                'group' => 'Lampiran (Attachments)',
                'icon' => 'fa-paperclip',
                'color' => '#ec4899',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/lampiran/download',
                        'title' => 'Download Lampiran',
                        'description' => 'Download file lampiran dari transaksi. Path didapat dari field lampiran_paths pada data transaksi.',
                        'auth' => true,
                        'params' => [
                            ['name' => 'path', 'type' => 'string', 'required' => true, 'description' => 'Path lampiran dari lampiran_paths. Contoh: lampiran_penjualan/INV-xxx-1.jpg'],
                        ],
                        'response' => 'Binary file download (image/pdf/document)',
                    ],
                    [
                        'method' => 'INFO',
                        'path' => '(upload via store)',
                        'title' => 'Upload Lampiran',
                        'description' => 'Upload lampiran dilakukan saat membuat transaksi (POST penjualan/pembelian/biaya/kunjungan). Gunakan multipart/form-data dan kirim file sebagai field lampiran[]. Max 2MB per file. Format: jpg, jpeg, png, pdf, zip, doc, docx.',
                        'auth' => true,
                        'body' => [
                            ['name' => 'lampiran[]', 'type' => 'file', 'required' => false, 'description' => 'Array file lampiran. Kirim sebagai multipart/form-data. Max 2MB per file.'],
                        ],
                        'response' => 'File tersimpan dan path ditambahkan ke lampiran_paths pada transaksi.',
                    ],
                ],
            ],
            [
                'group' => 'User Management',
                'icon' => 'fa-users-cog',
                'color' => '#64748b',
                'items' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/users',
                        'title' => 'List Users',
                        'description' => 'Daftar semua user. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'params' => [
                            ['name' => 'role', 'type' => 'string', 'required' => false, 'description' => 'Filter role: super_admin, admin, spectator, user'],
                            ['name' => 'search', 'type' => 'string', 'required' => false, 'description' => 'Cari berdasar nama/email'],
                            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah per halaman (default: 20)'],
                        ],
                        'response' => '{"data":[{"id":1,"name":"Admin","email":"admin@test.com","role":"admin","gudang":{"id":1,"nama_gudang":"..."},"gudangs":[...],"spectator_gudangs":[...]}],...}',
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/users/{id}',
                        'title' => 'Detail User',
                        'description' => 'Detail data user beserta gudang assign. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'response' => '{"id":1,"name":"Admin","email":"admin@test.com","role":"admin","gudang":{...},"gudangs":[...],...}',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/users',
                        'title' => 'Tambah User',
                        'description' => 'Buat user baru. Untuk admin/spectator, bisa assign multi-gudang. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'body' => [
                            ['name' => 'name', 'type' => 'string', 'required' => true, 'description' => 'Nama lengkap'],
                            ['name' => 'email', 'type' => 'string', 'required' => true, 'description' => 'Email (unik)'],
                            ['name' => 'password', 'type' => 'string', 'required' => true, 'description' => 'Password (min 8 karakter)'],
                            ['name' => 'role', 'type' => 'string', 'required' => true, 'description' => 'Role: super_admin, admin, spectator, user'],
                            ['name' => 'alamat', 'type' => 'string', 'required' => false, 'description' => 'Alamat'],
                            ['name' => 'no_telp', 'type' => 'string', 'required' => false, 'description' => 'Nomor telepon'],
                            ['name' => 'gudang_id', 'type' => 'integer', 'required' => false, 'description' => 'ID gudang (wajib untuk role user)'],
                            ['name' => 'gudangs', 'type' => 'array', 'required' => false, 'description' => 'Array ID gudang (wajib untuk role admin/spectator)'],
                        ],
                        'response' => '{"message":"User berhasil dibuat.","data":{...}}',
                    ],
                    [
                        'method' => 'PUT',
                        'path' => '/api/v1/users/{id}',
                        'title' => 'Update User',
                        'description' => 'Edit data user. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'body' => [
                            ['name' => 'name', 'type' => 'string', 'required' => true, 'description' => 'Nama lengkap'],
                            ['name' => 'email', 'type' => 'string', 'required' => true, 'description' => 'Email (unik)'],
                            ['name' => 'role', 'type' => 'string', 'required' => true, 'description' => 'Role'],
                            ['name' => 'password', 'type' => 'string', 'required' => false, 'description' => 'Password baru (kosongkan jika tidak ingin ubah)'],
                            ['name' => 'alamat', 'type' => 'string', 'required' => false, 'description' => 'Alamat'],
                            ['name' => 'no_telp', 'type' => 'string', 'required' => false, 'description' => 'Nomor telepon'],
                            ['name' => 'gudang_id', 'type' => 'integer', 'required' => false, 'description' => 'ID gudang (untuk role user)'],
                            ['name' => 'gudangs', 'type' => 'array', 'required' => false, 'description' => 'Array ID gudang (untuk admin/spectator)'],
                        ],
                        'response' => '{"message":"User berhasil diupdate.","data":{...}}',
                    ],
                    [
                        'method' => 'DELETE',
                        'path' => '/api/v1/users/{id}',
                        'title' => 'Hapus User',
                        'description' => 'Hapus user. Gagal jika user masih memiliki transaksi. Role: super_admin.',
                        'auth' => true,
                        'roles' => ['super_admin'],
                        'response' => '{"message":"User berhasil dihapus."}',
                    ],
                ],
            ],
        ];
    }
}
