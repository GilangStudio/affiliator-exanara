@extends('layouts.main')

@section('title', 'Edit Project - ' . $project->name)

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
@include('components.alert')

<form action="{{ route('admin.projects.update', $project) }}" id="edit-project-form" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row g-3">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-folder me-2"></i>Edit Project - {{ $project->name }}</h3>
                </div>
                <div class="card-body">
                    <!-- Project Name (Read Only for Admin) -->
                    <div class="mb-3">
                        <label class="form-label">Nama Project</label>
                        <input type="text" class="form-control" value="{{ $project->name }}" readonly>
                        <small class="form-hint text-info">
                            <i class="ti ti-info-circle me-1"></i>
                            Nama project hanya dapat diubah oleh Super Admin
                        </small>
                    </div>

                    @if($project->crm_project_id)
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-info-circle me-2"></i>
                            <div>
                                <strong>Project CRM:</strong> Project ini terhubung dengan CRM Project ID: {{ $project->crm_project_id }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Lokasi</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       name="location" value="{{ old('location', $project->location) }}"
                                       placeholder="Jakarta">
                                @error('location')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    Misal: Jakarta, Bandung, Surabaya
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
                                    <small class="text-danger">{{ $message }}</small>
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
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" id="description-editor" rows="4" 
                                  placeholder="Masukkan deskripsi project...">{{ old('description', $project->description) }}</textarea>
                        @error('description')
                            <small class="text-danger">{{ $message }}</small>
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
                            <small class="text-danger">{{ $message }}</small>
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
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <div class="invalid-feedback" id="additional-info-error" style="display: none;"></div>
                        <small class="form-hint">Informasi tambahan yang diperlukan affiliator.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Project Settings -->
            <div class="card">
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
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="text-center">
                                @php $pendingWithdrawals = $project->commissionWithdrawals()->where('status', 'pending')->count(); @endphp
                                <div class="h3 mb-0 {{ $pendingWithdrawals > 0 ? 'text-warning' : '' }}">{{ $pendingWithdrawals }}</div>
                                <div class="text-secondary small">Penarikan Pending</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Info -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Info Admin</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="ti ti-user-check icon icon-1"></i>
                        <div>
                            <strong>Catatan:</strong><br>
                            Sebagai admin project, Anda dapat mengubah informasi project kecuali nama project dan koneksi CRM yang hanya dapat diubah oleh Super Admin.
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
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-link">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize HugeRTE for textareas
    options.selector = '#description-editor';
    hugeRTE.init(options);

    options.selector = '#terms-editor';
    hugeRTE.init(options);

    options.selector = '#additional-info-editor';
    hugeRTE.init(options);
    
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

    // Show alert function
    function showAlert(input, type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-2`;
        alertDiv.innerHTML = `
            <div class="d-flex">
                <div>
                    <i class="ti ti-alert-triangle icon alert-icon me-2"></i>
                </div>
                <div>${message}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert"></a>
        `;
        
        // Insert after the input element
        input.parentNode.insertBefore(alertDiv, input.nextSibling);
        
        setTimeout(() => {
            if (alertDiv.parentElement) {
                alertDiv.remove();
            }
        }, 5000);
    }
});
</script>
@endpush