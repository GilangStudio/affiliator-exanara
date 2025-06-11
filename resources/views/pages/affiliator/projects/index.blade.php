@extends('layouts.main')

@section('title', 'Project')

@section('content')

@include('components.alert')
@include('components.toast')

<div class="row">
    <div class="col-12">
        <!-- Header Section -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="mb-2">Project yang Tersedia</h2>
                        <p class="text-secondary mb-0">
                            Bergabung dengan project untuk mulai mendapatkan komisi. 
                            Anda dapat bergabung maksimal {{ $maxProjects }} project.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary-lt me-2">
                                {{ $currentProjectCount }}/{{ $maxProjects }}
                            </span>
                            <span class="text-secondary">Project terdaftar</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Filter Section -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Cari project..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="location">
                            <option value="">Semua Lokasi</option>
                            @foreach($locations as $location)
                                <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                                    {{ $location }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i>
                            Cari
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('affiliator.project.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i>
                            Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>
    
        <!-- My Projects Section -->
        @if($userProjects->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-user-check me-2"></i>
                    Project Saya ({{ $userProjects->count() }})
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($userProjects as $userProject)
                        @php $project = $userProject->project; @endphp
                        @if($project)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card">
                                <div class="card-status-top bg-{{ $userProject->verification_status == 'verified' ? 'success' : ($userProject->verification_status == 'rejected' ? 'danger' : 'warning') }}"></div>
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        @if($project->logo)
                                            <img src="{{ $project->logo_url }}" alt="{{ $project->name }}" 
                                                 class="avatar avatar-md me-3">
                                        @else
                                            <div class="avatar avatar-md bg-primary-lt me-3">
                                                {{ substr($project->name, 0, 2) }}
                                            </div>
                                        @endif
                                        <div class="flex-fill">
                                            <h4 class="mb-1">{{ $project->name }}</h4>
                                            @if($project->location)
                                                <div class="text-secondary small">
                                                    <i class="ti ti-map-pin me-1"></i>
                                                    {{ $project->location }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
    
                                    <!-- Status Verifikasi -->
                                    <div class="mb-3">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="text-secondary small">Status Verifikasi</div>
                                                <span class="badge bg-{{ $userProject->verification_status == 'verified' ? 'success' : ($userProject->verification_status == 'rejected' ? 'danger' : 'warning') }}-lt">
                                                    {{ ucfirst($userProject->verification_status) }}
                                                </span>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-secondary small">Status Project</div>
                                                <span class="badge bg-{{ $userProject->status == 'active' ? 'success' : ($userProject->status == 'suspended' ? 'danger' : 'secondary') }}-lt">
                                                    {{ $userProject->status_label }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
    
                                    <!-- Progress Bar -->
                                    {{-- <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-secondary small">Progress Lengkap</span>
                                            <span class="text-secondary small">{{ number_format($userProject->completion_progress, 0) }}%</span>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-{{ $userProject->completion_progress == 100 ? 'success' : 'warning' }}" 
                                                 style="width: {{ $userProject->completion_progress }}%"></div>
                                        </div>
                                    </div> --}}
    
                                    <!-- Project Stats -->
                                    <div class="row g-2 mb-3">
                                        <div class="col-4">
                                            <div class="text-center">
                                                <div class="h4 mb-0">{{ $project->total_affiliators ?? 0 }}</div>
                                                <div class="text-secondary small">Affiliator</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-center">
                                                <div class="h4 mb-0">{{ $project->total_units ?? 0 }}</div>
                                                <div class="text-secondary small">Unit</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-center">
                                                @php
                                                    $userLeads = $userProject->leads()->count();
                                                    $verifiedLeads = $userProject->leads()->verified()->count();
                                                @endphp
                                                <div class="h4 mb-0">{{ $verifiedLeads }}/{{ $userLeads }}</div>
                                                <div class="text-secondary small">Lead</div>
                                            </div>
                                        </div>
                                    </div>
    
                                    <!-- Action Buttons -->
                                    <div class="btn-list">
                                        <a href="{{ route('affiliator.project.show', $project) }}" class="btn btn-primary w-100">
                                            <i class="ti ti-eye me-1"></i>
                                            Lihat Detail
                                        </a>
                                        @if($userProject->can_add_leads)
                                            {{-- <a href="{{ route('affiliator.leads.project.create', $project->slug) }}" class="btn btn-success w-100">
                                                <i class="ti ti-plus me-1"></i>
                                                Tambah Lead
                                            </a> --}}
                                        @else
                                            <span class="btn btn-outline-secondary w-100 disabled">
                                                <i class="ti ti-lock me-1"></i>
                                                Perlu Verifikasi
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    
        <!-- Available Projects Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-folder me-2"></i>
                    Project Tersedia ({{ $allProjects->count() }})
                </h3>
                @if($currentProjectCount >= $maxProjects)
                    <div class="card-actions">
                        <span class="badge bg-yellow-lt">
                            <i class="ti ti-alert-triangle me-1"></i>
                            Batas maksimal project tercapai
                        </span>
                    </div>
                @endif
            </div>
            <div class="card-body">
                @if($allProjects->count() > 0)
                    <div class="row">
                        @foreach($allProjects as $project)
                            @php
                                $userProject = $userProjects->get($project->id);
                                $isJoined = $userProject !== null;
                                $canJoin = $project->canBeJoinedBy(Auth::user());
                            @endphp
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card {{ $isJoined ? 'border-success' : '' }}">
                                    
                                    <!-- Project Image -->
                                    @if($project->logo)
                                        <img src="{{ $project->logo_url }}" alt="{{ $project->name }}" 
                                             class="card-img-top" style="height: 200px; object-fit: cover;">
                                    @else
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                            <div class="avatar avatar-xl" style="box-shadow: none;">
                                                <i class="ti ti-building-skyscraper"></i>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h4 class="card-title mb-1">{{ $project->name }}</h4>
                                            {{-- @if($isJoined)
                                                <span class="badge bg-success-lt">
                                                    <i class="ti ti-check"></i>
                                                </span>
                                            @endif --}}
                                        </div>
                                        
                                        @if($project->location)
                                            <div class="text-secondary mb-2">
                                                <i class="ti ti-map-pin me-1"></i>
                                                {{ $project->location }}
                                            </div>
                                        @endif
    
                                        {{-- @if($project->description)
                                            <p class="text-secondary small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ strip_tags($project->description) }}
                                            </p>
                                        @endif --}}
    
                                        <!-- Commission Info -->
                                        <div class="mb-3">
                                            <div class="text-secondary small mb-1">Komisi</div>
                                            <div class="fw-bold text-success">{{ $project->commission_info['range'] }}</div>
                                            <div class="text-secondary small">{{ $project->commission_info['description'] }}</div>
                                        </div>
    
                                        <!-- Project Stats -->
                                        <div class="row g-2 mb-3">
                                            <div class="col-4">
                                                <div class="text-center">
                                                    <div class="h5 mb-0">{{ $project->total_affiliators }}</div>
                                                    <div class="text-secondary small">Affiliator</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-center">
                                                    <div class="h5 mb-0">{{ $project->active_affiliators }}</div>
                                                    <div class="text-secondary small">Aktif</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-center">
                                                    <div class="h5 mb-0">{{ $project->total_units }}</div>
                                                    <div class="text-secondary small">Unit</div>
                                                </div>
                                            </div>
                                        </div>
    
                                        <!-- Action Buttons -->
                                        <div class="btn-list">
                                            <a href="{{ route('affiliator.project.show', $project) }}" class="btn btn-primary w-100">
                                                <i class="ti ti-eye me-1"></i>
                                                Lihat Detail
                                            </a>
                                            
                                            @if($isJoined)
                                                <button type="button" class="btn btn-{{ $userProject->status == 'active' ? 'warning' : 'success' }} w-100" 
                                                        onclick="toggleProjectStatus('{{ $project->slug }}', '{{ $project->name }}', '{{ $userProject->status }}')">
                                                    <i class="ti ti-{{ $userProject->status == 'active' ? 'pause' : 'play' }} me-1"></i>
                                                    {{ $userProject->status == 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            @else
                                                @if($canJoin['can_join'])
                                                    <a href="{{ route('affiliator.project.join.index', ['project' => $project->slug]) }}" type="button" class="btn btn-success w-100" 
                                                            onclick="joinProject({{ $project->id }}, '{{ $project->name }}')">
                                                        <i class="ti ti-plus me-1"></i>
                                                        Bergabung
                                                    </a>
                                                @else
                                                    <span class="btn btn-secondary w-100 disabled" 
                                                          data-bs-toggle="tooltip" title="{{ $canJoin['reason'] }}">
                                                        <i class="ti ti-lock me-1"></i>
                                                        Tidak Dapat Bergabung
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="ti ti-folder-off icon icon-xl text-secondary"></i>
                        </div>
                        <h3 class="text-secondary">Tidak ada project yang tersedia</h3>
                        <p class="text-secondary">Saat ini belum ada project yang dapat Anda ikuti.</p>
                    </div>
                @endif
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

@endsection

@push('scripts')
<script>
let currentProjectSlug = null;
let currentProjectStatus = null;

function toggleProjectStatus(projectSlug, projectName, currentStatus) {
    currentProjectSlug = projectSlug;
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

// Confirm toggle project status
document.getElementById('confirm-toggle-btn').addEventListener('click', function() {
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
            }, 1500);
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan jaringan', 'danger');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        bootstrap.Modal.getInstance(document.getElementById('toggle-status-modal')).hide();
    });
});

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush