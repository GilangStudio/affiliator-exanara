@extends('layouts.main')

@section('title', 'Tambah Affiliator')

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

<form action="{{ route('superadmin.affiliators.store') }}" id="create-affiliator-form" method="POST" enctype="multipart/form-data">
    @csrf
    
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
                                       name="name" value="{{ old('name') }}" required
                                       placeholder="Masukkan nama lengkap">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
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
                                       placeholder="affiliator@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Email akan digunakan untuk login</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Kode Negara <span class="text-danger">*</span></label>
                                <select class="form-select @error('country_code') is-invalid @enderror" 
                                        name="country_code" required>
                                    <option value="">Pilih</option>
                                    <option value="+62" {{ old('country_code') == '+62' ? 'selected' : '' }}>+62 (Indonesia)</option>
                                    <option value="+1" {{ old('country_code') == '+1' ? 'selected' : '' }}>+1 (US/Canada)</option>
                                    <option value="+44" {{ old('country_code') == '+44' ? 'selected' : '' }}>+44 (UK)</option>
                                    <option value="+65" {{ old('country_code') == '+65' ? 'selected' : '' }}>+65 (Singapore)</option>
                                    <option value="+60" {{ old('country_code') == '+60' ? 'selected' : '' }}>+60 (Malaysia)</option>
                                </select>
                                @error('country_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="mb-3">
                                <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       name="phone" value="{{ old('phone') }}" required
                                       placeholder="8123456789">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Nomor tanpa kode negara (contoh: 8123456789)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           name="password" required minlength="8" id="password-input"
                                           placeholder="Minimal 8 karakter">
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                        <i class="ti ti-eye" id="password-icon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
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
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                           name="password_confirmation" required id="confirm-password-input"
                                           placeholder="Ulangi password">
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-confirm-password">
                                        <i class="ti ti-eye" id="confirm-password-icon"></i>
                                    </button>
                                </div>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
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
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">
                            <i class="ti ti-info-circle me-1"></i>
                            Format: JPG, PNG, GIF. Maksimal 2MB
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
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="form-check-label">Akun Aktif</span>
                        </label>
                        <small class="form-hint">Centang untuk mengaktifkan akun setelah dibuat</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <div>
                            <strong>Info:</strong><br>
                            Affiliator akan menerima notifikasi bahwa akun mereka telah dibuat dan dapat langsung login ke sistem.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Permissions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-shield me-2"></i>Hak Akses Affiliator</h3>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-check text-success me-2"></i>
                            <span>Bergabung dengan project</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-check text-success me-2"></i>
                            <span>Menambah dan mengelola lead</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-check text-success me-2"></i>
                            <span>Melihat komisi dan melakukan penarikan</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-check text-success me-2"></i>
                            <span>Mengelola rekening bank</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-check text-success me-2"></i>
                            <span>Melihat laporan dan statistik</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center px-0">
                            <i class="ti ti-x text-danger me-2"></i>
                            <span>Mengelola user lain</span>
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
                            Pastikan email dan nomor telepon yang dimasukkan valid. Affiliator akan menerima informasi login melalui email.
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
                        <a href="{{ route('superadmin.affiliators.index') }}" class="btn btn-link">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary ms-auto" id="submit-btn">
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Affiliator
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
    
    // Password visibility toggles
    const passwordInput = document.getElementById('password-input');
    const passwordIcon = document.getElementById('password-icon');
    const togglePassword = document.getElementById('toggle-password');
    
    const confirmPasswordInput = document.getElementById('confirm-password-input');
    const confirmPasswordIcon = document.getElementById('confirm-password-icon');
    const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
    
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        passwordIcon.classList.toggle('ti-eye');
        passwordIcon.classList.toggle('ti-eye-off');
    });
    
    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);
        confirmPasswordIcon.classList.toggle('ti-eye');
        confirmPasswordIcon.classList.toggle('ti-eye-off');
    });
    
    // Password strength indicator
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
    const form = document.getElementById('create-affiliator-form');
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