@extends('layouts.main')

@section('title', 'Tambah Admin - ' . $project->name)

@push('styles')

@endpush

@section('content')
<!-- Project Header -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                @if($project->logo)
                    <img src="{{ $project->logo_url }}" alt="{{ $project->name }}" 
                            class="avatar avatar-lg">
                @else
                    <div class="avatar avatar-lg bg-primary-lt">
                        {{ substr($project->name, 0, 2) }}
                    </div>
                @endif
            </div>
            <div class="col">
                <h1 class="mb-1">{{ $project->name }}</h1>
                <div class="text-secondary">Tambah Admin Baru</div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.projects.index') }}">Projects</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.projects.show', $project) }}">{{ $project->name }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.projects.admins.index', $project) }}">Admin</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

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

<form action="{{ route('superadmin.projects.admins.store', $project) }}" id="create-admin-form" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="row g-3">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-user me-2"></i>Informasi Admin</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name') }}" required
                                       placeholder="Masukkan nama lengkap">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    <span id="name-count">0</span>/255 karakter
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" required
                                       placeholder="admin@example.com">
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Email akan digunakan untuk login</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror"
                                       name="username" value="{{ old('username') }}" required
                                       placeholder="Masukkan username">
                                @error('username')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    Minimal 6 karakter
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nomor Whatsapp <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">+62</span>
                                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                           placeholder="8123456789" value="{{ old('phone') }}" required>
                                </div>
                                @error('phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Nomor tanpa kode negara dan tanpa 0 di depan (contoh: 8123456789)</small>
                                <input type="hidden" name="country_code" value="+62">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-flat">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           name="password" required minlength="8" id="passwordInput"
                                           placeholder="Minimal 8 karakter" autocomplete="off">
                                    <span class="input-group-text">
                                        <a href="#" class="link-secondary" id="togglePassword" title="Tampilkan password">
                                            <i class="ti ti-eye icon icon-1"></i>
                                        </a>
                                    </span>
                                </div>
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <div class="mt-2">
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" id="password-strength" style="width: 0%"></div>
                                    </div>
                                    <small class="form-hint" id="password-hint">Minimal 8 karakter, kombinasi huruf dan angka</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-flat">
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                           name="password_confirmation" required id="passwordConfirmationInput"
                                           placeholder="Ulangi password Anda" autocomplete="off">
                                    <span class="input-group-text">
                                        <a href="#" class="link-secondary" id="togglePasswordConfirmation" title="Tampilkan password">
                                            <i class="ti ti-eye icon icon-1"></i>
                                        </a>
                                    </span>
                                </div>
                                @error('password_confirmation')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint" id="confirm-password-hint">Harus sama dengan password di atas</small>
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
                    <div class="mb-3">
                        <label class="form-label">Upload Foto</label>
                        <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" 
                               name="profile_photo" accept="image/*" id="photo-input">
                        @error('profile_photo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <small class="form-hint">
                            <i class="ti ti-info-circle me-1"></i>
                            Format: JPG, PNG, GIF. Maksimal 2MB
                        </small>
                        <div class="mt-2" id="photo-preview"></div>
                    </div>
                </div>
            </div>

            <!-- Project Assignment -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-folder me-2"></i>Assignment Project</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
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
                            <div class="text-secondary small">{{ $project->slug }}</div>
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="ti ti-info-circle me-2"></i>
                        <div>
                            <strong>Info:</strong><br>
                            Admin akan otomatis ditugaskan untuk mengelola project ini setelah akun dibuat.
                        </div>
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
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="form-check-label">Akun Aktif</span>
                        </label>
                        <small class="form-hint">Centang untuk mengaktifkan akun setelah dibuat</small>
                    </div>
                </div>
            </div>

            <!-- Role Permissions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-shield me-2"></i>Hak Akses Admin</h3>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-check text-success me-2"></i>
                            <span>Mengelola project {{ $project->name }}</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-check text-success me-2"></i>
                            <span>Verifikasi lead dan KTP affiliator</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-check text-success me-2"></i>
                            <span>Kelola penarikan komisi</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-check text-success me-2"></i>
                            <span>Lihat laporan dan statistik</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-check text-success me-2"></i>
                            <span>Kelola FAQ project</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-x text-danger me-2"></i>
                            <span>Kelola admin lain</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-x text-danger me-2"></i>
                            <span>Pengaturan sistem</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-lock me-2"></i>Keamanan</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <i class="ti ti-alert-triangle me-2"></i>
                        <div>
                            <strong>Penting!</strong><br>
                            Pastikan email dan nomor telepon yang dimasukkan valid. Admin akan menerima informasi login melalui notifikasi sistem.
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
                        <a href="{{ route('superadmin.projects.admins.index', $project) }}" class="btn btn-link">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary ms-auto" id="submit-btn">
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Admin
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
    
    // Password strength indicator
    const passwordInput = document.getElementById('passwordInput');
    const passwordStrength = document.getElementById('password-strength');
    const passwordHint = document.getElementById('password-hint');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        let hints = [];
        
        // Length check
        if (password.length >= 8) strength += 25;
        else hints.push('minimal 8 karakter');
        
        // Uppercase check
        if (/[A-Z]/.test(password)) strength += 25;
        else hints.push('huruf besar');
        
        // Lowercase check
        if (/[a-z]/.test(password)) strength += 25;
        else hints.push('huruf kecil');
        
        // Number check
        if (/[0-9]/.test(password)) strength += 25;
        else hints.push('angka');
        
        // Update progress bar
        passwordStrength.style.width = strength + '%';
        
        if (strength <= 25) {
            passwordStrength.className = 'progress-bar bg-danger';
            passwordHint.textContent = 'Password lemah - tambahkan: ' + hints.join(', ');
        } else if (strength <= 50) {
            passwordStrength.className = 'progress-bar bg-warning';
            passwordHint.textContent = 'Password sedang - tambahkan: ' + hints.join(', ');
        } else if (strength <= 75) {
            passwordStrength.className = 'progress-bar bg-info';
            passwordHint.textContent = 'Password kuat - tambahkan: ' + hints.join(', ');
        } else {
            passwordStrength.className = 'progress-bar bg-success';
            passwordHint.textContent = 'Password sangat kuat!';
        }
    });
    
    // Password confirmation validation
    const confirmPasswordInput = document.getElementById('passwordConfirmationInput');
    const confirmPasswordHint = document.getElementById('confirm-password-hint');
    
    function validatePasswordMatch() {
        if (confirmPasswordInput.value) {
            if (passwordInput.value === confirmPasswordInput.value) {
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
                confirmPasswordHint.textContent = 'Password cocok!';
                confirmPasswordHint.className = 'form-hint text-success';
                confirmPasswordInput.setCustomValidity('');
            } else {
                confirmPasswordInput.classList.remove('is-valid');
                confirmPasswordInput.classList.add('is-invalid');
                confirmPasswordHint.textContent = 'Password tidak sama!';
                confirmPasswordHint.className = 'form-hint text-danger';
                confirmPasswordInput.setCustomValidity('Password tidak sama');
            }
        } else {
            confirmPasswordInput.classList.remove('is-invalid', 'is-valid');
            confirmPasswordHint.textContent = 'Harus sama dengan password di atas';
            confirmPasswordHint.className = 'form-hint';
            confirmPasswordInput.setCustomValidity('');
        }
    }
    
    passwordInput.addEventListener('input', validatePasswordMatch);
    confirmPasswordInput.addEventListener('input', validatePasswordMatch);
    
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
                                <small class="text-success">
                                    <i class="ti ti-check me-1"></i>
                                    Siap untuk diupload
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

    // Form submission
    const form = document.getElementById('create-admin-form');
    const submitBtn = document.getElementById('submit-btn');
    
    form.addEventListener('submit', function(e) {
        // Final validation
        if (passwordInput.value !== confirmPasswordInput.value) {
            e.preventDefault();
            showAlert(confirmPasswordInput, 'danger', 'Password dan konfirmasi password harus sama.');
            confirmPasswordInput.focus();
            return false;
        }
        
        // Check password strength
        if (passwordInput.value.length < 8) {
            e.preventDefault();
            showAlert(passwordInput, 'danger', 'Password minimal 8 karakter.');
            passwordInput.focus();
            return false;
        }
    });
});
</script>
@endpush