@extends('layouts.main')

@section('title', 'Kelola Project')

@section('content')

@include('components.alert')

<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Project yang Saya Kelola</h3>
            <div class="text-secondary">
                <small>Anda mengelola {{ $projects->total() }} project</small>
            </div>
        </div>

        <!-- Filters -->
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Cari project..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="sort">
                        <option value="projects.created_at" {{ request('sort') == 'projects.created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                        <option value="projects.name" {{ request('sort') == 'projects.name' ? 'selected' : '' }}>Nama</option>
                        <option value="projects.updated_at" {{ request('sort') == 'projects.updated_at' ? 'selected' : '' }}>Terakhir Diperbarui</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="ti ti-search me-1"></i>
                            Filter
                        </button>
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary">
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
                            <th>Affiliator</th>
                            <th>Lead</th>
                            <th>Penarikan</th>
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
                                        <div class="fw-bold">{{ $project->name }} 
                                            {!! $project->crm_project_id ? '<i class="ti ti-link text-info" data-bs-toggle="tooltip" title="Project ini terhubung dengan CRM Project ID: ' . $project->crm_project_id . '"></i>' : '' !!}
                                        </div>
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
                                <a href="{{ route('admin.projects.show', $project) }}" class="badge bg-secondary-lt text-decoration-none">
                                    {{ $project->units_count }}
                                </a>
                            </td>
                            <td>
                                @php
                                    $totalAffiliators = $project->affiliatorProjects()->count();
                                    $pendingAffiliators = $project->affiliatorProjects()->pending()->count();
                                @endphp
                                <div>
                                    <span class="badge bg-blue-lt">{{ $totalAffiliators }}</span>
                                    @if($pendingAffiliators > 0)
                                        <span class="badge bg-warning-lt ms-1">{{ $pendingAffiliators }} pending</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $totalLeads = $project->leads()->count();
                                    $verifiedLeads = $project->leads()->verified()->count();
                                    $pendingLeads = $project->leads()->pending()->count();
                                @endphp
                                <div>
                                    <span class="badge bg-success-lt">{{ $verifiedLeads }}</span>
                                    /
                                    <span class="badge bg-secondary-lt">{{ $totalLeads }}</span>
                                    @if($pendingLeads > 0)
                                        <div><span class="badge bg-warning-lt small">{{ $pendingLeads }} pending</span></div>
                                    @endif
                                </div>
                                <div class="text-secondary small">Verified/Total</div>
                            </td>
                            <td>
                                @php
                                    $pendingWithdrawals = $project->commissionWithdrawals()->where('status', 'pending')->count();
                                @endphp
                                @if($pendingWithdrawals > 0)
                                    <span class="badge bg-warning-lt">{{ $pendingWithdrawals }} pending</span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
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
                                        <a href="{{ route('admin.projects.show', $project) }}" 
                                           class="dropdown-item">
                                            <i class="ti ti-eye me-2"></i>
                                            Lihat Detail
                                        </a>
                                        <a href="{{ route('admin.projects.edit', $project) }}" 
                                           class="dropdown-item">
                                            <i class="ti ti-edit me-2"></i>
                                            Edit Project
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a href="{{ route('admin.projects.affiliators.index', $project) }}"
                                           class="dropdown-item">
                                            <i class="ti ti-users-group me-2"></i>
                                            Kelola Affiliator
                                            @php $pendingAff = $project->affiliatorProjects()->pending()->count(); @endphp
                                            @if($pendingAff > 0)
                                                <span class="badge badge-sm bg-red text-white ms-1">{{ $pendingAff }}</span>
                                            @endif
                                        </a>
                                        <a href="{{ route('admin.projects.leads.index', $project) }}"
                                           class="dropdown-item">
                                            <i class="ti ti-users me-2"></i>
                                            Kelola Lead
                                            @php $pendingLeads = $project->leads()->pending()->count(); @endphp
                                            @if($pendingLeads > 0)
                                                <span class="badge badge-sm bg-orange text-white ms-1">{{ $pendingLeads }}</span>
                                            @endif
                                        </a>
                                        <a href="{{ route('admin.projects.withdrawals.index', $project) }}"
                                           class="dropdown-item">
                                            <i class="ti ti-credit-card me-2"></i>
                                            Kelola Penarikan
                                            @php $pendingWith = $project->commissionWithdrawals()->where('status', 'pending')->count(); @endphp
                                            @if($pendingWith > 0)
                                                <span class="badge badge-sm bg-yellow text-white ms-1">{{ $pendingWith }}</span>
                                            @endif
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('admin.projects.toggle-status', $project) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item">
                                                <i class="ti ti-{{ $project->is_active ? 'eye-off' : 'eye' }} me-2"></i>
                                                {{ $project->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
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
                <h3 class="text-secondary">Belum ada project yang dikelola</h3>
                <p class="text-secondary">Anda belum ditugaskan untuk mengelola project apapun. Hubungi Super Admin untuk mendapatkan akses.</p>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection