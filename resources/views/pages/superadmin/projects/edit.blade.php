@extends('layouts.main')

@section('title', 'Edit Project')

@push('styles')
<style>
    textarea:focus {
        border-color: #a60000;
        box-shadow: 0 0 0 0.2rem rgba(0, 84, 166, 0.25);
    }
</style>
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

<form action="{{ route('superadmin.projects.update', $project) }}" id="edit-project-form" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row g-3">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-folder me-2"></i>Informasi Project</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Project <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', $project->name) }}" required
                                       placeholder="Masukkan nama project">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">
                                    <span id="name-count">{{ strlen($project->name) }}</span>/255 karakter
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Logo Project</label>
                                @if($project->logo)
                                    <div class="mb-2">
                                        <img src="{{ $project->logo_url }}" alt="Current Logo" class="avatar avatar-lg">
                                        <div class="text-secondary small">Logo saat ini</div>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                       name="logo" accept="image/*" id="logo-input">
                                @error('logo')
                                    <small class="text-danger">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">
                                    <i class="ti ti-info-circle me-1"></i>
                                    Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah.
                                </small>
                                <div class="mt-2" id="logo-preview"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" value="{{ $project->slug }}" readonly>
                        <small class="form-hint">Slug otomatis dibuat dari nama project dan tidak dapat diubah.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" id="description-editor" rows="4" 
                                  placeholder="Masukkan deskripsi project...">{{ old('description', $project->description) }}</textarea>
                        @error('description')
                            <small class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback" id="description-error" style="display: none;"></div>
                        <small class="form-hint">Deskripsi singkat tentang project ini.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Syarat dan Ketentuan <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('terms_and_conditions') is-invalid @enderror" 
                                  name="terms_and_conditions" id="terms-editor" rows="8" 
                                  placeholder="Masukkan syarat dan ketentuan...">{{ old('terms_and_conditions', $project->terms_and_conditions) }}</textarea>
                        @error('terms_and_conditions')
                            <small class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback" id="terms-error" style="display: none;"></div>
                        <small class="form-hint">Syarat dan ketentuan lengkap untuk project ini.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Informasi Tambahan</label>
                        <textarea class="form-control @error('additional_info') is-invalid @enderror" 
                                  name="additional_info" id="additional-info-editor" rows="6" 
                                  placeholder="Masukkan informasi tambahan...">{{ old('additional_info', $project->additional_info) }}</textarea>
                        @error('additional_info')
                            <small class="text-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback" id="additional-info-error" style="display: none;"></div>
                        <small class="form-hint">Informasi tambahan yang diperlukan affiliator.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Commission Settings -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-percentage me-2"></i>Pengaturan Komisi</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Tipe Komisi <span class="text-danger">*</span></label>
                        <select class="form-select @error('commission_type') is-invalid @enderror" 
                                name="commission_type" required id="commission-type-select">
                            <option value="">Pilih Tipe</option>
                            <option value="percentage" {{ old('commission_type', $project->commission_type) == 'percentage' ? 'selected' : '' }}>
                                Persentase (%)
                            </option>
                            <option value="fixed" {{ old('commission_type', $project->commission_type) == 'fixed' ? 'selected' : '' }}>
                                Fixed Amount (Rp)
                            </option>
                        </select>
                        @error('commission_type')
                            <small class="text-danger">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Pilih jenis komisi yang akan diberikan.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nilai Komisi <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" id="commission-prefix">
                                {{ $project->commission_type == 'percentage' ? '%' : 'Rp' }}
                            </span>
                            <input type="number" class="form-control @error('commission_value') is-invalid @enderror" 
                                   name="commission_value" value="{{ old('commission_value', $project->commission_value) }}" 
                                   step="0.01" min="0" required placeholder="0">
                        </div>
                        @error('commission_value')
                            <small class="text-danger">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">Masukkan nilai komisi sesuai tipe yang dipilih.</small>
                    </div>
                </div>
            </div>

            <!-- Project Settings -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-settings me-2"></i>Pengaturan Project</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="require_digital_signature" 
                                   value="1" {{ old('require_digital_signature', $project->require_digital_signature) ? 'checked' : '' }}>
                            <span class="form-check-label">Wajib Tanda Tangan Digital</span>
                        </label>
                        <small class="form-hint">Centang jika affiliator wajib memberikan tanda tangan digital.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" 
                                   value="1" {{ old('is_active', $project->is_active) ? 'checked' : '' }}>
                            <span class="form-check-label">Project Aktif</span>
                        </label>
                        <small class="form-hint">Centang untuk mengaktifkan project.</small>
                    </div>
                </div>
            </div>

            <!-- Project Stats -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-chart-bar me-2"></i>Statistik Project</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h2 mb-0">{{ $project->affiliatorProjects()->count() }}</div>
                                <div class="text-secondary">Affiliator</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h2 mb-0">{{ $project->leads()->count() }}</div>
                                <div class="text-secondary">Lead</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h3 mb-0 text-success">{{ $project->leads()->verified()->count() }}</div>
                                <div class="text-secondary small">Verified</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h3 mb-0 text-blue">{{ $project->affiliatorProjects()->where('status', 'active')->count() }}</div>
                                <div class="text-secondary small">Active</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Assignment -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-users me-2"></i>Assign Admin</h3>
                </div>
                <div class="card-body">
                    @if($admins->count() > 0)
                        <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                            @foreach($admins as $admin)
                            <label class="form-selectgroup-item flex-fill">
                                <input type="checkbox" name="admins[]" value="{{ $admin->id }}" 
                                       class="form-selectgroup-input"
                                       {{ in_array($admin->id, old('admins', $assignedAdmins)) ? 'checked' : '' }}>
                                <div class="form-selectgroup-label d-flex align-items-center p-3">
                                    <div class="me-3">
                                        <span class="avatar">{{ $admin->initials }}</span>
                                    </div>
                                    <div>
                                        <div class="font-weight-medium">{{ $admin->name }}</div>
                                        <div class="text-secondary">{{ $admin->email }}</div>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        <small class="form-hint">Pilih admin yang akan mengelola project ini.</small>
                    @else
                        <div class="text-center text-secondary">
                            <i class="ti ti-users-off icon icon-lg mb-2"></i>
                            <div>Belum ada admin tersedia</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="col-12">
            <div class="card">
                <div class="card-footer text-end">
                    <div class="d-flex">
                        <a href="{{ route('superadmin.projects.index') }}" class="btn btn-link">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary ms-auto" id="submit-btn">
                            <i class="ti ti-device-floppy me-1"></i>
                            Perbarui Project
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
@include('components.scripts.wysiwyg')
@include('components.alert')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize HugeRTE for textareas
    options.selector = '#description-editor';
    hugeRTE.init(options);

    options.selector = '#terms-editor';
    hugeRTE.init(options);

    options.selector = '#additional-info-editor';
    hugeRTE.init(options);
    
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
    
    // Commission type handler
    const commissionTypeSelect = document.getElementById('commission-type-select');
    const commissionPrefix = document.getElementById('commission-prefix');
    
    function updateCommissionPrefix() {
        const type = commissionTypeSelect.value;
        if (type === 'percentage') {
            commissionPrefix.textContent = '%';
        } else if (type === 'fixed') {
            commissionPrefix.textContent = 'Rp';
        } else {
            commissionPrefix.textContent = '';
        }
    }
    
    commissionTypeSelect.addEventListener('change', updateCommissionPrefix);
    
    // Logo preview functionality
    const logoInput = document.getElementById('logo-input');
    const logoPreview = document.getElementById('logo-preview');
    
    logoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            const maxSize = 2 * 1024 * 1024; // 2MB
            if (file.size > maxSize) {
                showAlert(logoInput, 'danger', 'File terlalu besar. Maksimal 2MB.');
                logoInput.value = '';
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                showAlert(logoInput, 'danger', 'Pilih file gambar yang valid.');
                logoInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                logoPreview.innerHTML = `
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
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearLogoPreview()">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <small class="text-warning">
                                    <i class="ti ti-alert-triangle me-1"></i>
                                    Akan mengganti logo yang ada
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            logoPreview.innerHTML = '';
        }
    });

    // Clear logo preview function
    window.clearLogoPreview = function() {
        logoInput.value = '';
        logoPreview.innerHTML = '';
    };

    // Form submission
    const form = document.getElementById('edit-project-form');
    const submitBtn = document.getElementById('submit-btn');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate terms and conditions
        const termsEditor = hugeRTE.get('terms-editor');
        const termsContent = termsEditor.getContent();
        const termsTextarea = document.querySelector('textarea[name="terms_and_conditions"]');
        const termsError = document.getElementById('terms-error');
        
        // Clear previous errors
        termsTextarea.classList.remove('is-invalid');
        termsError.style.display = 'none';
        
        // Check if terms content is empty
        if (!termsContent.trim() || termsContent.trim() === '<p></p>' || termsContent.trim() === '<p><br></p>') {
            e.preventDefault();
            termsTextarea.classList.add('is-invalid');
            termsError.textContent = 'Syarat dan ketentuan wajib diisi.';
            termsError.style.display = 'block';
            isValid = false;
        } else {
            termsTextarea.value = termsContent;
        }
        
        // Update other editor contents
        const descEditor = hugeRTE.get('description-editor');
        if (descEditor) {
            document.querySelector('textarea[name="description"]').value = descEditor.getContent();
        }
        
        const additionalEditor = hugeRTE.get('additional-info-editor');
        if (additionalEditor) {
            document.querySelector('textarea[name="additional_info"]').value = additionalEditor.getContent();
        }
        
        if (!isValid) {
            // Scroll to first error
            document.getElementById('terms-editor').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
            return false;
        }
    });
});
</script>
@endpush