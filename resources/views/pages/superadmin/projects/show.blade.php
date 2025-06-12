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
                {{-- <div class="text-secondary mb-2">{{ $project->slug }}</div> --}}
                @if($project->developer_name)
                    <div class="text-secondary mb-2">{{ $project->developer_name }}</div>
                @endif
                <div class="row">
                    <div class="col-auto">
                        <!-- Registration Type Badge -->
                        <span class="badge bg-{{ $project->registration_type === 'manual' ? 'blue' : ($project->registration_type === 'internal' ? 'primary' : 'secondary') }}-lt me-2">
                            {{ $project->registration_type_label }}
                        </span>
                        
                        <!-- Project Active Status -->
                        <span class="badge bg-{{ $project->is_active ? 'success' : 'secondary' }}-lt me-2">
                            {{ $project->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                        
                        <!-- Registration Status untuk Manual Projects -->
                        @if($project->is_manual_registration)
                            <span class="badge bg-{{ $project->registration_status_color }}-lt me-2">
                                {{ $project->registration_status_label }}
                            </span>
                        @endif
                        
                        @if($project->require_digital_signature)
                            <span class="badge bg-blue-lt">Tanda Tangan Digital Wajib</span>
                        @endif
                        
                        <!-- CRM Connection -->
                        @if($project->crm_project_id)
                            <span class="badge bg-info-lt">
                                <i class="ti ti-link me-1"></i>
                                CRM ID: {{ $project->crm_project_id }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="btn-list">
                    @if($project->is_manual_registration)
                        @if($project->registration_status === 'pending')
                            <!-- Pending Registration Actions -->
                            <button type="button" class="btn btn-success" 
                                    data-bs-toggle="modal" data-bs-target="#approve-modal">
                                <i class="ti ti-check me-1"></i>
                                Setujui
                            </button>
                            <button type="button" class="btn btn-danger" 
                                    data-bs-toggle="modal" data-bs-target="#reject-modal">
                                <i class="ti ti-x me-1"></i>
                                Tolak
                            </button>
                        @elseif($project->registration_status === 'approved')
                            <!-- Approved Project Actions -->
                            <a href="{{ route('superadmin.projects.admins.index', $project) }}" class="btn btn-outline-primary">
                                <i class="ti ti-users me-1"></i>
                                Kelola Admin
                            </a>
                            <a href="{{ route('superadmin.projects.units.index', $project) }}" class="btn btn-outline-primary">
                                <i class="ti ti-home me-1"></i>
                                Kelola Unit
                            </a>
                        @endif
                    @else
                        <!-- Standard Project Actions (Internal/CRM) -->
                        <a href="{{ route('superadmin.projects.admins.index', $project) }}" class="btn btn-outline-primary">
                            <i class="ti ti-users me-1"></i>
                            Kelola Admin
                        </a>
                        <a href="{{ route('superadmin.projects.units.index', $project) }}" class="btn btn-outline-primary">
                            <i class="ti ti-home me-1"></i>
                            Kelola Unit
                        </a>
                    @endif
                    
                    <a href="{{ route('superadmin.projects.edit', $project) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i>
                        Edit Project
                    </a>
                    
                    <div class="dropdown">
                        <button class="btn btn-icon" data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu">
                            @if($project->registration_status === 'approved' || !$project->is_manual_registration)
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
                            @endif
                            <button type="button" class="dropdown-item text-danger delete-btn"
                                    data-name="{{ $project->name }}"
                                    data-url="{{ route('superadmin.projects.destroy', $project) }}">
                                <i class="ti ti-trash me-2"></i>
                                Hapus Project
                            </button>
                        </div>
                    </div>
                    
                    {{-- <a href="{{ route('superadmin.projects.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>
                        Kembali
                    </a> --}}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-3">
    <div class="col-4">
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
    <div class="col-4">
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
    <div class="col-4">
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
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Project Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Informasi Project</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Project</label>
                            <div class="form-control-plaintext fw-bold">{{ $project->name }}</div>
                        </div>
                    </div>
                    @if($project->developer_name)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Developer</label>
                            <div class="form-control-plaintext">{{ $project->developer_name }}</div>
                        </div>
                    </div>
                    @endif
                </div>
                
                <div class="row">
                    @if($project->location)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Lokasi</label>
                            <div class="form-control-plaintext">{{ $project->location }}</div>
                        </div>
                    </div>
                    @endif
                    @if($project->website_url)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Website</label>
                            <div class="form-control-plaintext">
                                <a href="{{ $project->website_url }}" target="_blank">
                                    {{ $project->website_url }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                @if($project->description)
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <div class="form-control-plaintext">
                        {!! $project->description !!}
                    </div>
                </div>
                @endif

                @if($project->start_date || $project->end_date)
                <div class="mb-3">
                    <label class="form-label">Periode Project</label>
                    <div class="form-control-plaintext">{{ $project->project_period }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Files (if available) -->
        @if($project->logo || $project->brochure_file || $project->price_list_file)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">File Project</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($project->logo)
                    <div class="col-md-4">
                        <label class="form-label">Logo</label>
                        <div class="mb-2">
                            <img src="{{ $project->logo_url }}" 
                                 alt="Logo" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    </div>
                    @endif
                    @if($project->brochure_file)
                    <div class="col-md-4">
                        <label class="form-label">Brosur</label>
                        <div>
                            <a href="{{ $project->brochure_file_url }}" 
                               target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-file-text me-1"></i>
                                Lihat Brosur
                            </a>
                        </div>
                    </div>
                    @endif
                    @if($project->price_list_file)
                    <div class="col-md-4">
                        <label class="form-label">Price List</label>
                        <div>
                            <a href="{{ $project->price_list_file_url }}" 
                               target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-file-text me-1"></i>
                                Lihat Price List
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Units -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Data Unit</h3>
                @if($project->registration_status === 'approved' || !$project->is_manual_registration)
                    <a href="{{ route('superadmin.projects.units.index', $project) }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-settings me-1"></i>
                        Kelola Unit
                    </a>
                @endif
            </div>
            <div class="card-body p-0">
                @if($project->units->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th>Harga</th>
                                    <th>Komisi</th>
                                    <th>Spesifikasi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->units->take(10) as $unit)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($unit->image)
                                                <img src="{{ $unit->image_url }}" alt="{{ $unit->name }}" 
                                                     class="avatar avatar-sm me-2">
                                            @else
                                                <div class="avatar avatar-sm bg-blue-lt me-2">
                                                    {{ substr($unit->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $unit->name }}</div>
                                                @if($unit->unit_type)
                                                    <div class="text-secondary small">{{ $unit->unit_type_display }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $unit->price_formatted }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $unit->commission_display }}</div>
                                        <div class="text-secondary small">{{ $unit->commission_amount_formatted }}</div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $unit->unit_specs ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $unit->is_active ? 'success' : 'secondary' }}-lt">
                                            {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($project->units->count() > 10)
                        <div class="card-footer text-center">
                            <a href="{{ route('superadmin.projects.units.index', $project) }}" class="text-secondary">
                                Lihat {{ $project->units->count() - 10 }} unit lainnya
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <div class="text-secondary">
                            <i class="ti ti-home-off icon icon-xl mb-2"></i>
                            <div>Belum ada unit yang terdaftar</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Commission & PIC Info (for manual registration) -->
        @if($project->is_manual_registration && ($project->commission_payment_trigger || $project->pic_name))
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Komisi & PIC</h3>
            </div>
            <div class="card-body">
                @if($project->commission_payment_trigger)
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Komisi Dibayar Setelah</label>
                            <div class="form-control-plaintext">
                                {{ $project->commission_payment_trigger_label }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($project->pic_name)
                <h5 class="mb-3">Informasi PIC</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Nama PIC</label>
                            <div class="form-control-plaintext">{{ $project->pic_name }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Phone PIC</label>
                            <div class="form-control-plaintext">{{ $project->pic_phone }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Email PIC</label>
                            <div class="form-control-plaintext">{{ $project->pic_email }}</div>
                        </div>
                    </div>
                </div>

                @if($project->registration_status === 'approved' && $project->picUser)
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Akun PIC telah dibuat:</strong> 
                        PIC dapat login menggunakan email {{ $project->picUser->email }} 
                        dengan password default yang telah dikirim.
                    </div>
                @endif
                @endif
            </div>
        </div>
        @endif

        <!-- Recent Leads -->
        <div class="card mb-3">
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

        <!-- Project Details (Terms & Additional Info) -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                    <li class="nav-item">
                        <a href="#terms" class="nav-link active" data-bs-toggle="tab">Syarat & Ketentuan</a>
                    </li>
                    @if($project->additional_info)
                    <li class="nav-item">
                        <a href="#additional-info" class="nav-link" data-bs-toggle="tab">Informasi Tambahan</a>
                    </li>
                    @endif
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="terms">
                        <div class="markdown">
                            {!! $project->terms_and_conditions !!}
                        </div>
                    </div>
                    @if($project->additional_info)
                    <div class="tab-pane" id="additional-info">
                        <div class="markdown">
                            {!! $project->additional_info !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Registration Info (for manual registration) -->
        @if($project->is_manual_registration && $project->latestRegistration)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Info Pendaftaran</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Pendaftar</label>
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-sm me-2">{{ $project->latestRegistration->submittedBy->initials }}</span>
                        <div>
                            <div class="fw-bold">{{ $project->latestRegistration->submittedBy->name }}</div>
                            <div class="text-secondary small">{{ $project->latestRegistration->submittedBy->email }}</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Daftar</label>
                    <div class="form-control-plaintext">
                        {{ $project->latestRegistration->created_at->format('d F Y, H:i') }}
                        <div class="text-secondary small">{{ $project->latestRegistration->created_at->diffForHumans() }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <div class="form-control-plaintext">
                        <span class="badge bg-{{ $project->registration_status_color }}-lt">
                            {{ $project->registration_status_label }}
                        </span>
                    </div>
                </div>

                @if($project->latestRegistration->reviewed_at)
                    <div class="mb-3">
                        <label class="form-label">Tanggal Review</label>
                        <div class="form-control-plaintext">
                            {{ $project->latestRegistration->reviewed_at->format('d F Y, H:i') }}
                            <div class="text-secondary small">{{ $project->latestRegistration->reviewed_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @endif

                @if($project->latestRegistration->reviewedBy)
                    <div class="mb-3">
                        <label class="form-label">Direview Oleh</label>
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-sm me-2">{{ $project->latestRegistration->reviewedBy->initials }}</span>
                            <div>
                                <div class="fw-bold">{{ $project->latestRegistration->reviewedBy->name }}</div>
                                <div class="text-secondary small">{{ $project->latestRegistration->reviewedBy->email }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($project->latestRegistration->review_notes)
                    <div class="mb-3">
                        <label class="form-label">
                            {{ $project->registration_status === 'rejected' ? 'Alasan Penolakan' : 'Catatan Review' }}
                        </label>
                        <div class="form-control-plaintext">
                            <div class="alert alert-{{ $project->registration_status === 'rejected' ? 'danger' : 'info' }} alert-important">
                                {{ $project->latestRegistration->review_notes }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Project Admins -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Admin Project</h3>
                @if($project->registration_status === 'approved' || !$project->is_manual_registration)
                    <a href="{{ route('superadmin.projects.admins.index', $project) }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-settings me-1"></i>
                        Kelola
                    </a>
                @endif
            </div>
            <div class="card-body p-0">
                @if($project->admins->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($project->admins->take(5) as $admin)
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
                                    @if($admin->is_pic)
                                        <span class="badge badge-sm bg-info-lt mt-1">PIC</span>
                                    @endif
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-{{ $admin->is_active ? 'success' : 'secondary' }}-lt">
                                        {{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($project->admins->count() > 5)
                        <div class="card-footer text-center">
                            <a href="{{ route('superadmin.projects.admins.index', $project) }}" class="text-secondary">
                                Lihat {{ $project->admins->count() - 5 }} admin lainnya
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <div class="text-secondary">
                            <i class="ti ti-users-off icon icon-xl mb-2"></i>
                            <div>Belum ada admin</div>
                            @if($project->registration_status === 'approved' || !$project->is_manual_registration)
                                <div class="mt-2">
                                    <a href="{{ route('superadmin.projects.admins.create', $project) }}" class="btn btn-sm btn-primary">
                                        Tambah Admin
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Project Statistics -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-chart-bar me-2"></i>Statistik Project</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h2 mb-0">{{ $project->total_affiliators }}</div>
                            <div class="text-secondary">Affiliator</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h2 mb-0">{{ $project->total_leads }}</div>
                            <div class="text-secondary">Lead</div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h3 mb-0 text-success">{{ $project->active_affiliators }}</div>
                            <div class="text-secondary small">Aktif</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h3 mb-0 text-blue">{{ $project->verified_leads }}</div>
                            <div class="text-secondary small">Verified</div>
                        </div>
                    </div>
                </div>
                @if($stats['total_commission_paid'] > 0)
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="text-center">
                            <div class="h4 mb-0 text-green">Rp {{ number_format($stats['total_commission_paid'], 0, ',', '.') }}</div>
                            <div class="text-secondary small">Total Komisi Dibayar</div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal (for pending manual registration) -->
@if($project->is_manual_registration && $project->registration_status === 'pending')
<div class="modal fade" id="approve-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.projects.approve-registration', $project) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Pendaftaran Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Anda akan menyetujui pendaftaran project <strong>{{ $project->name }}</strong> 
                        oleh {{ $project->latestRegistration->submittedBy->name }}.
                    </p>
                    
                    <div class="alert alert-info">
                        <h4 class="alert-title">Yang akan terjadi setelah disetujui:</h4>
                        <ul class="mb-0">
                            <li>Project akan diaktifkan dan dapat dilihat oleh affiliator</li>
                            <li>Semua unit dalam project akan diaktifkan</li>
                            <li>Akun admin akan dibuat untuk PIC ({{ $project->pic_name }})</li>
                            <li>PIC akan mendapat notifikasi dan dapat login ke sistem</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan (opsional)</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Tambahkan catatan untuk pendaftar..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check me-1"></i>
                        Setujui Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal (for pending manual registration) -->
<div class="modal fade" id="reject-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.projects.reject-registration', $project) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Pendaftaran Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Anda akan menolak pendaftaran project <strong>{{ $project->name }}</strong> 
                        oleh {{ $project->latestRegistration->submittedBy->name }}.
                    </p>
                    
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        Pendaftar akan mendapat notifikasi penolakan beserta alasan yang Anda berikan.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="4" required
                                  placeholder="Jelaskan dengan detail alasan penolakan agar pendaftar dapat memperbaiki dan mendaftar ulang..."></textarea>
                        <small class="form-hint">Berikan alasan yang konstruktif untuk membantu pendaftar</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-x me-1"></i>
                        Tolak Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@include('components.delete-modal')
@endsection