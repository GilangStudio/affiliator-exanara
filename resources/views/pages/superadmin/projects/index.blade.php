@extends('layouts.main')

@section('title', 'Kelola Project')

@section('content')

@include('components.alert')

<!-- Statistics Cards dengan Registration Info -->
<div class="row mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Project</div>
                </div>
                <div class="h1 mb-0">{{ number_format($statistics['total_projects']) }}</div>
                <div class="d-flex mb-2">
                    <div class="text-secondary">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            {{ number_format($statistics['active_projects']) }} aktif
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
                    <div class="subheader">Manual Registration</div>
                </div>
                <div class="h1 mb-0">{{ number_format($statistics['manual_projects']) }}</div>
                <div class="d-flex mb-2">
                    <div class="text-secondary">
                        <span class="text-warning d-inline-flex align-items-center lh-1">
                            {{ number_format($statistics['pending_registrations']) }} pending
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
                    <div class="subheader">CRM Projects</div>
                </div>
                <div class="h1 mb-0">{{ number_format($statistics['crm_projects']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Butuh Review</div>
                </div>
                <div class="h1 mb-0 text-warning">{{ number_format($statistics['needs_review']) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Daftar Project</h3>
            <div class="btn-list">
                @if($statistics['pending_registrations'] > 0)
                    <form action="{{ route('superadmin.projects.bulk-approve-registrations') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success" id="bulk-approve-btn" style="display: none;">
                            <i class="ti ti-checks me-1"></i>
                            Setujui Terpilih (<span id="selected-count">0</span>)
                        </button>
                    </form>
                @endif
                <a href="{{ route('superadmin.projects.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Project
                </a>
            </div>
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
                    <select class="form-select" name="registration_type">
                        <option value="">Semua Tipe</option>
                        <option value="internal" {{ request('registration_type') == 'internal' ? 'selected' : '' }}>Internal</option>
                        <option value="crm" {{ request('registration_type') == 'crm' ? 'selected' : '' }}>CRM</option>
                        <option value="manual" {{ request('registration_type') == 'manual' ? 'selected' : '' }}>Manual Registration</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="registration_status">
                        <option value="">Semua Status Registration</option>
                        <option value="pending" {{ request('registration_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('registration_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('registration_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                            <th>Project</th>
                            <th>Tipe & Status</th>
                            <th>Developer/Pendaftar</th>
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
                                @if($project->registration_status === 'pending')
                                    <input type="checkbox" class="form-check-input project-checkbox" 
                                           value="{{ $project->id }}" name="project_ids[]">
                                @endif
                            </td>
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
                                        <div class="fw-bold">{{ $project->name }}</div>
                                        @if($project->location)
                                            <div class="text-secondary small">{{ strtoupper($project->location) }}</div>
                                        @endif
                                        
                                        <!-- Registration Info untuk Manual Projects -->
                                        {{-- @if($project->is_manual_registration && $project->latestRegistration)
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    <i class="ti ti-user me-1"></i>
                                                    Didaftarkan oleh: {{ $project->latestRegistration->submittedBy->name }}
                                                </small>
                                            </div>
                                        @endif --}}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mb-1">
                                    <!-- Registration Type Badge -->
                                    <span class="badge bg-{{ $project->registration_type === 'manual' ? 'blue' : ($project->registration_type === 'internal' ? 'primary' : 'secondary') }}-lt">
                                        {{ $project->registration_type_label }}
                                    </span>
                                    
                                    <!-- CRM Connection untuk CRM projects -->
                                    @if($project->crm_project_id)
                                        <i class="ti ti-link text-info ms-1" 
                                           data-bs-toggle="tooltip" 
                                           title="Terhubung dengan CRM Project ID: {{ $project->crm_project_id }}"></i>
                                    @endif
                                </div>
                                
                                <div class="mb-1">
                                    <!-- Project Active Status -->
                                    <span class="badge bg-{{ $project->is_active ? 'success' : 'secondary' }}-lt">
                                        {{ $project->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </div>
                                
                                <!-- Registration Status untuk Manual Projects -->
                                @if($project->is_manual_registration)
                                    <div>
                                        <span class="badge bg-{{ $project->registration_status_color }}-lt">
                                            {{ $project->registration_status_label }}
                                        </span>
                                        
                                        @if($project->registration_status === 'pending')
                                            <div class="text-warning small mt-1">
                                                <i class="ti ti-clock me-1"></i>
                                                Butuh Review
                                            </div>
                                        @elseif($project->registration_status === 'rejected' && $project->rejection_reason)
                                            <div class="text-danger small mt-1" data-bs-toggle="tooltip" 
                                                 title="{{ $project->rejection_reason }}">
                                                <i class="ti ti-info-circle me-1"></i>
                                                {{ Str::limit($project->rejection_reason, 30) }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($project->is_manual_registration)
                                    <!-- Developer info untuk manual registration -->
                                    <div class="fw-bold">{{ $project->developer_name ?: '-' }}</div>
                                    @if($project->latestRegistration)
                                        <div class="text-secondary small">
                                            Pendaftar: {{ $project->latestRegistration->submittedBy->name }}
                                        </div>
                                        <div class="text-secondary small">{{ $project->latestRegistration->submittedBy->email }}</div>
                                    @endif
                                    
                                    <!-- PIC Info -->
                                    @if($project->pic_name)
                                        <div class="text-info small mt-1">
                                            <i class="ti ti-user-star me-1"></i>
                                            PIC: {{ $project->pic_name }}
                                        </div>
                                    @endif
                                @else
                                    <!-- Internal/CRM project info -->
                                    <div class="text-secondary">
                                        {{ $project->registration_type === 'internal' ? 'Project Internal' : 'Project CRM' }}
                                    </div>
                                    @if($project->registration_type === 'crm' && $project->projectCrm)
                                        <div class="small">{{ $project->projectCrm->nama_project }}</div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-lt">
                                    {{ $project->units_count }}
                                </span>
                                @if($project->units_count > 0)
                                    <div class="text-secondary small">
                                        {{ $project->units->where('is_active', true)->count() }} aktif
                                    </div>
                                @endif
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
                                
                                <!-- PIC Badge untuk manual projects -->
                                @if($project->is_manual_registration && $project->picUser)
                                    <div class="mt-1">
                                        <span class="badge badge-sm bg-info-lt">
                                            <i class="ti ti-star me-1"></i>PIC
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-lt">{{ $project->affiliatorProjects()->count() }}</span>
                                @if($project->affiliatorProjects()->count() > 0)
                                    <div class="text-secondary small">
                                        {{ $project->affiliatorProjects()->where('status', 'active')->count() }} aktif
                                    </div>
                                @endif
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
                                
                                <!-- Registration date untuk manual projects -->
                                @if($project->is_manual_registration && $project->latestRegistration)
                                    <div class="text-info small">
                                        Reg: {{ $project->latestRegistration->created_at->format('d/m/Y') }}
                                    </div>
                                @endif
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
                                        
                                        <!-- Manual Project Actions -->
                                        @if($project->is_manual_registration)
                                            @if($project->registration_status === 'pending')
                                                <button type="button" class="dropdown-item text-success" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#approve-modal-{{ $project->id }}">
                                                    <i class="ti ti-check me-2"></i>
                                                    Setujui Registration
                                                </button>
                                                <button type="button" class="dropdown-item text-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#reject-modal-{{ $project->id }}">
                                                    <i class="ti ti-x me-2"></i>
                                                    Tolak Registration
                                                </button>
                                                <div class="dropdown-divider"></div>
                                            @endif
                                            
                                            @if($project->latestRegistration)
                                                <a href="{{ route('superadmin.projects.registration-detail', $project) }}" 
                                                   class="dropdown-item">
                                                    <i class="ti ti-file-text me-2"></i>
                                                    Detail Registration
                                                </a>
                                            @endif
                                        @endif
                                        
                                        <!-- Standard Project Actions -->
                                        @if($project->registration_status === 'approved' || $project->registration_type === 'crm')
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
                                        @endif
                                        
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

<!-- Approve/Reject Modals untuk Manual Registration Projects -->
@foreach($projects->where('is_manual_registration', true)->where('registration_status', 'pending') as $project)
    <!-- Approve Modal -->
    <div class="modal fade" id="approve-modal-{{ $project->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('superadmin.projects.approve-registration', $project) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Setujui Project Registration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">
                            Anda akan menyetujui project <strong>{{ $project->name }}</strong> 
                            yang didaftarkan oleh {{ $project->latestRegistration->submittedBy->name }}.
                        </p>
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            Project akan diaktifkan dan PIC akan mendapat akun admin.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan (opsional)</label>
                            <textarea class="form-control" name="notes" rows="3" 
                                      placeholder="Tambahkan catatan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-check me-1"></i>
                            Setujui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="reject-modal-{{ $project->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('superadmin.projects.reject-registration', $project) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tolak Project Registration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">
                            Anda akan menolak project <strong>{{ $project->name }}</strong> 
                            yang didaftarkan oleh {{ $project->latestRegistration->submittedBy->name }}.
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="reason" rows="3" required
                                      placeholder="Jelaskan alasan penolakan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="ti ti-x me-1"></i>
                            Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@include('components.delete-modal')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const projectCheckboxes = document.querySelectorAll('.project-checkbox');
    const bulkApproveBtn = document.getElementById('bulk-approve-btn');
    const selectedCountSpan = document.getElementById('selected-count');

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.project-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count > 0) {
            bulkApproveBtn.style.display = 'inline-block';
            selectedCountSpan.textContent = count;
            
            // Update form data
            const form = bulkApproveBtn.closest('form');
            const existingInputs = form.querySelectorAll('input[name="project_ids[]"]');
            existingInputs.forEach(input => input.remove());
            
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'project_ids[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });
        } else {
            bulkApproveBtn.style.display = 'none';
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            projectCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }

    projectCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush