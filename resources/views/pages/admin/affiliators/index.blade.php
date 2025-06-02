@extends('layouts.main')

@section('title', 'Kelola Affiliator')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Daftar Affiliator</h3>
                <div class="btn-list">
                    <button type="button" class="btn btn-outline-secondary" id="export-btn">
                        <i class="ti ti-download me-1"></i>
                        Export Data
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <form method="GET" class="row g-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Cari affiliator..." 
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
                        <select class="form-select" name="verification">
                            <option value="">Status Verifikasi</option>
                            <option value="pending" {{ request('verification') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ request('verification') == 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                            <option value="rejected" {{ request('verification') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="project">
                            <option value="">Semua Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="ti ti-search me-1"></i>
                                Filter
                            </button>
                            <a href="{{ route('admin.affiliators.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                @if($affiliators->count() > 0)
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>Affiliator</th>
                                <th>Contact</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Verifikasi</th>
                                <th>Lead</th>
                                <th>Komisi</th>
                                <th>Bergabung</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($affiliators as $affiliator)
                            @php
                                // Get first affiliator project for this admin's projects
                                $affiliatorProject = $affiliator->affiliatorProjects->first();
                                
                                // Count leads using relationship
                                $totalLeads = $affiliator->leads ? $affiliator->leads->count() : 0;
                                $verifiedLeads = $affiliator->leads ? $affiliator->leads->where('verification_status', 'verified')->count() : 0;
                                
                                // Get commission data
                                $totalCommission = $affiliator->commissions ? $affiliator->commissions->where('type', 'earned')->sum('amount') : 0;
                                $withdrawnCommission = $affiliator->withdrawals ? $affiliator->withdrawals->where('status', 'processed')->sum('amount') : 0;
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $affiliator->profile_photo_url }}" alt="{{ $affiliator->name }}" 
                                             class="avatar avatar-sm me-2">
                                        <div>
                                            <div class="fw-bold">{{ $affiliator->name }}</div>
                                            <div class="text-secondary small">{{ $affiliator->initials }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-bold">{{ $affiliator->email }}</div>
                                        <div class="text-secondary small">
                                            {{ $affiliator->country_code }} {{ $affiliator->phone }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($affiliatorProject)
                                        <div class="d-flex align-items-center">
                                            @if($affiliatorProject->project->logo)
                                                <img src="{{ $affiliatorProject->project->logo_url }}" 
                                                     alt="{{ $affiliatorProject->project->name }}" 
                                                     class="avatar avatar-xs me-1">
                                            @else
                                                <div class="avatar avatar-xs bg-primary-lt me-1">
                                                    {{ substr($affiliatorProject->project->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <span class="text-truncate" style="max-width: 120px;">
                                                {{ $affiliatorProject->project->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $affiliator->is_active ? 'success' : 'secondary' }}-lt">
                                        {{ $affiliator->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td>
                                    @if($affiliatorProject)
                                        @php
                                            $verificationColor = match($affiliatorProject->verification_status) {
                                                'verified' => 'success',
                                                'rejected' => 'danger',
                                                default => 'warning'
                                            };
                                            $verificationLabel = match($affiliatorProject->verification_status) {
                                                'verified' => 'Terverifikasi',
                                                'rejected' => 'Ditolak',
                                                default => 'Pending'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $verificationColor }}-lt">
                                            {{ $verificationLabel }}
                                        </span>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <span class="badge bg-success-lt">{{ $verifiedLeads }}</span>
                                        /
                                        <span class="badge bg-secondary-lt">{{ $totalLeads }}</span>
                                    </div>
                                    <div class="text-secondary small">Verified/Total</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-success">Rp {{ number_format($totalCommission, 0, ',', '.') }}</div>
                                    <div class="text-secondary small">Ditarik: Rp {{ number_format($withdrawnCommission, 0, ',', '.') }}</div>
                                </td>
                                <td>
                                    <div class="small">{{ $affiliator->created_at->format('d/m/Y') }}</div>
                                    <div class="text-secondary small">{{ $affiliator->created_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-icon bg-light" 
                                                data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a href="{{ route('admin.affiliators.show', $affiliator) }}" 
                                               class="dropdown-item">
                                                <i class="ti ti-eye me-2"></i>
                                                Lihat Detail
                                            </a>
                                            <a href="{{ route('admin.affiliators.edit', $affiliator) }}" 
                                               class="dropdown-item">
                                                <i class="ti ti-edit me-2"></i>
                                                Edit Profile
                                            </a>
                                            @if($affiliatorProject && $affiliatorProject->verification_status == 'pending')
                                            <button type="button" class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#verification-modal"
                                                    data-affiliator-id="{{ $affiliator->id }}"
                                                    data-affiliator-name="{{ $affiliator->name }}"
                                                    data-project-id="{{ $affiliatorProject->project_id }}">
                                                <i class="ti ti-check me-2"></i>
                                                Verifikasi
                                            </button>
                                            @endif
                                            <button type="button" class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#reset-password-modal"
                                                    data-affiliator-id="{{ $affiliator->id }}"
                                                    data-affiliator-name="{{ $affiliator->name }}">
                                                <i class="ti ti-key me-2"></i>
                                                Reset Password
                                            </button>
                                            <form action="{{ route('admin.affiliators.toggle-status', $affiliator) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item">
                                                    <i class="ti ti-{{ $affiliator->is_active ? 'eye-off' : 'eye' }} me-2"></i>
                                                    {{ $affiliator->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
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
                        Menampilkan {{ $affiliators->firstItem() ?? 0 }} hingga {{ $affiliators->lastItem() ?? 0 }} 
                        dari {{ $affiliators->total() }} affiliator
                    </p>
                    {{-- {{ $affiliators->withQueryString()->links() }} --}}
                    @include('components.pagination', ['paginator' => $affiliators])
                </div>
                @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="ti ti-users-off icon icon-xl text-secondary"></i>
                    </div>
                    <h3 class="text-secondary">Belum ada affiliator</h3>
                    <p class="text-secondary">Project Anda belum memiliki affiliator yang bergabung.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Verification Modal -->
<div class="modal fade" id="verification-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="verification-form" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Verifikasi Affiliator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status Verifikasi <span class="text-danger">*</span></label>
                        <select class="form-select" name="verification_status" required>
                            <option value="">Pilih Status</option>
                            <option value="verified">Terverifikasi</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Verifikasi</label>
                        <textarea class="form-control" name="verification_notes" rows="3"
                                  placeholder="Berikan catatan untuk affiliator (opsional)"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Affiliator akan menerima notifikasi tentang status verifikasi ini.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Verifikasi</button>
                </div>
            </form>
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
                        Affiliator akan menerima notifikasi bahwa password mereka telah direset.
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

@endsection

@push('scripts')
@include('components.alert')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verification modal handler
    const verificationModal = document.getElementById('verification-modal');
    const verificationForm = document.getElementById('verification-form');
    
    verificationModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const affiliatorId = button.getAttribute('data-affiliator-id');
        const affiliatorName = button.getAttribute('data-affiliator-name');
        const projectId = button.getAttribute('data-project-id');
        
        // Update form action
        verificationForm.action = `{{ route('admin.affiliators.verify', ':id') }}`;
        verificationForm.action = verificationForm.action.replace(':id', affiliatorId);
        
        // Add hidden project ID input
        let projectInput = verificationForm.querySelector('input[name="project_id"]');
        if (!projectInput) {
            projectInput = document.createElement('input');
            projectInput.type = 'hidden';
            projectInput.name = 'project_id';
            verificationForm.appendChild(projectInput);
        }
        projectInput.value = projectId;
        
        // Update modal title
        verificationModal.querySelector('.modal-title').textContent = `Verifikasi - ${affiliatorName}`;
        
        // Clear form
        verificationForm.querySelector('select[name="verification_status"]').value = '';
        verificationForm.querySelector('textarea[name="verification_notes"]').value = '';
    });
    
    // Reset password modal handler
    const resetPasswordModal = document.getElementById('reset-password-modal');
    const resetPasswordForm = document.getElementById('reset-password-form');
    
    resetPasswordModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const affiliatorId = button.getAttribute('data-affiliator-id');
        const affiliatorName = button.getAttribute('data-affiliator-name');
        
        // Update form action
        resetPasswordForm.action = `{{ route('admin.affiliators.reset-password', ':id') }}`;
        resetPasswordForm.action = resetPasswordForm.action.replace(':id', affiliatorId);
        
        // Update modal title
        resetPasswordModal.querySelector('.modal-title').textContent = `Reset Password - ${affiliatorName}`;
        
        // Clear form
        resetPasswordForm.reset();
    });
    
    // Export functionality
    const exportBtn = document.getElementById('export-btn');
    exportBtn.addEventListener('click', function() {
        const currentUrl = new URL(window.location);
        currentUrl.pathname = '{{ route('admin.affiliators.export') }}';
        window.open(currentUrl.toString(), '_blank');
    });
});
</script>
@endpush