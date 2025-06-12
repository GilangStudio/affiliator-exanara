@extends('layouts.main')

@section('title', 'Detail Project - ' . $project->name)

@push('styles')
<style>
.progress-circle {
    position: relative;
    display: inline-block;
}

.progress-ring {
    transform: rotate(-90deg);
}

.progress-ring-circle {
    fill: transparent;
    stroke: #e9ecef;
    stroke-width: 4;
    stroke-dasharray: 326.725;
    stroke-dashoffset: 326.725;
    transition: stroke-dashoffset 0.5s ease-in-out;
}

.progress-success .progress-ring-circle {
    stroke: #2fb344;
}

.progress-warning .progress-ring-circle {
    stroke: #f59f00;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}
</style>
@endpush

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
                @if($project->location)
                    <div class="text-secondary mb-2">
                        <i class="ti ti-map-pin me-1"></i>
                        {{ $project->location }}
                    </div>
                @endif
                <div class="row">
                    <div class="col-auto">
                        @if($userProject)
                            <span class="badge bg-success-lt me-2">
                                <i class="ti ti-check me-1"></i>
                                Terdaftar
                            </span>
                            <span class="badge bg-{{ $userProject->verification_status == 'verified' ? 'success' : ($userProject->verification_status == 'rejected' ? 'danger' : 'warning') }}-lt">
                                {{ ucfirst($userProject->verification_status) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('affiliator.project.index') }}">Project</a></li>
                        <li class="breadcrumb-item active">{{ $project->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- User Status & Actions -->
@if($userProject)
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-user-check me-2"></i>
            Status Partisipasi Anda
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <!-- Status Information -->
                <div class="mt-3">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-secondary small">Status Verifikasi</div>
                            <span class="badge bg-{{ $userProject->verification_status == 'verified' ? 'success' : ($userProject->verification_status == 'rejected' ? 'danger' : 'warning') }}-lt">
                                {{ ucfirst($userProject->verification_status) }}
                            </span>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary small">Status Affiliator</div>
                            <span class="badge bg-{{ $userProject->status == 'active' ? 'success' : ($userProject->status == 'suspended' ? 'danger' : 'secondary') }}-lt">
                                {{ $userProject->status_label }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($userProject->verification_notes)
                <div class="mt-3">
                    <div class="alert alert-{{ $userProject->verification_status == 'rejected' ? 'danger' : 'info' }}">
                        <strong>Catatan Admin:</strong> {{ $userProject->verification_notes }}
                    </div>
                </div>
                @endif
            </div>
            <div class="col-md-6">
                <!-- Progress Circle -->
                <div class="text-center">
                    <div class="progress-circle progress-{{ $userProject->completion_progress == 100 ? 'success' : 'warning' }} mb-3" 
                         data-progress="{{ $userProject->completion_progress }}">
                        <svg class="progress-ring" width="120" height="120">
                            <circle class="progress-ring-circle" cx="60" cy="60" r="52"></circle>
                        </svg>
                        <div class="progress-text">
                            <div class="h2 mb-0">{{ number_format($userProject->completion_progress, 0) }}%</div>
                            <div class="text-secondary small">Progress</div>
                        </div>
                    </div>
                    <div class="btn-list justify-content-center">
                        @if($userProject->can_add_leads)
                            <a href="{{ route('affiliator.leads.project.create', $project->slug) }}" class="btn btn-success">
                                <i class="ti ti-plus me-1"></i>
                                Tambah Lead
                            </a>
                        @else
                            <span class="btn btn-outline-secondary disabled">
                                <i class="ti ti-lock me-1"></i>
                                Perlu Verifikasi
                            </span>
                        @endif
                        <a href="{{ route('affiliator.leads.project', $project->slug) }}" class="btn btn-outline-primary">
                            <i class="ti ti-users me-1"></i>
                            Lihat Lead Saya
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Stats Cards -->
<div class="row g-3 mb-3">
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
                    <div class="subheader">Total Unit</div>
                </div>
                <div class="h1 mb-0">{{ number_format($stats['total_units']) }}</div>
                <div class="d-flex mb-2">
                    <div class="text-secondary">
                        <span class="text-blue d-inline-flex align-items-center lh-1">
                            Unit tersedia
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
                    <div class="subheader">Komisi</div>
                </div>
                <div class="h2 mb-0 text-success">{{ $stats['commission_info']['range'] }}</div>
                {{-- <div class="d-flex mb-2">
                    <div class="text-secondary small">
                        {{ $stats['commission_info']['description'] }}
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Lead Saya</div>
                </div>
                @if($userProject)
                    @php
                        $myLeads = $userProject->leads()->count();
                        $myVerifiedLeads = $userProject->leads()->verified()->count();
                    @endphp
                    <div class="h1 mb-0">{{ $myVerifiedLeads }}/{{ $myLeads }}</div>
                    <div class="d-flex mb-2">
                        <div class="text-secondary">
                            <span class="text-green d-inline-flex align-items-center lh-1">
                                Verified/Total
                            </span>
                        </div>
                    </div>
                @else
                    <div class="h1 mb-0">-</div>
                    <div class="d-flex mb-2">
                        <div class="text-secondary">
                            <span class="d-inline-flex align-items-center lh-1">
                                Belum bergabung
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Project Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                    <li class="nav-item">
                        <a href="#overview" class="nav-link active" data-bs-toggle="tab">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a href="#units" class="nav-link" data-bs-toggle="tab">Unit ({{ $project->units->count() }})</a>
                    </li>
                    <li class="nav-item">
                        <a href="#terms" class="nav-link" data-bs-toggle="tab">Syarat & Ketentuan</a>
                    </li>
                    @if($project->faqs->count() > 0)
                    <li class="nav-item">
                        <a href="#faq" class="nav-link" data-bs-toggle="tab">FAQ</a>
                    </li>
                    @endif
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div class="tab-pane active" id="overview">
                        @if($project->description)
                            <div class="markdown mb-4">
                                {!! $project->description !!}
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <h4>Informasi Project</h4>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="140">Nama Project:</td>
                                        <td><strong>{{ $project->name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Lokasi:</td>
                                        <td>{{ $project->location ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Total Unit:</td>
                                        <td>{{ $stats['total_units'] }} unit</td>
                                    </tr>
                                    <tr>
                                        <td>Total Affiliator:</td>
                                        <td>{{ $stats['total_affiliators'] }} orang</td>
                                    </tr>
                                    <tr>
                                        <td>Tanda Tangan Digital:</td>
                                        <td>
                                            <span class="badge bg-{{ $project->require_digital_signature ? 'blue' : 'secondary' }}-lt">
                                                {{ $project->require_digital_signature ? 'Wajib' : 'Opsional' }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h4>Informasi Komisi</h4>
                                <div class="alert alert-success">
                                    <div class="h4 mb-1">{{ $stats['commission_info']['range'] }}</div>
                                    <div class="text-secondary">{{ $stats['commission_info']['description'] }}</div>
                                </div>
                                
                                @if($project->additional_info)
                                    <h5 class="mt-4">Informasi Tambahan</h5>
                                    <div class="markdown">
                                        {!! $project->additional_info !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Units Tab -->
                    <div class="tab-pane" id="units">
                        @if($project->units->count() > 0)
                            <div class="row">
                                @foreach($project->units as $unit)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <img src="{{ $unit->image_url }}" alt="{{ $unit->name }}" class="card-img-top" style="height: 180px; object-fit: cover;">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $unit->name }}</h5>
                                            <div class="text-success fw-bold mb-2">{{ $unit->price_formatted }}</div>
                                            
                                            @if($unit->unit_specs)
                                                <div class="text-secondary small mb-2">{{ $unit->unit_specs }}</div>
                                            @endif

                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="text-secondary small">Komisi</div>
                                                    <div class="fw-bold text-primary">{{ $unit->commission_display }}</div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="text-secondary small">Estimasi</div>
                                                    <div class="fw-bold text-success">{{ $unit->commission_amount_formatted }}</div>
                                                </div>
                                            </div>

                                            {{-- @if($unit->description)
                                                <div class="mt-2">
                                                    <small class="text-secondary">{{ $unit->description }}</small>
                                                </div>
                                            @endif --}}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="ti ti-building icon icon-xl text-secondary mb-3"></i>
                                <h4 class="text-secondary">Belum ada unit</h4>
                                <p class="text-secondary">Unit untuk project ini belum tersedia.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Terms & Conditions Tab -->
                    <div class="tab-pane" id="terms">
                        <div class="markdown">
                            {!! $project->terms_and_conditions !!}
                        </div>
                    </div>

                    <!-- FAQ Tab -->
                    @if($project->faqs->count() > 0)
                    <div class="tab-pane" id="faq">
                        <div class="accordion">
                            @foreach($project->faqs as $index => $faq)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq-heading-{{ $index }}">
                                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" 
                                            type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#faq-collapse-{{ $index }}" 
                                            aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" 
                                            aria-controls="faq-collapse-{{ $index }}">
                                        {{ $faq->question }}
                                    </button>
                                </h2>
                                <div id="faq-collapse-{{ $index }}" 
                                     class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" 
                                     aria-labelledby="faq-heading-{{ $index }}">
                                    <div class="accordion-body">
                                        <div class="markdown">
                                            {!! $faq->answer !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Action Panel -->
    <div class="col-lg-4">
        @if(!$userProject)
            <!-- Join Project Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-plus me-2"></i>
                        Bergabung dengan Project
                    </h3>
                </div>
                <div class="card-body">
                    @if($canJoin['can_join'])
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            Bergabung dengan project ini untuk mulai mendapatkan komisi dari lead yang Anda kumpulkan.
                        </div>
                        <button type="button" class="btn btn-primary w-100" 
                                onclick="joinProject('{{ $project->slug }}', '{{ $project->name }}')">
                            <i class="ti ti-plus me-1"></i>
                            Bergabung Sekarang
                        </button>
                    @else
                        <div class="alert alert-warning">
                            <i class="ti ti-alert-triangle me-2"></i>
                            {{ $canJoin['reason'] }}
                        </div>
                        <button type="button" class="btn btn-outline-secondary w-100 disabled">
                            <i class="ti ti-lock me-1"></i>
                            Tidak Dapat Bergabung
                        </button>
                    @endif
                </div>
            </div>
        @else
            <!-- Quick Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-rocket me-2"></i>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush list-group-hoverable">
                        @if($userProject->can_add_leads)
                            <a href="{{ route('affiliator.leads.project.create', $project->slug) }}" class="list-group-item list-group-item-action">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <i class="ti ti-plus text-success"></i>
                                    </div>
                                    <div class="col text-truncate">
                                        <div class="text-reset d-block">Tambah Lead Baru</div>
                                        <div class="d-block text-secondary text-truncate mt-n1">
                                            Tambahkan customer baru untuk project ini
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endif
                        
                        <a href="{{ route('affiliator.leads.project', $project->slug) }}" class="list-group-item list-group-item-action">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="ti ti-users text-primary"></i>
                                </div>
                                <div class="col text-truncate">
                                    <div class="text-reset d-block">Lihat Lead Saya</div>
                                    <div class="d-block text-secondary text-truncate mt-n1">
                                        Kelola semua lead untuk project ini
                                    </div>
                                </div>
                                <div class="col-auto">
                                    @if($userProject)
                                        @php $myLeads = $userProject->leads()->count(); @endphp
                                        @if($myLeads > 0)
                                            <span class="badge bg-blue">{{ $myLeads }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-{{ $userProject->status == 'active' ? 'warning' : 'success' }} w-100" 
                                onclick="toggleProjectStatus('{{ $project->slug }}', '{{ $project->name }}', '{{ $userProject->status }}')">
                            <i class="ti ti-{{ $userProject->status == 'active' ? 'pause' : 'play' }} me-1"></i>
                            {{ $userProject->status == 'active' ? 'Nonaktifkan Project' : 'Aktifkan Project' }}
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Project Statistics -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-chart-bar me-2"></i>
                    Statistik Project
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h2 mb-0 text-primary">{{ $stats['total_affiliators'] }}</div>
                            <div class="text-secondary small">Total Affiliator</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h2 mb-0 text-success">{{ $stats['active_affiliators'] }}</div>
                            <div class="text-secondary small">Affiliator Aktif</div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="text-center">
                            <div class="h2 mb-0 text-blue">{{ $stats['total_units'] }}</div>
                            <div class="text-secondary small">Unit Tersedia</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Join Project Modal -->
<div class="modal modal-blur fade" id="join-project-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Bergabung dengan Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin bergabung dengan project <strong id="join-project-name"></strong>?</p>
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Setelah bergabung, Anda perlu melengkapi data dan upload KTP untuk verifikasi.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirm-join-btn">
                    <i class="ti ti-plus me-1"></i>
                    Bergabung
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toggle Project Status Modal -->
<div class="modal modal-blur fade" id="toggle-status-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="toggle-status-title">Konfirmasi Ubah Status Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="toggle-status-message">Apakah Anda yakin ingin mengubah status project <strong id="toggle-project-name"></strong>?</p>
                <div class="alert" id="toggle-status-alert">
                    <i class="ti ti-info-circle me-2"></i>
                    <span id="toggle-status-info"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn" id="confirm-toggle-btn">
                    <i class="ti ti-check me-1"></i>
                    <span id="confirm-toggle-text">Konfirmasi</span>
                </button>
            </div>
        </div>
    </div>
</div>

@include('components.toast')

@endsection

@push('scripts')
<script>
let currentProjectSlug = null;
let currentProjectStatus = null;
let toggleStatusModal = null;
let joinProjectModal = null;

function joinProject(projectSlug, projectName) {
    currentProjectSlug = projectSlug;
    const projectNameEl = document.getElementById('join-project-name');
    if (projectNameEl) {
        projectNameEl.textContent = projectName;
    }
    
    if (!joinProjectModal) {
        joinProjectModal = new bootstrap.Modal(document.getElementById('join-project-modal'));
    }
    joinProjectModal.show();
}

function toggleProjectStatus(projectId, projectName, currentStatus) {
    currentProjectSlug = projectId;
    currentProjectStatus = currentStatus;
    
    const isActive = currentStatus === 'active';
    const action = isActive ? 'menonaktifkan' : 'mengaktifkan';
    const newStatus = isActive ? 'suspended' : 'active';
    
    document.getElementById('toggle-project-name').textContent = projectName;
    document.getElementById('toggle-status-message').innerHTML = `Apakah Anda yakin ingin ${action} project <strong id="toggle-project-name">${projectName}</strong>?`;
    
    const alertDiv = document.getElementById('toggle-status-alert');
    const infoSpan = document.getElementById('toggle-status-info');
    const confirmBtn = document.getElementById('confirm-toggle-btn');
    const confirmText = document.getElementById('confirm-toggle-text');
    
    if (isActive) {
        alertDiv.className = 'alert alert-warning';
        infoSpan.textContent = 'Project akan dinonaktifkan dan Anda tidak dapat menambah lead baru sampai diaktifkan kembali.';
        confirmBtn.className = 'btn btn-warning';
        confirmText.textContent = 'Nonaktifkan';
    } else {
        alertDiv.className = 'alert alert-success';
        infoSpan.textContent = 'Project akan diaktifkan dan Anda dapat kembali menambah lead baru.';
        confirmBtn.className = 'btn btn-success';
        confirmText.textContent = 'Aktifkan';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('toggle-status-modal'));
    modal.show();
}

// Confirm join project
document.addEventListener('DOMContentLoaded', function() {
    const confirmJoinBtn = document.getElementById('confirm-join-btn');
    if (confirmJoinBtn) {
        confirmJoinBtn.addEventListener('click', function() {
            if (!currentProjectSlug) return;
            
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Bergabung...';
            btn.disabled = true;
            
            fetch(`/project/${currentProjectSlug}/join`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan jaringan', 'error');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                if (joinProjectModal) {
                    joinProjectModal.hide();
                }
            });
        });
    }

    // Confirm toggle project status
    const confirmToggleBtn = document.getElementById('confirm-toggle-btn');
    if (confirmToggleBtn) {
        confirmToggleBtn.addEventListener('click', function() {
            if (!currentProjectSlug) return;
            
            const btn = this;
            const originalText = btn.innerHTML;
            const isActivating = currentProjectStatus === 'suspended';
            const loadingText = isActivating ? 'Mengaktifkan...' : 'Menonaktifkan...';
            
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>${loadingText}`;
            btn.disabled = true;
            
            fetch(`/project/${currentProjectSlug}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan jaringan', 'error');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                if (toggleStatusModal) {
                    toggleStatusModal.hide();
                }
                // Clean up modal state
                setTimeout(() => {
                    currentProjectSlug = null;
                    currentProjectStatus = null;
                }, 500);
            });
        });
    }
});

// Progress circle animation
document.addEventListener('DOMContentLoaded', function() {
    const progressCircles = document.querySelectorAll('.progress-circle');
    
    progressCircles.forEach(circle => {
        const progress = parseInt(circle.dataset.progress);
        const progressRing = circle.querySelector('.progress-ring-circle');
        const circumference = 2 * Math.PI * 52; // radius = 52
        
        progressRing.style.strokeDasharray = circumference;
        progressRing.style.strokeDashoffset = circumference;
        
        // Animate the progress
        setTimeout(() => {
            const offset = circumference - (progress / 100 * circumference);
            progressRing.style.strokeDashoffset = offset;
        }, 500);
    });
});

</script>
@endpush
