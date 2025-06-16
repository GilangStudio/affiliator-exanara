@extends('layouts.main')

@section('title', 'Detail Project - ' . $project->name)

@section('content')

@include('components.alert')

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
                <div class="row">
                    <div class="col-auto">
                        <span class="badge bg-{{ $project->is_active ? 'success' : 'secondary' }}-lt me-2">
                            {{ $project->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                        @if($project->require_digital_signature)
                            <span class="badge bg-blue-lt">Tanda Tangan Digital Wajib</span>
                        @endif
                        @if($project->crm_project_id)
                            <span class="badge bg-info-lt ms-2">
                                <i class="ti ti-link me-1"></i>
                                Terhubung CRM
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="btn-list">
                    <a href="{{ route('admin.projects.affiliators.index', $project) }}" class="btn btn-outline-primary">
                        <i class="ti ti-users-group me-1"></i>
                        Kelola Affiliator
                        @php $pendingAff = $project->affiliatorProjects()->pending()->count(); @endphp
                        @if($pendingAff > 0)
                            <span class="badge badge-sm bg-red text-white ms-1">{{ $pendingAff }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.projects.leads.index', $project) }}" class="btn btn-outline-primary">
                        <i class="ti ti-users me-1"></i>
                        Kelola Lead
                        @php $pendingLeads = $project->leads()->pending()->count(); @endphp
                        @if($pendingLeads > 0)
                            <span class="badge badge-sm bg-orange text-white ms-1">{{ $pendingLeads }}</span>
                        @endif
                    </a>
                    @if ($isPic)
                    <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i>
                        Edit Project
                    </a>
                    @endif
                    <div class="dropdown">
                        <button class="btn btn-icon" data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="{{ route('admin.projects.withdrawals.index', $project) }}" class="dropdown-item">
                                <i class="ti ti-credit-card me-2"></i>
                                Kelola Penarikan
                                @php $pendingWith = $project->commissionWithdrawals()->where('status', 'pending')->count(); @endphp
                                @if($pendingWith > 0)
                                    <span class="badge badge-sm bg-yellow text-white ms-1">{{ $pendingWith }}</span>
                                @endif
                            </a>
                            @if ($isPic)
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
                            @endif
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
                    <div class="subheader">Total Unit</div>
                </div>
                <div class="h1 mb-0">{{ number_format($stats['total_units']) }}</div>
                <div class="d-flex mb-2">
                    <div class="text-secondary">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            {{ number_format($stats['active_units']) }} aktif
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
                    <div class="subheader">Penarikan Pending</div>
                </div>
                @php $pendingWithdrawals = $project->commissionWithdrawals()->where('status', 'pending')->count(); @endphp
                <div class="h1 mb-0 {{ $pendingWithdrawals > 0 ? 'text-warning' : '' }}">
                    {{ number_format($pendingWithdrawals) }}
                </div>
                @if($pendingWithdrawals > 0)
                    <div class="d-flex mb-2">
                        <div class="text-warning">
                            <span class="d-inline-flex align-items-center lh-1">
                                <i class="ti ti-alert-triangle me-1"></i>
                                Perlu ditinjau
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Aksi Cepat</h3>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush list-group-hoverable">
                    <a href="{{ route('admin.projects.affiliators.index', $project) }}" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ti ti-users-group text-primary"></i>
                            </div>
                            <div class="col text-truncate">
                                <div class="text-reset d-block">Kelola Affiliator</div>
                                <div class="d-block text-secondary text-truncate mt-n1">
                                    Verifikasi dan kelola affiliator project
                                </div>
                            </div>
                            <div class="col-auto">
                                @php $pendingAff = $project->affiliatorProjects()->pending()->count(); @endphp
                                @if($pendingAff > 0)
                                    <span class="badge bg-red">{{ $pendingAff }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.projects.leads.index', $project) }}" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ti ti-users text-success"></i>
                            </div>
                            <div class="col text-truncate">
                                <div class="text-reset d-block">Kelola Lead</div>
                                <div class="d-block text-secondary text-truncate mt-n1">
                                    Verifikasi dan kelola lead customer
                                </div>
                            </div>
                            <div class="col-auto">
                                @php $pendingLeads = $project->leads()->pending()->count(); @endphp
                                @if($pendingLeads > 0)
                                    <span class="badge bg-orange">{{ $pendingLeads }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.projects.withdrawals.index', $project) }}" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ti ti-credit-card text-warning"></i>
                            </div>
                            <div class="col text-truncate">
                                <div class="text-reset d-block">Kelola Penarikan</div>
                                <div class="d-block text-secondary text-truncate mt-n1">
                                    Proses permintaan penarikan komisi
                                </div>
                            </div>
                            <div class="col-auto">
                                @php $pendingWith = $project->commissionWithdrawals()->where('status', 'pending')->count(); @endphp
                                @if($pendingWith > 0)
                                    <span class="badge bg-yellow">{{ $pendingWith }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Project Admins -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Admin Project</h3>
            </div>
            <div class="card-body p-0">
                @if($project->admins->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($project->admins->take(5) as $admin)
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="avatar avatar-sm">{{ $admin->initials }}</span>
                                </div>
                                <div class="col text-truncate">
                                    <div class="text-reset d-block">{{ $admin->name }}</div>
                                    <div class="d-block text-secondary text-truncate mt-n1">
                                        {{ $admin->email }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    @if($admin->id == Auth::id())
                                        <span class="badge bg-blue-lt">Anda</span>
                                    @else
                                        <span class="badge bg-{{ $admin->is_active ? 'success' : 'secondary' }}-lt">
                                            {{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($project->admins->count() > 5)
                        <div class="card-footer text-center">
                            <span class="text-secondary">
                                {{ $project->admins->count() - 5 }} admin lainnya
                            </span>
                        </div>
                    @endif
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Lead Terbaru</h3>
                <a href="{{ route('admin.projects.leads.index', $project) }}" class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
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
                                    <th width="80">Aksi</th>
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
                                        <span class="badge bg-{{ $lead->verification_status == 'verified' ? 'success' : ($lead->verification_status == 'rejected' ? 'danger' : 'warning') }}-lt">
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
                                    <td>
                                        <a href="{{ route('admin.projects.leads.show', [$project, $lead]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye"></i>
                                        </a>
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
                            <div class="small mt-1">Lead akan muncul setelah affiliator menambahkan customer</div>
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
                        <a href="#overview" class="nav-link active" data-bs-toggle="tab">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a href="#terms" class="nav-link" data-bs-toggle="tab">Syarat & Ketentuan</a>
                    </li>
                    <li class="nav-item">
                        <a href="#additional-info" class="nav-link" data-bs-toggle="tab">Informasi Tambahan</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="overview">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Informasi Project</h4>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150">Nama Project:</td>
                                        <td><strong>{{ $project->name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Lokasi:</td>
                                        <td>{{ $project->location ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Status:</td>
                                        <td>
                                            <span class="badge bg-{{ $project->is_active ? 'success' : 'secondary' }}-lt">
                                                {{ $project->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Tanda Tangan Digital:</td>
                                        <td>
                                            <span class="badge bg-{{ $project->require_digital_signature ? 'blue' : 'secondary' }}-lt">
                                                {{ $project->require_digital_signature ? 'Wajib' : 'Opsional' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @if($project->crm_project_id)
                                    <tr>
                                        <td>CRM Project ID:</td>
                                        <td>
                                            <span class="badge bg-info-lt">{{ $project->crm_project_id }}</span>
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h4>Statistik</h4>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="card bg-primary-lt">
                                            <div class="card-body text-center">
                                                <div class="h2 mb-0">{{ $stats['total_affiliators'] }}</div>
                                                <div class="small">Total Affiliator</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-success-lt">
                                            <div class="card-body text-center">
                                                <div class="h2 mb-0">{{ $stats['verified_leads'] }}</div>
                                                <div class="small">Lead Verified</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-blue-lt">
                                            <div class="card-body text-center">
                                                <div class="h2 mb-0">{{ $stats['total_units'] }}</div>
                                                <div class="small">Total Unit</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-yellow-lt">
                                            <div class="card-body text-center">
                                                @php $pendingWithdrawals = $project->commissionWithdrawals()->where('status', 'pending')->count(); @endphp
                                                <div class="h2 mb-0">{{ $pendingWithdrawals }}</div>
                                                <div class="small">Penarikan Pending</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($project->description)
                        <div class="mt-4">
                            <h4>Deskripsi</h4>
                            <div class="markdown">
                                {!! $project->description !!}
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="tab-pane" id="terms">
                        <div class="markdown">
                            {!! $project->terms_and_conditions !!}
                        </div>
                    </div>
                    
                    <div class="tab-pane" id="additional-info">
                        @if($project->additional_info)
                            <div class="markdown">
                                {!! $project->additional_info !!}
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

@endsection