@extends('layouts.main')

@section('title', 'Kelola Affiliator')

@section('content')

@include('components.alert')

<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Daftar Affiliator</h3>
            <a href="{{ route('superadmin.affiliators.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>
                Tambah Affiliator
            </a>
        </div>

        <!-- Filters -->
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Cari affiliator..." 
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
                        <a href="{{ route('superadmin.affiliators.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i>
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            @if($affiliators->count() > 0)
            <div class="table-responsive">
                <table class="table table-vcenter">
                    <thead>
                        <tr>
                            <th>Affiliator</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Projects</th>
                            <th>Leads</th>
                            <th>Last Login</th>
                            <th>Dibuat</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($affiliators as $affiliator)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $affiliator->profile_photo_url }}" alt="{{ $affiliator->name }}" 
                                         class="avatar avatar-sm me-2">
                                    <div>
                                        <div class="fw-bold">{{ $affiliator->name }}</div>
                                        <div class="text-secondary small">{{ $affiliator->initials }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-bold">{{ $affiliator->email }}</div>
                                    <div class="text-secondary small">
                                        {{ $affiliator->country_code }} {{ $affiliator->phone }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $affiliator->is_active ? 'success' : 'secondary' }}-lt">
                                    {{ $affiliator->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                @if($affiliator->affiliatorProjects->count() > 0)
                                    <div class="avatar-list avatar-list-stacked">
                                        @foreach($affiliator->affiliatorProjects->take(3) as $affiliatorProject)
                                            <span class="avatar avatar-xs" title="{{ $affiliatorProject->project->name }}">
                                                {{ substr($affiliatorProject->project->name, 0, 1) }}
                                            </span>
                                        @endforeach
                                        @if($affiliator->affiliatorProjects->count() > 3)
                                            <span class="avatar avatar-xs">+{{ $affiliator->affiliatorProjects->count() - 3 }}</span>
                                        @endif
                                    </div>
                                    <div class="text-secondary small">{{ $affiliator->affiliatorProjects->count() }} project</div>
                                @else
                                    <span class="text-secondary">Belum ada project</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $totalLeads = 0;
                                    $verifiedLeads = 0;
                                    foreach($affiliator->affiliatorProjects as $ap) {
                                        $totalLeads += $ap->leads()->count();
                                        $verifiedLeads += $ap->leads()->verified()->count();
                                    }
                                @endphp
                                @if($totalLeads > 0)
                                    <div>
                                        <span class="badge bg-success-lt">{{ $verifiedLeads }}</span>
                                        /
                                        <span class="badge bg-secondary-lt">{{ $totalLeads }}</span>
                                    </div>
                                    <div class="text-secondary small">Verified/Total</div>
                                @else
                                    <span class="text-secondary">Belum ada lead</span>
                                @endif
                            </td>
                            <td>
                                @if($affiliator->last_login_at)
                                    <div class="small">{{ $affiliator->last_login_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-secondary small">{{ $affiliator->last_login_at->diffForHumans() }}</div>
                                @else
                                    <span class="text-secondary">Belum pernah login</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">{{ $affiliator->created_at->format('d/m/Y') }}</div>
                                <div class="text-secondary small">{{ $affiliator->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-icon bg-light" 
                                            data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="{{ route('superadmin.affiliators.edit', $affiliator) }}" 
                                           class="dropdown-item">
                                            <i class="ti ti-edit me-2"></i>
                                            Edit
                                        </a>
                                        <button type="button" class="dropdown-item" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#reset-password-modal"
                                                data-affiliator-id="{{ $affiliator->id }}"
                                                data-affiliator-name="{{ $affiliator->name }}">
                                            <i class="ti ti-key me-2"></i>
                                            Reset Password
                                        </button>
                                        <form action="{{ route('superadmin.affiliators.toggle-status', $affiliator) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item">
                                                <i class="ti ti-{{ $affiliator->is_active ? 'eye-off' : 'eye' }} me-2"></i>
                                                {{ $affiliator->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        @if($affiliator->profile_photo)
                                        <form action="{{ route('superadmin.affiliators.remove-photo', $affiliator) }}" 
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
                                                data-name="{{ $affiliator->name }}"
                                                data-url="{{ route('superadmin.affiliators.destroy', $affiliator) }}">
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
                    Menampilkan {{ $affiliators->firstItem() ?? 0 }} hingga {{ $affiliators->lastItem() ?? 0 }} 
                    dari {{ $affiliators->total() }} affiliator
                </p>
                @include('components.pagination', ['paginator' => $affiliators->appends(request()->all())])
            </div>
            @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="ti ti-users-off icon icon-xl text-secondary"></i>
                </div>
                <h3 class="text-secondary">Belum ada affiliator</h3>
                <p class="text-secondary">Mulai dengan membuat affiliator pertama Anda.</p>
                <a href="{{ route('superadmin.affiliators.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Affiliator
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
                        Affiliator akan menerima notifikasi bahwa password mereka telah direset.
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
        const affiliatorId = button.getAttribute('data-affiliator-id');
        const affiliatorName = button.getAttribute('data-affiliator-name');
        
        // Update form action
        resetPasswordForm.action = `{{ route('superadmin.affiliators.index') }}/${affiliatorId}/reset-password`;
        
        // Update modal title
        resetPasswordModal.querySelector('.modal-title').textContent = `Reset Password - ${affiliatorName}`;
        
        // Clear form
        resetPasswordForm.reset();
    });
});
</script>
@endpush