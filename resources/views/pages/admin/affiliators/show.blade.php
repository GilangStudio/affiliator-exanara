@extends('layouts.main')

@section('title', 'Detail Affiliator - ' . $affiliator->user->name)

@section('content')

@include('components.alert')
@include('components.toast')

<!-- Affiliator Header -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <span class="avatar avatar-xl">{{ $affiliator->user->initials }}</span>
            </div>
            <div class="col">
                <h1 class="mb-1">{{ $affiliator->user->name }}</h1>
                <div class="text-secondary mb-2">{{ $affiliator->user->email }} • {{ $affiliator->user->phone }}</div>
                <div class="row">
                    <div class="col-auto">
                        <span class="badge bg-{{ $affiliator->verification_status == 'verified' ? 'success' : ($affiliator->verification_status == 'rejected' ? 'danger' : 'warning') }}-lt me-2">
                            {{ ucfirst($affiliator->verification_status) }}
                        </span>
                        <span class="badge bg-{{ $affiliator->status == 'active' ? 'success' : ($affiliator->status == 'suspended' ? 'danger' : 'secondary') }}-lt me-2">
                            {{ $affiliator->status_label }}
                        </span>
                        @if(!$affiliator->user->is_active)
                            <span class="badge bg-danger-lt">User Nonaktif</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.affiliators.index') }}">Affiliator</a></li>
                        <li class="breadcrumb-item active">{{ $affiliator->user->name }}</li>
                    </ol>
                </nav>
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
                    <div class="subheader">Total Lead</div>
                </div>
                <div class="h1 mb-0">{{ number_format($stats['total_leads']) }}</div>
                <div class="text-secondary">Lead</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Verified</div>
                </div>
                <div class="h1 mb-0 text-success">{{ number_format($stats['verified_leads']) }}</div>
                <div class="text-secondary">Lead</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Pending</div>
                </div>
                <div class="h1 mb-0 text-warning">{{ number_format($stats['pending_leads']) }}</div>
                <div class="text-secondary">Lead</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Komisi</div>
                </div>
                <div class="h1 mb-0 text-blue">Rp {{ number_format($stats['total_commission'], 0, ',', '.') }}</div>
                <div class="text-secondary">Diperoleh</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Profile Information -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Profil</h3>
                <div class="card-actions">
                    <a href="{{ route('admin.affiliators.edit', $affiliator) }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-edit me-1"></i>
                        Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td width="120">Nama:</td>
                        <td><strong>{{ $affiliator->user->name }}</strong></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td>{{ $affiliator->user->email }}</td>
                    </tr>
                    <tr>
                        <td>Telepon:</td>
                        <td>{{ $affiliator->user->phone }}</td>
                    </tr>
                    <tr>
                        <td>Status User:</td>
                        <td>
                            <span class="badge bg-{{ $affiliator->user->is_active ? 'success' : 'danger' }}-lt">
                                {{ $affiliator->user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Project:</td>
                        <td>
                            <div class="fw-bold">{{ $affiliator->project->name }}</div>
                            @if($affiliator->project->location)
                                <div class="text-secondary small">{{ $affiliator->project->location }}</div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Bergabung:</td>
                        <td>{{ $affiliator->created_at->format('d M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- KTP Information -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Data KTP</h3>
            </div>
            <div class="card-body">
                @if($affiliator->ktp_number && $affiliator->ktp_photo)
                    <div class="mb-3">
                        <label class="form-label">Nomor KTP</label>
                        <div class="fw-bold">{{ $affiliator->ktp_number }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Foto KTP</label>
                        <div>
                            <img src="{{ $affiliator->ktp_photo_url }}" alt="KTP" 
                                 class="img-fluid rounded border" style="max-height: 200px;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status Verifikasi</label>
                        <div>
                            <span class="badge bg-{{ $affiliator->verification_status == 'verified' ? 'success' : ($affiliator->verification_status == 'rejected' ? 'danger' : 'warning') }}-lt">
                                {{ ucfirst($affiliator->verification_status) }}
                            </span>
                        </div>
                    </div>
                    
                    @if($affiliator->verification_notes)
                    <div class="mb-3">
                        <label class="form-label">Catatan Verifikasi</label>
                        <div class="text-secondary">{{ $affiliator->verification_notes }}</div>
                    </div>
                    @endif
                    
                    @if($affiliator->verified_at)
                    <div class="mb-3">
                        <label class="form-label">Diverifikasi</label>
                        <div>{{ $affiliator->verified_at->format('d M Y H:i') }}</div>
                        @if($affiliator->verifiedBy)
                            <div class="text-secondary small">oleh {{ $affiliator->verifiedBy->name }}</div>
                        @endif
                    </div>
                    @endif

                    @if($affiliator->verification_status === 'pending')
                    <div class="mt-3">
                        <button type="button" class="btn btn-success btn-sm me-2" 
                                onclick="showKtpVerificationModal({{ $affiliator->id }}, '{{ $affiliator->user->name }}', '{{ $affiliator->ktp_photo_url }}', '{{ $affiliator->ktp_number }}')">
                            <i class="ti ti-check me-1"></i>
                            Verifikasi KTP
                        </button>
                    </div>
                    @endif
                @else
                    <div class="text-center text-secondary">
                        <i class="ti ti-id-off icon icon-xl mb-2"></i>
                        <div>Data KTP belum dilengkapi</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Aksi Cepat</h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary" 
                            onclick="resetPassword({{ $affiliator->id }}, '{{ $affiliator->user->name }}')">
                        <i class="ti ti-key me-1"></i>
                        Reset Password
                    </button>
                    
                    <button type="button" class="btn btn-outline-{{ $affiliator->user->is_active ? 'danger' : 'success' }}" 
                            onclick="toggleStatus({{ $affiliator->id }}, '{{ $affiliator->user->name }}', {{ $affiliator->user->is_active ? 'false' : 'true' }})">
                        <i class="ti ti-{{ $affiliator->user->is_active ? 'user-off' : 'user-check' }} me-1"></i>
                        {{ $affiliator->user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} User
                    </button>
                </div>
            </div>
        </div>

        <!-- Activity Logs -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Aktivitas Terbaru</h3>
            </div>
            <div class="card-body p-0">
                @if($recentActivities->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentActivities as $activity)
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="ti ti-{{ $activity->action == 'verify_ktp' ? 'id-badge' : ($activity->action == 'reject_ktp' ? 'id-off' : ($activity->action == 'reset_password' ? 'key' : ($activity->action == 'toggle_affiliator_status' ? 'user-check' : 'activity'))) }} text-{{ $activity->action == 'verify_ktp' ? 'success' : ($activity->action == 'reject_ktp' ? 'danger' : ($activity->action == 'reset_password' ? 'warning' : 'primary')) }}"></i>
                                </div>
                                <div class="col text-truncate">
                                    <div class="text-reset d-block">{{ $activity->description }}</div>
                                    <div class="d-block text-secondary text-truncate mt-n1">
                                        {{ $activity->created_at->format('d M Y H:i') }} 
                                        @if($activity->user && $activity->user_id != $affiliator->user_id)
                                            • oleh {{ $activity->user->name }}
                                        @endif
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-{{ $activity->action == 'verify_ktp' ? 'success' : ($activity->action == 'reject_ktp' ? 'danger' : ($activity->action == 'reset_password' ? 'warning' : 'primary')) }}-lt">
                                        {{ $activity->action_label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="text-secondary">
                            <i class="ti ti-activity icon icon-xl mb-2"></i>
                            <div>Belum ada aktivitas</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Leads Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Daftar Lead</h3>
                <a href="{{ route('admin.projects.leads.index', $affiliator->project) }}?affiliator={{ $affiliator->user_id }}" 
                   class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                @if($affiliator->leads->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Komisi</th>
                                    <th>Tanggal</th>
                                    <th width="80">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($affiliator->leads->take(10) as $lead)
                                <tr>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $lead->customer_name }}</div>
                                            <div class="text-secondary small">{{ $lead->customer_phone }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($lead->unit)
                                            <div class="fw-bold">{{ $lead->unit->name }}</div>
                                            <div class="text-secondary small">{{ $lead->unit->price_formatted }}</div>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
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
                                        <a href="{{ route('admin.projects.leads.show', [$affiliator->project, $lead]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($affiliator->leads->count() > 10)
                        <div class="card-footer text-center">
                            <a href="{{ route('admin.projects.leads.index', $affiliator->project) }}?affiliator={{ $affiliator->user_id }}" 
                               class="text-secondary">
                                Lihat {{ $affiliator->leads->count() - 10 }} lead lainnya
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <div class="text-secondary">
                            <i class="ti ti-file-off icon icon-xl mb-2"></i>
                            <div>Belum ada lead</div>
                            <div class="small mt-1">Affiliator belum menambahkan lead customer</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- KTP Verification Modal -->
<div class="modal modal-blur fade" id="ktp-verification-modal" tabindex="-1" role="dialog" aria-hidden="true">
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
</div>

@endsection

@push('scripts')
<script>
let currentAffiliatorId = {{ $affiliator->id }};

function showKtpVerificationModal(affiliatorId, name, ktpPhotoUrl, ktpNumber) {
    currentAffiliatorId = affiliatorId;
    
    document.getElementById('affiliator-name').textContent = name;
    document.getElementById('ktp-number').textContent = ktpNumber;
    document.getElementById('ktp-image').src = ktpPhotoUrl;
    document.getElementById('verification-notes').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('ktp-verification-modal'));
    modal.show();
}

function verifyKtp(action) {
    if (!currentAffiliatorId) return;
    
    const notes = document.getElementById('verification-notes').value;
    
    if (action === 'reject' && !notes.trim()) {
        alert('Catatan wajib diisi untuk penolakan KTP');
        return;
    }
    
    const confirmMessage = action === 'verify' 
        ? 'Apakah Anda yakin ingin memverifikasi KTP ini?' 
        : 'Apakah Anda yakin ingin menolak KTP ini?';
    
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

function showToast(message, type) {
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
    } else {
        alert(message);
    }
}
</script>
@endpush