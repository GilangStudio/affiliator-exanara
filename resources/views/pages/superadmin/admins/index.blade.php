@extends('layouts.main')

@section('title', 'Kelola Admin')

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Daftar Admin</h3>
            <a href="{{ route('superadmin.admins.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>
                Tambah Admin
            </a>
        </div>

        <!-- Filters -->
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Cari admin..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="sort">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nama</option>
                        <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>Email</option>
                        <option value="last_login_at" {{ request('sort') == 'last_login_at' ? 'selected' : '' }}>Terakhir Login</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="order">
                        <option value="desc" {{ request('order') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                        <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="ti ti-search me-1"></i>
                            Filter
                        </button>
                        <a href="{{ route('superadmin.admins.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i>
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            @if($admins->count() > 0)
            <div class="table-responsive">
                <table class="table table-vcenter">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Projects</th>
                            <th>Last Login</th>
                            <th>Dibuat</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $admin->profile_photo_url }}" alt="{{ $admin->name }}" 
                                         class="avatar avatar-sm me-2">
                                    <div>
                                        <div class="fw-bold">{{ $admin->name }}</div>
                                        <div class="text-secondary small">{{ $admin->initials }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-bold">{{ $admin->email }}</div>
                                    <div class="text-secondary small">
                                        {{ $admin->country_code }} {{ $admin->phone }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $admin->is_active ? 'success text-white' : 'secondary text-white' }}">
                                    {{ $admin->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                @if($admin->projects_count > 0)
                                    <span class="text-success">{{ $admin->projects_count }}</span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                @if($admin->last_login_at)
                                    <div class="small">{{ $admin->last_login_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-secondary small">{{ $admin->last_login_at->diffForHumans() }}</div>
                                @else
                                    <span class="text-secondary">Belum pernah login</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">{{ $admin->created_at->format('d/m/Y') }}</div>
                                <div class="text-secondary small">{{ $admin->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-ghost-secondary btn-sm dropdown-toggle" 
                                            data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="{{ route('superadmin.admins.edit', $admin) }}" 
                                           class="dropdown-item">
                                            <i class="ti ti-edit me-2"></i>
                                            Edit
                                        </a>
                                        <button type="button" class="dropdown-item" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#reset-password-modal"
                                                data-admin-id="{{ $admin->id }}"
                                                data-admin-name="{{ $admin->name }}">
                                            <i class="ti ti-key me-2"></i>
                                            Reset Password
                                        </button>
                                        <form action="{{ route('superadmin.admins.toggle-status', $admin) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item">
                                                <i class="ti ti-{{ $admin->is_active ? 'eye-off' : 'eye' }} me-2"></i>
                                                {{ $admin->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        @if($admin->profile_photo)
                                        <form action="{{ route('superadmin.admins.remove-photo', $admin) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item"
                                                    onclick="return confirm('Hapus foto profil?')">
                                                <i class="ti ti-photo-off me-2"></i>
                                                Hapus Foto
                                            </button>
                                        </form>
                                        @endif
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item text-danger delete-btn"
                                                data-name="{{ $admin->name }}"
                                                data-url="{{ route('superadmin.admins.destroy', $admin) }}">
                                            <i class="ti ti-trash me-2"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer d-flex align-items-center">
                <p class="m-0 text-secondary">
                    Menampilkan {{ $admins->firstItem() ?? 0 }} hingga {{ $admins->lastItem() ?? 0 }} 
                    dari {{ $admins->total() }} admin
                </p>
                @include('components.pagination', ['paginator' => $admins])
            </div>
            @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="ti ti-users-off icon icon-xl text-secondary"></i>
                </div>
                <h3 class="text-secondary">Belum ada admin</h3>
                <p class="text-secondary">Mulai dengan membuat admin pertama Anda.</p>
                <a href="{{ route('superadmin.admins.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Admin
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="reset-password-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reset-password-form" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="new_password" required minlength="8">
                        <div class="form-hint">Minimal 8 karakter</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="new_password_confirmation" required>
                    </div>
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        Admin akan menerima notifikasi bahwa password mereka telah direset.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('components.delete-modal')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reset password modal handler
    const resetPasswordModal = document.getElementById('reset-password-modal');
    const resetPasswordForm = document.getElementById('reset-password-form');
    
    resetPasswordModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const adminId = button.getAttribute('data-admin-id');
        const adminName = button.getAttribute('data-admin-name');
        
        // Update form action
        resetPasswordForm.action = `{{ route('superadmin.admins.index') }}/${adminId}/reset-password`;
        
        // Update modal title
        resetPasswordModal.querySelector('.modal-title').textContent = `Reset Password - ${adminName}`;
        
        // Clear form
        resetPasswordForm.reset();
    });
});
</script>
@endpush