@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">User Management</h1>
            <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah User Baru
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar User</h6>
            </div>
            <div class="card-body">
                {{-- Filter & Search --}}
                <div class="row mb-3">
                    <div class="col-md-4 mb-2">
                        <label class="small text-muted mb-1">Filter Role</label>
                        <select class="form-control form-control-sm" id="roleFilter">
                            <option value="">Semua Role</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <option value="spectator">Spectator</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small text-muted mb-1">Cari</label>
                        <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Cari nama atau email...">
                    </div>
                    <div class="col-md-4 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="resetFilter">
                            <i class="fas fa-sync-alt mr-1"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="userTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Gudang</th>
                                <th class="text-center" width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr data-role="{{ $user->role }}">
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @switch($user->role)
                                            @case('super_admin')
                                                <span class="badge" style="background: #7c3aed; color: white;">
                                                    <i class="fas fa-crown mr-1"></i> Super Admin
                                                </span>
                                                @break
                                            @case('admin')
                                                <span class="badge" style="background: #059669; color: white;">
                                                    <i class="fas fa-user-tie mr-1"></i> Admin
                                                </span>
                                                @break
                                            @case('spectator')
                                                <span class="badge" style="background: #0891b2; color: white;">
                                                    <i class="fas fa-eye mr-1"></i> Spectator
                                                </span>
                                                @break
                                            @case('user')
                                                <span class="badge" style="background: #2563eb; color: white;">
                                                    <i class="fas fa-user mr-1"></i> User
                                                </span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ $user->role }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($user->role == 'super_admin')
                                            <span class="text-muted small"><i class="fas fa-infinity mr-1"></i> Semua Gudang</span>
                                        @elseif($user->role == 'admin')
                                            @if($user->gudangs->count() > 0)
                                                <div class="d-flex flex-wrap">
                                                    @foreach($user->gudangs as $gudang)
                                                        <span class="badge mr-1 mb-1" style="background: #d1fae5; color: #065f46;">
                                                            <i class="fas fa-warehouse mr-1"></i>{{ $gudang->nama_gudang }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted small">Belum ada gudang</span>
                                            @endif
                                        @elseif($user->role == 'spectator')
                                            @if($user->spectatorGudangs->count() > 0)
                                                <div class="d-flex flex-wrap">
                                                    @foreach($user->spectatorGudangs as $gudang)
                                                        <span class="badge mr-1 mb-1" style="background: #cffafe; color: #155e75;">
                                                            <i class="fas fa-warehouse mr-1"></i>{{ $gudang->nama_gudang }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted small">Belum ada gudang</span>
                                            @endif
                                        @else
                                            @if($user->gudang)
                                                <span class="badge" style="background: #dbeafe; color: #1e40af;">
                                                    <i class="fas fa-warehouse mr-1"></i>{{ $user->gudang->nama_gudang }}
                                                </span>
                                            @else
                                                <span class="text-muted small">Belum ada gudang</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown action-dropdown">
                                            <button class="btn btn-sm dropdown-toggle no-caret" type="button"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                <a class="dropdown-item" href="{{ route('users.edit', $user->id) }}">
                                                    <i class="fas fa-pen fa-fw mr-2 text-warning"></i> Edit
                                                </a>
                                                @if(auth()->id() != $user->id)
                                                    <div class="dropdown-divider"></div>
                                                    <button type="button" class="dropdown-item text-danger" data-toggle="modal"
                                                        data-target="#deleteModal"
                                                        data-action="{{ route('users.destroy', $user->id) }}"
                                                        data-name="{{ $user->name }}">
                                                        <i class="fas fa-trash fa-fw mr-2"></i> Hapus
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Belum ada data user.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Summary --}}
                <div class="row mt-3">
                    <div class="col-12">
                        <small class="text-muted">
                            Total: <strong id="totalCount">{{ $users->count() }}</strong> user
                            <span id="filteredInfo"></span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus</h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin <strong>menghapus</strong> user <strong id="deleteUserName"></strong>?</p>
                    <p class="text-muted mb-0"><small>Data yang dihapus tidak dapat dikembalikan.</small></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Delete modal
            $('#deleteModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var action = button.data('action');
                var name = button.data('name');
                var modal = $(this);
                modal.find('#deleteForm').attr('action', action);
                modal.find('#deleteUserName').text(name);
            });

            // Filter and Search
            function filterTable() {
                var roleFilter = $('#roleFilter').val().toLowerCase();
                var searchText = $('#searchInput').val().toLowerCase();
                var visibleCount = 0;
                var totalCount = $('#userTable tbody tr').length;

                $('#userTable tbody tr').each(function() {
                    var row = $(this);
                    var role = row.data('role') ? row.data('role').toLowerCase() : '';
                    var name = row.find('td:eq(0)').text().toLowerCase();
                    var email = row.find('td:eq(1)').text().toLowerCase();

                    var roleMatch = !roleFilter || role === roleFilter;
                    var searchMatch = !searchText || name.indexOf(searchText) > -1 || email.indexOf(searchText) > -1;

                    if (roleMatch && searchMatch) {
                        row.show();
                        visibleCount++;
                    } else {
                        row.hide();
                    }
                });

                // Update count
                if (roleFilter || searchText) {
                    $('#filteredInfo').text(' (menampilkan ' + visibleCount + ' dari ' + totalCount + ')');
                } else {
                    $('#filteredInfo').text('');
                }
            }

            $('#roleFilter').on('change', filterTable);
            $('#searchInput').on('keyup', filterTable);
            
            $('#resetFilter').on('click', function() {
                $('#roleFilter').val('');
                $('#searchInput').val('');
                filterTable();
            });
        });
    </script>
@endpush