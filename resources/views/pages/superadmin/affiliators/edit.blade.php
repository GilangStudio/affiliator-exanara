@extends('layouts.main')

@section('title', 'Edit Affiliator - ' . $affiliator->name)

@push('styles')

@endpush

@section('content')
{{-- Alert Messages --}}
@if(session('error'))
<div class="alert alert-danger alert-dismissible" role="alert">
    <div class="d-flex">
        <div>
            <i class="ti ti-exclamation-circle me-2"></i>
        </div>
        <div>{{ session('error') }}</div>
    </div>
    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
</div>
@endif

<form action="{{ route('superadmin.affiliators.update', $affiliator) }}" id="edit-affiliator-form" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row g-3">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-user me-2"></i>Informasi Affiliator</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', $affiliator->name) }}" required
                                       placeholder="Masukkan nama lengkap">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">
                                    <span id="name-count">{{ strlen($affiliator->name) }}</span>/255 karakter
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email', $affiliator->email) }}" required
                                       placeholder="affiliator@example.com">
                                @error('email')
                                    <small class="text-danger">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Email akan digunakan untuk login</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor Whatsapp <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">+62</span>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   placeholder="8123456789" value="{{ old('phone', $affiliator->phone) }}" required>
                        </div>
                        @error('phone')
                            <small class="text-danger">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Nomor tanpa kode negara dan tanpa 0 di depan (contoh: 8123456789)</small>
                        <input type="hidden" name="country_code" value="+62">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <div class="input-group input-group-flat">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           name="password" minlength="8" id="passwordInput"
                                           placeholder="Kosongkan jika tidak ingin mengubah" autocomplete="off">
                                    <span class="input-group-text">
                                        <a href="#" class="link-secondary" id="togglePassword" title="Tampilkan password">
                                            <i class="ti ti-eye icon icon-1"></i>
                                        </a>
                                    </span>
                                </div>
                                @error('password')
                                    <small class="text-danger">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Minimal 8 karakter. Kosongkan jika tidak ingin mengubah.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password</label>
                                <div class="input-group input-group-flat">
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                           name="password_confirmation" id="passwordConfirmationInput"
                                           placeholder="Ulangi password baru" autocomplete="off">
                                    <span class="input-group-text">
                                        <a href="#" class="link-secondary" id="togglePasswordConfirmation" title="Tampilkan password">
                                            <i class="ti ti-eye icon icon-1"></i>
                                        </a>
                                    </span>
                                </div>
                                @error('password_confirmation')
                                    <small class="text-danger">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Harus sama dengan password baru</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Profile Photo -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-photo me-2"></i>Foto Profil</h3>
                </div>
                <div class="card-body">
                    @if($affiliator->profile_photo)
                        <div class="mb-3 text-center">
                            <img src="{{ $affiliator->profile_photo_url }}" alt="Current Photo" 
                                 class="avatar avatar-xl mb-2">
                            <div class="text-secondary small">Foto saat ini</div>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label class="form-label">Upload Foto Baru</label>
                        <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" 
                               name="profile_photo" accept="image/*" id="photo-input">
                        @error('profile_photo')
                            <small class="text-danger">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">
                            <i class="ti ti-info-circle me-1"></i>
                            Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah.
                        </small>
                        <div class="mt-2" id="photo-preview"></div>
                    </div>
                </div>
            </div>

            <!-- Account Settings -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-settings me-2"></i>Pengaturan Akun</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" 
                                   value="1" {{ old('is_active', $affiliator->is_active) ? 'checked' : '' }}>
                            <span class="form-check-label">Akun Aktif</span>
                        </label>
                        <small class="form-hint">Centang untuk mengaktifkan akun</small>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h3 mb-0">{{ $affiliator->affiliatorProjects()->count() }}</div>
                                <div class="text-secondary small">Project</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h3 mb-0">
                                    @if($affiliator->last_login_at)
                                        {{ $affiliator->last_login_at->diffForHumans() }}
                                    @else
                                        Belum pernah
                                    @endif
                                </div>
                                <div class="text-secondary small">Last Login</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Joined Projects -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-folder me-2"></i>Project yang Diikuti</h3>
                </div>
                <div class="card-body">
                    @if($affiliator->affiliatorProjects->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($affiliator->affiliatorProjects as $affiliatorProject)
                            <div class="list-group-item d-flex align-items-center px-0">
                                @if($affiliatorProject->project->logo)
                                    <img src="{{ $affiliatorProject->project->logo_url }}" alt="{{ $affiliatorProject->project->name }}" 
                                         class="avatar avatar-sm me-2">
                                @else
                                    <div class="avatar avatar-sm bg-primary-lt me-2">
                                        {{ substr($affiliatorProject->project->name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="flex-fill">
                                    <div class="fw-bold">{{ $affiliatorProject->project->name }}</div>
                                    <div class="text-secondary small">
                                        <span class="badge bg-{{ $affiliatorProject->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ $affiliatorProject->status_label }}
                                        </span>
                                        <span class="badge bg-{{ $affiliatorProject->verification_status == 'verified' ? 'success' : ($affiliatorProject->verification_status == 'rejected' ? 'danger' : 'warning') }} ms-1">
                                            @if($affiliatorProject->verification_status == 'verified')
                                                Terverifikasi
                                            @elseif($affiliatorProject->verification_status == 'rejected')
                                                Ditolak
                                            @else
                                                Pending
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-secondary">
                            <i class="ti ti-folder-off icon icon-lg mb-2"></i>
                            <div>Belum bergabung project</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Account Info -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Info Akun</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <div class="text-secondary small">Dibuat</div>
                            <div>{{ $affiliator->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-12 mb-2">
                            <div class="text-secondary small">Terakhir Diperbarui</div>
                            <div>{{ $affiliator->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                        @if($affiliator->email_verified_at)
                        <div class="col-12">
                            <div class="text-secondary small">Email Terverifikasi</div>
                            <div class="text-success">
                                <i class="ti ti-check me-1"></i>
                                {{ $affiliator->email_verified_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        @else
                        <div class="col-12">
                            <div class="text-secondary small">Email</div>
                            <div class="text-warning">
                                <i class="ti ti-alert-triangle me-1"></i>
                                Belum terverifikasi
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="col-12">
            <div class="card">
                <div class="card-footer text-end">
                    <div class="d-flex">
                        <a href="{{ route('superadmin.affiliators.index') }}" class="btn btn-link">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary ms-auto" id="submit-btn">
                            <i class="ti ti-device-floppy me-1"></i>
                            Perbarui Affiliator
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
@include('components.alert')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Name character counter
    const nameInput = document.querySelector('input[name="name"]');
    const nameCount = document.getElementById('name-count');
    
    nameInput.addEventListener('input', function() {
        const currentLength = this.value.length;
        nameCount.textContent = currentLength;
        
        if (currentLength > 200) {
            nameCount.parentElement.classList.add('text-warning');
        } else if (currentLength > 255) {
            nameCount.parentElement.classList.remove('text-warning');
            nameCount.parentElement.classList.add('text-danger');
        } else {
            nameCount.parentElement.classList.remove('text-warning', 'text-danger');
        }
    });
    
    // Password toggle functionality
    function setupPasswordToggle(inputId, toggleId) {
        const passwordInput = document.getElementById(inputId);
        const togglePassword = document.getElementById(toggleId);
        const eyeIcon = togglePassword.querySelector('i');

        togglePassword.addEventListener('click', function (e) {
            e.preventDefault();
            // Toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle the icon
            if (type === 'password') {
                eyeIcon.classList.remove('ti-eye-off');
                eyeIcon.classList.add('ti-eye');
                togglePassword.setAttribute('title', 'Tampilkan password');
            } else {
                eyeIcon.classList.remove('ti-eye');
                eyeIcon.classList.add('ti-eye-off');
                togglePassword.setAttribute('title', 'Sembunyikan password');
            }
        });
    }

    // Setup password toggles
    setupPasswordToggle('passwordInput', 'togglePassword');
    setupPasswordToggle('passwordConfirmationInput', 'togglePasswordConfirmation');
    
    // Photo preview functionality
    const photoInput = document.getElementById('photo-input');
    const photoPreview = document.getElementById('photo-preview');
    
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            const maxSize = 2 * 1024 * 1024; // 2MB
            if (file.size > maxSize) {
                showAlert(photoInput, 'danger', 'File terlalu besar. Maksimal 2MB.');
                photoInput.value = '';
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                showAlert(photoInput, 'danger', 'Pilih file gambar yang valid.');
                photoInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title h6 mb-1">${file.name}</h5>
                                    <small class="text-secondary">
                                        ${(file.size / 1024 / 1024).toFixed(2)} MB
                                    </small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearPhotoPreview()">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <small class="text-warning">
                                    <i class="ti ti-alert-triangle me-1"></i>
                                    Akan mengganti foto yang ada
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            photoPreview.innerHTML = '';
        }
    });

    // Clear photo preview function
    window.clearPhotoPreview = function() {
        photoInput.value = '';
        photoPreview.innerHTML = '';
    };

    // Password confirmation validation
    const passwordInput = document.getElementById('passwordInput');
    const confirmPasswordInput = document.getElementById('passwordConfirmationInput');
    
    function validatePasswordMatch() {
        if (passwordInput.value || confirmPasswordInput.value) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Password tidak sama');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        } else {
            confirmPasswordInput.setCustomValidity('');
        }
    }
    
    passwordInput.addEventListener('input', validatePasswordMatch);
    confirmPasswordInput.addEventListener('input', validatePasswordMatch);

    // Form submission
    const form = document.getElementById('edit-affiliator-form');
    const submitBtn = document.getElementById('submit-btn');
    
    form.addEventListener('submit', function(e) {
        // Final validation
        if ((passwordInput.value || confirmPasswordInput.value) && passwordInput.value !== confirmPasswordInput.value) {
            e.preventDefault();
            showAlert(confirmPasswordInput, 'danger', 'Password dan konfirmasi password harus sama.');
            return false;
        }
    });
});
</script>
@endpush