@extends('layouts.main')

@section('title', 'Edit Affiliator - ' . $affiliator->user->name)

@section('content')

@include('components.alert')

<form action="{{ route('admin.affiliators.update', $affiliator) }}" method="POST" id="edit-affiliator-form">
    @csrf
    @method('PUT')
    
    <div class="row g-3">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Affiliator Header -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar avatar-lg">{{ $affiliator->user->initials }}</span>
                        </div>
                        <div class="col">
                            <h1 class="mb-1">Edit Affiliator</h1>
                            <div class="text-secondary">{{ $affiliator->project->name }}</div>
                        </div>
                        <div class="col-auto">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.affiliators.index') }}">Affiliator</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.affiliators.show', $affiliator) }}">{{ $affiliator->user->name }}</a></li>
                                    <li class="breadcrumb-item active">Edit</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Affiliator</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', $affiliator->user->name) }}" 
                                       placeholder="Masukkan nama lengkap">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email', $affiliator->user->email) }}" 
                                       placeholder="user@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                        placeholder="08123456789" value="{{ old('phone', $affiliator->user->phone_number) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status Verifikasi <span class="text-danger">*</span></label>
                                <select class="form-select @error('verification_status') is-invalid @enderror" 
                                        name="verification_status">
                                    <option value="pending" {{ old('verification_status', $affiliator->verification_status) == 'pending' ? 'selected' : '' }}>
                                        Pending
                                    </option>
                                    <option value="verified" {{ old('verification_status', $affiliator->verification_status) == 'verified' ? 'selected' : '' }}>
                                        Verified
                                    </option>
                                    <option value="rejected" {{ old('verification_status', $affiliator->verification_status) == 'rejected' ? 'selected' : '' }}>
                                        Rejected
                                    </option>
                                </select>
                                @error('verification_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status Affiliator <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        name="status">
                                    <option value="incomplete" {{ old('status', $affiliator->status) == 'incomplete' ? 'selected' : '' }}>
                                        Incomplete
                                    </option>
                                    <option value="pending_verification" {{ old('status', $affiliator->status) == 'pending_verification' ? 'selected' : '' }}>
                                        Pending Verification
                                    </option>
                                    <option value="active" {{ old('status', $affiliator->status) == 'active' ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="active" {{ old('status', $affiliator->status) == 'inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                    <option value="suspended" {{ old('status', $affiliator->status) == 'suspended' ? 'selected' : '' }}>
                                        Suspended
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Verifikasi</label>
                        <textarea class="form-control @error('verification_notes') is-invalid @enderror" 
                                  name="verification_notes" rows="4" 
                                  placeholder="Masukkan catatan verifikasi...">{{ old('verification_notes', $affiliator->verification_notes) }}</textarea>
                        @error('verification_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">
                            Catatan ini akan dikirim ke affiliator jika status verifikasi diubah.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Current Status -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Status Saat Ini</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status User</label>
                        <div>
                            <span class="badge bg-{{ $affiliator->user->is_active ? 'success' : 'danger' }}-lt">
                                {{ $affiliator->user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
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

                    <div class="mb-3">
                        <label class="form-label">Status Affiliator</label>
                        <div>
                            <span class="badge bg-{{ $affiliator->status == 'active' ? 'success' : ($affiliator->status == 'suspended' ? 'danger' : 'secondary') }}-lt">
                                {{ $affiliator->status_label }}
                            </span>
                        </div>
                    </div>

                    @if($affiliator->verified_at)
                    <div class="mb-3">
                        <label class="form-label">Diverifikasi</label>
                        <div>{{ $affiliator->verified_at->format('d M Y H:i') }}</div>
                        @if($affiliator->verifiedBy)
                            <div class="text-secondary small">oleh {{ $affiliator->verifiedBy->name }}</div>
                        @endif
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Bergabung</label>
                        <div>{{ $affiliator->created_at->format('d M Y H:i') }}</div>
                        <div class="text-secondary small">{{ $affiliator->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            </div>

            <!-- KTP Information -->
            @if($affiliator->ktp_number && $affiliator->ktp_photo)
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Data KTP</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nomor KTP</label>
                        <div class="fw-bold">{{ $affiliator->ktp_number }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Foto KTP</label>
                        <div>
                            <img src="{{ $affiliator->ktp_photo_url }}" alt="KTP" 
                                 class="img-fluid rounded border" style="max-height: 150px;">
                        </div>
                    </div>

                    @if($affiliator->verification_status === 'pending')
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                onclick="showKtpModal()">
                            <i class="ti ti-eye me-1"></i>
                            Lihat Detail KTP
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Statistics -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Statistik</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h3 mb-0">{{ $affiliator->leads()->count() }}</div>
                                <div class="text-secondary small">Total Lead</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h3 mb-0 text-success">{{ $affiliator->leads()->verified()->count() }}</div>
                                <div class="text-secondary small">Verified</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="text-center">
                                @php $totalCommission = $affiliator->leads()->verified()->sum('commission_earned'); @endphp
                                <div class="h4 mb-0 text-blue">Rp {{ number_format($totalCommission, 0, ',', '.') }}</div>
                                <div class="text-secondary small">Total Komisi</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning Info -->
            <div class="card mt-3">
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <i class="ti ti-alert-triangle icon"></i>
                        <div>
                            <strong>Perhatian:</strong><br>
                            Perubahan status verifikasi akan mengirim notifikasi ke affiliator dan dapat mempengaruhi kemampuan mereka untuk menambah lead.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="col-12">
            <div class="card">
                <div class="card-footer text-end">
                    <div class="d-flex">
                        <a href="{{ route('admin.affiliators.show', $affiliator) }}" class="btn btn-link">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary ms-auto" id="submit-btn">
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- KTP Detail Modal -->
@if($affiliator->ktp_number && $affiliator->ktp_photo)
<div class="modal modal-blur fade" id="ktp-detail-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail KTP - {{ $affiliator->user->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Foto KTP</h6>
                        <img src="{{ $affiliator->ktp_photo_url }}" alt="KTP" class="img-fluid rounded border">
                    </div>
                    <div class="col-md-6">
                        <h6>Informasi</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td>Nama:</td>
                                <td><strong>{{ $affiliator->user->name }}</strong></td>
                            </tr>
                            <tr>
                                <td>No. KTP:</td>
                                <td><strong>{{ $affiliator->ktp_number }}</strong></td>
                            </tr>
                            <tr>
                                <td>Status:</td>
                                <td>
                                    <span class="badge bg-{{ $affiliator->verification_status == 'verified' ? 'success' : ($affiliator->verification_status == 'rejected' ? 'danger' : 'warning') }}-lt">
                                        {{ ucfirst($affiliator->verification_status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                        
                        @if($affiliator->verification_notes)
                        <div class="mb-3">
                            <h6>Catatan Saat Ini</h6>
                            <div class="text-secondary">{{ $affiliator->verification_notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('edit-affiliator-form');
    const submitBtn = document.getElementById('submit-btn');
    
    // Form submission handling
    form.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    });

    // Phone number formatting
    const phoneInput = document.querySelector('input[name="phone"]');
    phoneInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Status change warning
    const verificationStatusSelect = document.querySelector('select[name="verification_status"]');
    const statusSelect = document.querySelector('select[name="status"]');
    const originalVerificationStatus = '{{ $affiliator->verification_status }}';
    const originalStatus = '{{ $affiliator->status }}';

    function checkStatusChange() {
        const newVerificationStatus = verificationStatusSelect.value;
        const newStatus = statusSelect.value;
        
        if (newVerificationStatus !== originalVerificationStatus || newStatus !== originalStatus) {
            showStatusChangeWarning();
        }
    }

    verificationStatusSelect.addEventListener('change', checkStatusChange);
    statusSelect.addEventListener('change', checkStatusChange);

    function showStatusChangeWarning() {
        // You can add a visual indicator here
        console.log('Status will be changed - notification will be sent');
    }
});

@if($affiliator->ktp_number && $affiliator->ktp_photo)
function showKtpModal() {
    const modal = new bootstrap.Modal(document.getElementById('ktp-detail-modal'));
    modal.show();
}
@endif
</script>
@endpush