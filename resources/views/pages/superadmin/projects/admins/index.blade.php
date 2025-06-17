@extends('layouts.main')

@section('title', 'Admin Project - ' . $project->name)

@section('content')

@include('components.alert')

<!-- Project Header -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                @if($project->logo)
                    <img src="{{ $project->logo_url }}" alt="{{ $project->name }}" 
                            class="avatar avatar-lg">
                @else
                    <div class="avatar avatar-lg bg-primary-lt">
                        {{ substr($project->name, 0, 2) }}
                    </div>
                @endif
            </div>
            <div class="col">
                <h1 class="mb-1">{{ $project->name }}</h1>
                <div class="text-secondary">Kelola Admin Project</div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.projects.index') }}">Projects</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.projects.show', $project) }}">{{ $project->name }}</a></li>
                        <li class="breadcrumb-item active">Admin</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Daftar Admin Project</h3>
                <a href="{{ route('superadmin.projects.admins.create', $project) }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Buat Admin Baru
                </a>
            </div>

            <div class="card-body p-0">
                @if($project->admins->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Admin</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    {{-- <th>Projects Lain</th> --}}
                                    <th>Ditugaskan</th>
                                    <th>Last Login</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->admins as $admin)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $admin->profile_photo_url }}" alt="{{ $admin->name }}" 
                                                 class="avatar avatar-sm me-2">
                                            <div>
                                                <div class="fw-bold">{{ $admin->name }} @if($admin->is_pic) <span class="badge bg-info-lt">PIC</span> @endif</div>
                                                <div class="text-secondary small">{{ $admin->username }}</div>
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
                                        <span class="badge bg-{{ $admin->is_active ? 'success' : 'secondary' }}-lt">
                                            {{ $admin->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    {{-- <td>
                                        @php
                                            $otherProjects = $admin->adminProjects()->where('projects.id', '!=', $project->id)->count();
                                        @endphp
                                        @if($otherProjects > 0)
                                            <span class="badge bg-gray">{{ $otherProjects }} project</span>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td> --}}
                                    <td>
                                        <div class="small">{{ $admin->created_at->format('d/m/Y') }}</div>
                                        <div class="text-secondary small">{{ $admin->created_at->diffForHumans() }}</div>
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
                                        <div class="dropdown">
                                            <button class="btn btn-icon bg-light" 
                                                    data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href="{{ route('superadmin.projects.admins.edit', [$project, $admin]) }}" 
                                                   class="dropdown-item">
                                                    <i class="ti ti-edit me-2"></i>
                                                    Edit Admin
                                                </a>
                                                <button type="button" class="dropdown-item" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#reset-password-modal"
                                                        data-admin-id="{{ $admin->id }}"
                                                        data-admin-name="{{ $admin->name }}">
                                                    <i class="ti ti-key me-2"></i>
                                                    Reset Password
                                                </button>
                                                <form action="{{ route('superadmin.projects.admins.toggle-status', [$project, $admin]) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="ti ti-{{ $admin->is_active ? 'eye-off' : 'eye' }} me-2"></i>
                                                        {{ $admin->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                                <div class="dropdown-divider"></div>
                                                <button type="button" class="dropdown-item text-danger delete-btn"
                                                        data-name="{{ $admin->name }}"
                                                        data-url="{{ route('superadmin.projects.admins.destroy', [$project, $admin]) }}">
                                                    <i class="ti ti-trash me-2"></i>
                                                    Hapus Admin
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="ti ti-users-off icon icon-xl text-secondary"></i>
                        </div>
                        <h3 class="text-secondary">Belum ada admin</h3>
                        <p class="text-secondary">Project ini belum memiliki admin yang ditugaskan.</p>
                        <a href="{{ route('superadmin.projects.admins.create', $project) }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>
                            Buat Admin Pertama
                        </a>
                    </div>
                @endif
            </div>
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
        resetPasswordForm.action = `{{ route('superadmin.projects.admins.index', $project) }}/${adminId}/reset-password`;
        
        // Update modal title
        resetPasswordModal.querySelector('.modal-title').textContent = `Reset Password - ${adminName}`;
        
        // Clear form
        resetPasswordForm.reset();
    });
});
</script>
@endpush