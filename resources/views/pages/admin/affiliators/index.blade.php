@extends('layouts.main')

@section('title', 'Kelola Affiliator')

@section('content')

@include('components.alert')
@include('components.toast')

<!-- Stats Cards -->
<div class="row g-2 mb-3">
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total</div>
                </div>
                <div class="h1 mb-0">{{ number_format($stats['total']) }}</div>
                <div class="text-secondary">Affiliator</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Pending</div>
                </div>
                <div class="h1 mb-0 text-warning">{{ number_format($stats['pending']) }}</div>
                <div class="text-secondary">Verifikasi</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Verified</div>
                </div>
                <div class="h1 mb-0 text-success">{{ number_format($stats['verified']) }}</div>
                <div class="text-secondary">Affiliator</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Active</div>
                </div>
                <div class="h1 mb-0 text-blue">{{ number_format($stats['active']) }}</div>
                <div class="text-secondary">Affiliator</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Suspended</div>
                </div>
                <div class="h1 mb-0 text-danger">{{ number_format($stats['suspended']) }}</div>
                <div class="text-secondary">Affiliator</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Quick Action</div>
                </div>
                <div class="h1 mb-0">
                    @if($stats['pending'] > 0)
                        <a href="?verification_status=pending" class="text-warning text-decoration-none">
                            <i class="ti ti-alert-triangle"></i>
                        </a>
                    @else
                        <i class="ti ti-check text-success"></i>
                    @endif
                </div>
                <div class="text-secondary">
                    @if($stats['pending'] > 0)
                        Perlu Verifikasi
                    @else
                        Semua Terverifikasi
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Daftar Affiliator</h3>
            <a href="{{ route('admin.affiliators.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
                <i class="ti ti-download me-1"></i>
                Export CSV
            </a>
            {{-- <div class="btn-group">
                <a href="{{ route('admin.affiliators.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
                    <i class="ti ti-download me-1"></i>
                    Export CSV
                </a>
                <div class="text-secondary ms-3">
                    <small>{{ $affiliators->total() }} affiliator dari {{ $projects->count() }} project</small>
                </div>
            </div> --}}
        </div>

        <!-- Filters -->
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Cari affiliator atau project..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="project_id">
                        <option value="">Semua Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="verification_status">
                        <option value="">Semua Status Verifikasi</option>
                        <option value="pending" {{ request('verification_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="verified" {{ request('verification_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="rejected" {{ request('verification_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>Incomplete</option>
                        <option value="pending_verification" {{ request('status') == 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
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
                            <th>Project</th>
                            <th>Status Verifikasi</th>
                            <th>Status</th>
                            <th>Lead</th>
                            <th>Komisi</th>
                            <th>Bergabung</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($affiliators as $affiliator)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm me-2">
                                        {{ $affiliator->user->initials }}
                                    </span>
                                    <div>
                                        <div class="fw-bold">{{ $affiliator->user->name }} {!! $affiliator->user->is_active ? '' : '<i class="ti ti-user-off text-danger"></i>' !!}</div>
                                        <div class="text-secondary small">{{ $affiliator->user->email }}</div>
                                        <div class="text-secondary small">{{ $affiliator->user->country_code }} {{ $affiliator->user->phone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-bold">{{ $affiliator->project->name }}</div>
                                    @if($affiliator->project->location)
                                        <div class="text-secondary small">{{ $affiliator->project->location }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $affiliator->verification_status == 'verified' ? 'success' : ($affiliator->verification_status == 'rejected' ? 'danger' : 'warning') }}-lt">
                                    {{ ucfirst($affiliator->verification_status) }}
                                </span>
                                @if($affiliator->verified_at)
                                    <div class="text-secondary small">{{ $affiliator->verified_at->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td>
                                @if(!$affiliator->user->is_active)
                                    <span class="badge bg-danger-lt small">User Nonaktif</span>
                                @else
                                    <span class="badge bg-{{ $affiliator->status == 'active' ? 'success' : ($affiliator->status == 'suspended' ? 'danger' : 'secondary') }}-lt">
                                        {{ $affiliator->status_label }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $totalLeads = $affiliator->leads()->count();
                                    $verifiedLeads = $affiliator->leads()->verified()->count();
                                @endphp
                                <div>
                                    <span class="badge bg-success-lt">{{ $verifiedLeads }}</span>
                                    /
                                    <span class="badge bg-secondary-lt">{{ $totalLeads }}</span>
                                </div>
                                <div class="text-secondary small">Verified/Total</div>
                            </td>
                            <td>
                                @php
                                    $totalCommission = $affiliator->leads()->verified()->sum('commission_earned');
                                @endphp
                                @if($totalCommission > 0)
                                    <div class="fw-bold text-success">Rp {{ number_format($totalCommission, 0, ',', '.') }}</div>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">{{ $affiliator->created_at->format('d/m/Y') }}</div>
                                <div class="text-secondary small">{{ $affiliator->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-icon bg-light" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="{{ route('admin.affiliators.show', $affiliator) }}" class="dropdown-item">
                                            <i class="ti ti-eye me-2"></i>
                                            Lihat Detail
                                        </a>
                                        <a href="{{ route('admin.affiliators.edit', $affiliator) }}" class="dropdown-item">
                                            <i class="ti ti-edit me-2"></i>
                                            Edit Data
                                        </a>
                                        
                                        @if($affiliator->ktp_number && $affiliator->ktp_photo && $affiliator->verification_status != 'verified')
                                            <button type="button" class="dropdown-item" 
                                                    onclick="showKtpVerificationModal({{ $affiliator->id }}, '{{ $affiliator->user->name }}', '{{ $affiliator->ktp_photo_url }}', '{{ $affiliator->ktp_number }}')">
                                                <i class="ti ti-id me-2"></i>
                                                Verifikasi KTP
                                            </button>
                                        @endif
                                        
                                        <button type="button" class="dropdown-item" 
                                                onclick="resetPassword({{ $affiliator->id }}, '{{ $affiliator->user->name }}')">
                                            <i class="ti ti-key me-2"></i>
                                            Reset Password
                                        </button>
                                        
                                        <div class="dropdown-divider"></div>
                                        
                                        <button type="button" class="dropdown-item {{ $affiliator->user->is_active ? 'text-danger' : 'text-success' }}" 
                                                onclick="toggleStatus({{ $affiliator->id }}, '{{ $affiliator->user->name }}', {{ $affiliator->user->is_active ? 'false' : 'true' }})">
                                            <i class="ti ti-{{ $affiliator->user->is_active ? 'user-off' : 'user-check' }} me-2"></i>
                                            {{ $affiliator->user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} User
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
                    Menampilkan {{ $affiliators->firstItem() ?? 0 }} hingga {{ $affiliators->lastItem() ?? 0 }} 
                    dari {{ $affiliators->total() }} affiliator
                </p>
                @include('components.pagination', ['paginator' => $affiliators])
            </div>
            @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="ti ti-users-off icon icon-xl text-secondary"></i>
                </div>
                <h3 class="text-secondary">Belum ada affiliator</h3>
                <p class="text-secondary">Belum ada affiliator yang terdaftar di project yang Anda kelola.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- KTP Verification Modal -->
{{-- <div class="modal modal-blur fade" id="ktp-verification-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verifikasi KTP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Foto KTP</h6>
                        <img id="ktp-image" src="" alt="KTP" class="img-fluid rounded border">
                    </div>
                    <div class="col-md-6">
                        <h6>Informasi</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td>Nama:</td>
                                <td><strong id="affiliator-name"></strong></td>
                            </tr>
                            <tr>
                                <td>No. KTP:</td>
                                <td><strong id="ktp-number"></strong></td>
                            </tr>
                        </table>
                        
                        <div class="mb-3">
                            <label class="form-label">Catatan Verifikasi</label>
                            <textarea class="form-control" id="verification-notes" rows="3" 
                                      placeholder="Masukkan catatan verifikasi (opsional)..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="verifyKtp('reject')">
                    <i class="ti ti-x me-1"></i>
                    Tolak
                </button>
                <button type="button" class="btn btn-success" onclick="verifyKtp('verify')">
                    <i class="ti ti-check me-1"></i>
                    Verifikasi
                </button>
            </div>
        </div>
    </div>
</div> --}}

<div class="modal modal-blur fade" id="ktp-verification-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verifikasi Data Affiliator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Foto KTP</label>
                        <img id="ktp-image" src="" alt="KTP" class="img-fluid rounded border">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Informasi</label>
                        <table class="table table-borderless">
                            <tr>
                                <td>Nama:</td>
                                <td><strong id="affiliator-name"></strong></td>
                            </tr>
                            <tr>
                                <td>No. KTP:</td>
                                <td><strong id="ktp-number"></strong></td>
                            </tr>
                            <tr>
                                <td>Status Syarat & Ketentuan:</td>
                                <td>
                                    <span id="terms-status" class="badge"></span>
                                    <div id="terms-date" class="text-secondary small"></div>
                                </td>
                            </tr>
                        </table>
                        
                        {{-- Tampilkan Tanda Tangan Digital jika wajib --}}
                        <div id="digital-signature-section" style="display: none;">
                            <label class="form-label">Tanda Tangan Digital</label>
                            <div id="signature-container" class="border rounded p-3 mb-3" style="background: #f8f9fa;">
                                <div id="signature-display" class="text-center"></div>
                                <div id="signature-date" class="text-secondary small mt-2"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Catatan Verifikasi</label>
                            <textarea class="form-control" id="verification-notes" rows="3" 
                                      placeholder="Masukkan catatan verifikasi (opsional)..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="verifyKtp('reject')">
                    <i class="ti ti-x me-1"></i>
                    Tolak
                </button>
                <button type="button" class="btn btn-success" onclick="verifyKtp('verify')">
                    <i class="ti ti-check me-1"></i>
                    Verifikasi
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentAffiliatorId = null;
let currentAffiliatorData = null;

function showKtpVerificationModal(affiliatorId, name, ktpPhotoUrl, ktpNumber) {
    currentAffiliatorId = affiliatorId;
    
    // Load affiliator data via AJAX untuk mendapatkan data lengkap
    fetch(`{{ route('admin.affiliators.show', ':id') }}`.replace(':id', affiliatorId) + '/data', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        currentAffiliatorData = data.affiliatorProject;
        
        // Set basic info
        document.getElementById('affiliator-name').textContent = name;
        document.getElementById('ktp-number').textContent = ktpNumber;
        document.getElementById('ktp-image').src = ktpPhotoUrl;
        document.getElementById('verification-notes').value = '';
        
        // Set terms status
        const termsStatus = document.getElementById('terms-status');
        const termsDate = document.getElementById('terms-date');
        
        if (data.affiliatorProject.terms_accepted) {
            termsStatus.className = 'badge bg-success-lt';
            termsStatus.textContent = 'Disetujui';
            if (data.affiliatorProject.terms_accepted_at) {
                termsDate.textContent = 'Pada ' + new Date(data.affiliatorProject.terms_accepted_at).toLocaleDateString('id-ID');
            }
        } else {
            termsStatus.className = 'badge bg-danger-lt';
            termsStatus.textContent = 'Belum Disetujui';
            termsDate.textContent = '';
        }

        // Show digital signature if required and exists
        const signatureSection = document.getElementById('digital-signature-section');
        if (currentAffiliatorData.project.require_digital_signature) {
            signatureSection.style.display = 'block';
            
            const signatureDisplay = document.getElementById('signature-display');
            const signatureDate = document.getElementById('signature-date');
            
            if (data.affiliatorProject.digital_signature) {

                signatureDisplay.innerHTML = currentAffiliatorData.digital_signature;

                if (data.affiliatorProject.digital_signature_at) {
                    signatureDate.textContent = 'Ditandatangani pada ' + 
                        new Date(data.affiliatorProject.digital_signature_at).toLocaleDateString('id-ID', {
                            year: 'numeric', month: 'long', day: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        });
                }
            }
        } else {
            signatureSection.style.display = 'none';
        }
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('ktp-verification-modal'));
        modal.show();
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Gagal memuat data affiliator', 'error');
    });
}

function verifyKtp(action) {
    if (!currentAffiliatorId) return;
    
    const notes = document.getElementById('verification-notes').value;
    
    if (action === 'reject' && !notes.trim()) {
        alert('Catatan wajib diisi untuk penolakan verifikasi');
        return;
    }
    
    // Check completion requirements
    if (action === 'verify' && currentAffiliatorData) {
        const missingRequirements = [];
        
        // Check terms acceptance
        if (!currentAffiliatorData.terms_accepted) {
            missingRequirements.push('Persetujuan Syarat & Ketentuan');
        }
        
        // Check digital signature if required
        if (currentAffiliatorData.project && currentAffiliatorData.project.require_digital_signature && !currentAffiliatorData.digital_signature) {
            missingRequirements.push('Tanda Tangan Digital');
        }
        
        if (missingRequirements.length > 0) {
            alert('Tidak dapat memverifikasi karena affiliator belum melengkapi:\n- ' + missingRequirements.join('\n- ') + 
                  '\n\nSilakan minta affiliator untuk melengkapi data terlebih dahulu.');
            return;
        }
    }
    
    const confirmMessage = action === 'verify' 
        ? 'Apakah Anda yakin ingin memverifikasi affiliator ini?' 
        : 'Apakah Anda yakin ingin menolak verifikasi affiliator ini?';
    
    if (!confirm(confirmMessage)) return;
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('ktp-verification-modal'));
    
    fetch(`{{ route('admin.affiliators.verify-ktp', ':id') }}`.replace(':id', currentAffiliatorId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            action: action,
            verification_notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            modal.hide();
            showToast(data.message, 'success');
            
            location.reload();
        } else {
            showToast(data.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan jaringan', 'error');
    });
}

function resetPassword(affiliatorId, name) {
    if (!confirm(`Apakah Anda yakin ingin mereset password untuk ${name}?`)) return;
    
    fetch(`{{ route('admin.affiliators.reset-password', ':id') }}`.replace(':id', affiliatorId), {
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
            
            // Show password in alert
            alert(`Password baru untuk ${name}: ${data.password}\n\nPassword telah dikirim ke affiliator melalui notifikasi.`);
        } else {
            showToast(data.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan jaringan', 'error');
    });
}

function toggleStatus(affiliatorId, name, newStatus) {
    const action = newStatus === 'true' ? 'mengaktifkan' : 'menonaktifkan';
    
    if (!confirm(`Apakah Anda yakin ingin ${action} user ${name}?`)) return;
    
    fetch(`{{ route('admin.affiliators.toggle-status', ':id') }}`.replace(':id', affiliatorId), {
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
            location.reload();
        } else {
            showToast(data.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan jaringan', 'error');
    });
}
</script>
@endpush