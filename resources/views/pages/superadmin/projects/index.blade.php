@extends('layouts.main')

@section('title', 'Kelola Project')

@section('content')

@include('components.alert')

<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Daftar Project</h3>
            <a href="{{ route('superadmin.projects.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>
                Tambah Project
            </a>
        </div>

        <!-- Filters -->
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Cari project..." 
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
                        <option value="updated_at" {{ request('sort') == 'updated_at' ? 'selected' : '' }}>Terakhir Diperbarui</option>
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
                        <a href="{{ route('superadmin.projects.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i>
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            @if($projects->count() > 0)
            <div class="table-responsive">
                <table class="table table-vcenter">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Unit</th>
                            <th>Admin</th>
                            <th>Affiliator</th>
                            <th>Lead</th>
                            <th>Dibuat</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($project->logo)
                                        <img src="{{ $project->logo_url }}" alt="{{ $project->name }}" 
                                             class="avatar avatar-sm me-2">
                                    @else
                                        <div class="avatar avatar-sm bg-primary-lt me-2">
                                            {{ substr($project->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold">{{ $project->name }} {!! $project->crm_project_id ? '<i class="ti ti-link text-info" data-bs-toggle="tooltip" title="Project ini terhubung dengan CRM Project ID: ' . $project->crm_project_id . '"></i>' : '' !!}</div>
                                        <div class="text-secondary small">{{ strtoupper($project->location) }}</div>
                                        @if($project->description)
                                            <div class="text-secondary small text-truncate" style="max-width: 200px;">
                                                {{ strip_tags($project->description) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $project->is_active ? 'success' : 'secondary' }}-lt">
                                    {{ $project->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-lt">
                                    {{ $project->units_count }}
                                </span>
                            </td>
                            <td>
                                <div class="avatar-list avatar-list-stacked">
                                    @forelse($project->admins->take(3) as $admin)
                                        <span class="avatar avatar-xs" title="{{ $admin->name }}">
                                            {{ $admin->initials }}
                                        </span>
                                    @empty
                                        <span class="text-secondary small">Belum ada admin</span>
                                    @endforelse
                                    @if($project->admins->count() > 3)
                                        <span class="avatar avatar-xs">+{{ $project->admins->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-lt">{{ $project->affiliatorProjects()->count() }}</span>
                            </td>
                            <td>
                                <div>
                                    <span class="badge bg-success-lt">{{ $project->leads()->verified()->count() }}</span>
                                    /
                                    <span class="badge bg-secondary-lt">{{ $project->leads()->count() }}</span>
                                </div>
                                <div class="text-secondary small">Verified/Total</div>
                            </td>
                            <td>
                                <div class="small">{{ $project->created_at->format('d/m/Y') }}</div>
                                <div class="text-secondary small">{{ $project->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-icon bg-light" 
                                            data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="{{ route('superadmin.projects.show', $project) }}" 
                                           class="dropdown-item">
                                            <i class="ti ti-eye me-2"></i>
                                            Lihat Detail
                                        </a>
                                        <a href="{{ route('superadmin.projects.edit', $project) }}" 
                                           class="dropdown-item">
                                            <i class="ti ti-edit me-2"></i>
                                            Edit
                                        </a>
                                        <a href="{{ route('superadmin.projects.admins.index', $project) }}"
                                           class="dropdown-item">
                                            <i class="ti ti-user-check me-2"></i>
                                            Kelola Admin
                                        </a>
                                        <a href="{{ route('superadmin.projects.units.index', $project) }}"
                                           class="dropdown-item">
                                            <i class="ti ti-home-check me-2"></i>
                                            Kelola Unit
                                        </a>
                                        <form action="{{ route('superadmin.projects.toggle-status', $project) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item">
                                                <i class="ti ti-{{ $project->is_active ? 'eye-off' : 'eye' }} me-2"></i>
                                                {{ $project->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item text-danger delete-btn"
                                                data-name="{{ $project->name }}"
                                                data-url="{{ route('superadmin.projects.destroy', $project) }}">
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
                    Menampilkan {{ $projects->firstItem() ?? 0 }} hingga {{ $projects->lastItem() ?? 0 }} 
                    dari {{ $projects->total() }} project
                </p>
                @include('components.pagination', ['paginator' => $projects])
            </div>
            @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="ti ti-folder-off icon icon-xl text-secondary"></i>
                </div>
                <h3 class="text-secondary">Belum ada project</h3>
                <p class="text-secondary">Mulai dengan membuat project pertama Anda.</p>
                <a href="{{ route('superadmin.projects.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Project
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@include('components.delete-modal')
@endsection