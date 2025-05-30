@extends('layouts.main')

@section('title', 'Admin Project - ' . $project->name)

@section('content')
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
                @if($availableAdmins->count() > 0)
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-admin-modal">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Admin
                </button>
                @endif
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
                                    <th>Projects Lain</th>
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
                                        @php
                                            $otherProjects = $admin->adminProjects()->where('projects.id', '!=', $project->id)->count();
                                        @endphp
                                        @if($otherProjects > 0)
                                            <span class="badge bg-gray">{{ $otherProjects }} project</span>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
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
                                            <button class="btn btn-ghost-secondary btn-sm py-2" 
                                                    data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href="{{ route('superadmin.admins.edit', $admin) }}" 
                                                   class="dropdown-item">
                                                    <i class="ti ti-edit me-2"></i>
                                                    Edit Admin
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('superadmin.projects.admins.destroy', [$project, $admin]) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"
                                                            onclick="return confirm('Hapus admin ini dari project?')">
                                                        <i class="ti ti-trash me-2"></i>
                                                        Hapus dari Project
                                                    </button>
                                                </form>
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
                        @if($availableAdmins->count() > 0)
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-admin-modal">
                            <i class="ti ti-plus me-1"></i>
                            Tambah Admin Pertama
                        </button>
                        @else
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle me-2"></i>
                            Tidak ada admin yang tersedia untuk ditugaskan.
                        </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
@if($availableAdmins->count() > 0)
<div class="modal fade" id="add-admin-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.projects.admins.store', $project) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Admin ke Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Pilih Admin</label>
                        <select class="form-select" name="admin_id" required>
                            <option value="">Pilih admin...</option>
                            @foreach($availableAdmins as $admin)
                                <option value="{{ $admin->id }}">
                                    {{ $admin->name }} ({{ $admin->email }})
                                    @if($admin->adminProjects()->count() > 0)
                                        - {{ $admin->adminProjects()->count() }} project lain
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="form-hint">Hanya admin yang belum ditugaskan di project ini yang ditampilkan.</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Admin yang ditambahkan akan menerima notifikasi dan dapat langsung mengelola project ini.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection