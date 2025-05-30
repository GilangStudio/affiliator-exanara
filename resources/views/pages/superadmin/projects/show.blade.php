@extends('layouts.main')

@section('title', 'Detail Project - ' . $project->name)

@section('content')
<!-- Project Header -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                @if($project->logo)
                    <img src="{{ $project->logo_url }}" alt="{{ $project->name }}" 
                            class="avatar avatar-xl">
                @else
                    <div class="avatar avatar-xl bg-primary-lt">
                        {{ substr($project->name, 0, 2) }}
                    </div>
                @endif
            </div>
            <div class="col">
                <h1 class="mb-1">{{ $project->name }}</h1>
                <div class="text-secondary mb-2">{{ $project->slug }}</div>
                @if($project->description)
                    <p class="text-secondary mb-2">{{ $project->description }}</p>
                @endif
                <div class="row">
                    <div class="col-auto">
                        <span class="badge bg-{{ $project->is_active ? 'success' : 'secondary' }} me-2">
                            {{ $project->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                        <span class="badge bg-blue me-2">{{ $project->commission_display }}</span>
                        @if($project->require_digital_signature)
                            <span class="badge bg-purple">Tanda Tangan Digital Wajib</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="btn-list">
                    <a href="{{ route('superadmin.projects.edit', $project) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i>
                        Edit Project
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu">
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
                                Hapus Project
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Affiliator</div>
                </div>
                <div class="h1 mb-0">{{ number_format($stats['total_affiliators']) }}</div>
                <div class="d-flex mb-2">
                    <div class="text-secondary">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            {{ number_format($stats['active_affiliators']) }} aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Lead</div>
                </div>
                <div class="h1 mb-0">{{ number_format($stats['total_leads']) }}</div>
                <div class="d-flex mb-2">
                    <div class="text-secondary">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            {{ number_format($stats['verified_leads']) }} terverifikasi
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Komisi Diperoleh</div>
                </div>
                <div class="h1 mb-0">Rp {{ number_format($stats['total_commission_earned'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Komisi Ditarik</div>
                </div>
                <div class="h1 mb-0">Rp {{ number_format($stats['total_commission_withdrawn'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Project Admins -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Admin Project</h3>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#add-admin-modal">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Admin
                </button>
            </div>
            <div class="card-body p-0">
                @if($project->admins->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($project->admins as $admin)
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="avatar">{{ $admin->initials }}</span>
                                </div>
                                <div class="col text-truncate">
                                    <div class="text-reset d-block">{{ $admin->name }}</div>
                                    <div class="d-block text-secondary text-truncate mt-n1">
                                        {{ $admin->email }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <form action="{{ route('superadmin.projects.remove-admin', [$project, $admin]) }}" 
                                            method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-ghost-danger"
                                                onclick="return confirm('Hapus admin ini dari project?')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="text-secondary">
                            <i class="ti ti-users-off icon icon-xl mb-2"></i>
                            <div>Belum ada admin</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Leads -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lead Terbaru</h3>
            </div>
            <div class="card-body p-0">
                @if($recentLeads->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Affiliator</th>
                                    <th>Status</th>
                                    <th>Komisi</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLeads as $lead)
                                <tr>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $lead->customer_name }}</div>
                                            <div class="text-secondary small">{{ $lead->customer_phone }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm me-2">
                                                {{ $lead->affiliatorProject->user->initials }}
                                            </span>
                                            <div>
                                                <div class="fw-bold">{{ $lead->affiliatorProject->user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $lead->verification_status == 'verified' ? 'success' : ($lead->verification_status == 'rejected' ? 'danger' : 'warning') }}">
                                            {{ $lead->verification_status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($lead->commission_earned > 0)
                                            <div class="fw-bold text-success">{{ $lead->commission_formatted }}</div>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">{{ $lead->created_at->format('d/m/Y') }}</div>
                                        <div class="text-secondary small">{{ $lead->created_at->diffForHumans() }}</div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="text-secondary">
                            <i class="ti ti-file-off icon icon-xl mb-2"></i>
                            <div>Belum ada lead</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Project Details -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                    <li class="nav-item">
                        <a href="#terms" class="nav-link active" data-bs-toggle="tab">Syarat & Ketentuan</a>
                    </li>
                    <li class="nav-item">
                        <a href="#additional-info" class="nav-link" data-bs-toggle="tab">Informasi Tambahan</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="terms">
                        <div class="markdown">
                            {!! nl2br(e($project->terms_and_conditions)) !!}
                        </div>
                    </div>
                    <div class="tab-pane" id="additional-info">
                        @if($project->additional_info)
                            <div class="markdown">
                                {!! nl2br(e($project->additional_info)) !!}
                            </div>
                        @else
                            <div class="text-center text-secondary">
                                <i class="ti ti-info-circle icon icon-xl mb-2"></i>
                                <div>Tidak ada informasi tambahan</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="add-admin-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.projects.add-admin', $project) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Pilih Admin</label>
                        <select class="form-select" name="admin_id" required>
                            <option value="">Pilih admin...</option>
                            @foreach(App\Models\User::where('role', 'admin')->active()->whereNotIn('id', $project->admins->pluck('id'))->get() as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }})</option>
                            @endforeach
                        </select>
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

@include('components.delete-modal')
@endsection