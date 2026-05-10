@extends('layouts.app')

@section('content')
@php
    $gudang = $user->getCurrentGudang();
    $roleLabel = ucfirst(str_replace('_', ' ', $user->role));
    $infoItems = [
        ['icon' => 'fas fa-user', 'label' => 'Nama', 'value' => $user->name],
        ['icon' => 'fas fa-envelope', 'label' => 'Email', 'value' => $user->email],
        ['icon' => 'fas fa-phone', 'label' => 'No. Telepon', 'value' => $user->no_telp ?? '-'],
        ['icon' => 'fas fa-map-marker-alt', 'label' => 'Alamat', 'value' => $user->alamat ?? '-'],
    ];

    if ($gudang) {
        $infoItems[] = ['icon' => 'fas fa-warehouse', 'label' => 'Gudang Aktif', 'value' => $gudang->nama_gudang];
    }
@endphp

<div class="profile-page">
    <div class="profile-hero">
        <div class="profile-hero-title">
            <div class="profile-hero-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="profile-hero-copy">
                <h1>Profil Saya</h1>
                <p>Kelola identitas akun dan keamanan login.</p>
            </div>
        </div>
        <div class="profile-hero-meta">
            <span class="profile-meta-pill">
                <i class="fas fa-id-badge"></i>{{ $roleLabel }}
            </span>
            @if($gudang)
                <span class="profile-meta-pill profile-meta-pill-muted">
                    <i class="fas fa-warehouse"></i>{{ $gudang->nama_gudang }}
                </span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show profile-alert profile-alert-success" role="alert">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="close ml-auto" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show profile-alert profile-alert-danger" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            <button type="button" class="close ml-auto" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row profile-layout">
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card profile-card profile-avatar-card mb-4">
                <div class="profile-avatar-cover"></div>
                <div class="card-body profile-avatar-body">
                    <div class="profile-avatar-wrapper">
                        <div id="avatarCircle" class="profile-avatar">
                            @if($user->avatar)
                                <img id="avatarImg" src="{{ $user->avatarUrl() }}" alt="Avatar">
                            @else
                                <span id="avatarInitial">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                        </div>

                        <label for="avatarFileInput" class="profile-avatar-edit" title="Ganti foto profil" aria-label="Ganti foto profil">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>

                    <h2 class="profile-user-name">{{ $user->name }}</h2>
                    <p class="profile-user-email">{{ $user->email }}</p>
                    <span class="profile-role-badge">{{ $roleLabel }}</span>

                    <form id="avatarForm" action="{{ route('profil.avatar') }}" method="POST" enctype="multipart/form-data" class="profile-avatar-form">
                        @csrf
                        <input type="file" id="avatarFileInput" name="avatar" accept="image/*" class="profile-file-input">
                        <div id="avatarPreviewBar" class="profile-avatar-preview">
                            <img id="avatarPreviewImg" src="" alt="Preview">
                            <div class="profile-avatar-actions">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-upload"></i> Simpan Foto
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancelAvatarPreview()">
                                    Batal
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($user->avatar)
                        <form action="{{ route('profil.avatar.delete') }}" method="POST" class="profile-delete-avatar" id="deleteAvatarForm">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-link text-danger"
                                onclick="if(confirm('Hapus foto profil?')) document.getElementById('deleteAvatarForm').submit();">
                                <i class="fas fa-trash-alt"></i> Hapus Foto
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card profile-card profile-info-card">
                <div class="profile-card-heading">
                    <div class="profile-section-icon profile-section-icon-info">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h2>Informasi Akun</h2>
                        <p>Ringkasan data pengguna.</p>
                    </div>
                </div>
                <div class="profile-info-list">
                    @foreach($infoItems as $info)
                        <div class="profile-info-item">
                            <div class="profile-info-icon">
                                <i class="{{ $info['icon'] }}"></i>
                            </div>
                            <div>
                                <span class="profile-info-label">{{ $info['label'] }}</span>
                                <span class="profile-info-value">{{ $info['value'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card profile-card profile-form-card mb-4">
                <div class="profile-card-heading">
                    <div class="profile-section-icon profile-section-icon-primary">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h2>Edit Profil</h2>
                        <p>Perbarui nomor telepon dan alamat.</p>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('profil.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="profile-form-grid">
                            <div class="form-group">
                                <label for="name">
                                    Nama
                                    <span class="profile-readonly-badge">
                                        <i class="fas fa-lock"></i> Hanya Baca
                                    </span>
                                </label>
                                <input type="text" id="name" class="form-control profile-readonly-control" value="{{ $user->name }}" readonly>
                                <small class="form-text text-muted">Hubungi Super Admin untuk perubahan nama.</small>
                            </div>

                            <div class="form-group">
                                <label for="email">
                                    Email
                                    <span class="profile-readonly-badge">
                                        <i class="fas fa-lock"></i> Hanya Baca
                                    </span>
                                </label>
                                <input type="email" id="email" class="form-control profile-readonly-control" value="{{ $user->email }}" readonly>
                            </div>

                            <div class="form-group">
                                <label for="no_telp">No. Telepon</label>
                                <input type="tel" id="no_telp" name="no_telp" class="form-control @error('no_telp') is-invalid @enderror"
                                    value="{{ old('no_telp', $user->no_telp) }}" placeholder="Contoh: 08123456789" maxlength="20">
                                @error('no_telp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group profile-form-full">
                                <label for="alamat">Alamat</label>
                                <textarea id="alamat" name="alamat" class="form-control @error('alamat') is-invalid @enderror"
                                    rows="4" placeholder="Masukkan alamat lengkap">{{ old('alamat', $user->alamat) }}</textarea>
                                @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="profile-form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card profile-card profile-form-card">
                <div class="profile-card-heading">
                    <div class="profile-section-icon profile-section-icon-warning">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h2>Ubah Password</h2>
                        <p>Gunakan password baru minimal 8 karakter.</p>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('profil.change-password') }}" method="POST">
                        @csrf
                        @foreach([
                            ['id' => 'current_password', 'name' => 'current_password', 'label' => 'Password Saat Ini', 'placeholder' => 'Masukkan password saat ini'],
                            ['id' => 'new_password', 'name' => 'new_password', 'label' => 'Password Baru', 'placeholder' => 'Minimal 8 karakter'],
                            ['id' => 'confirm_password', 'name' => 'new_password_confirmation', 'label' => 'Konfirmasi Password Baru', 'placeholder' => 'Ulangi password baru'],
                        ] as $field)
                            <div class="form-group">
                                <label for="{{ $field['id'] }}">{{ $field['label'] }}</label>
                                <div class="input-group profile-password-field">
                                    <input type="password" id="{{ $field['id'] }}" name="{{ $field['name'] }}"
                                        class="form-control @error($field['name']) is-invalid @enderror"
                                        placeholder="{{ $field['placeholder'] }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary toggle-pass profile-password-toggle" type="button"
                                            data-target="{{ $field['id'] }}" aria-label="Tampilkan password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error($field['name'])<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        @endforeach

                        <div class="profile-form-actions">
                            <button type="submit" class="btn btn-warning text-white">
                                <i class="fas fa-key"></i> Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-pass').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = document.getElementById(this.dataset.target);
            var icon = this.querySelector('i');
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });

    var avatarInput = document.getElementById('avatarFileInput');
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            var file = this.files[0];
            if (!file) return;

            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreviewImg').src = e.target.result;
                document.getElementById('avatarPreviewBar').style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }

    function cancelAvatarPreview() {
        document.getElementById('avatarFileInput').value = '';
        document.getElementById('avatarPreviewBar').style.display = 'none';
    }
</script>
@endsection
