@extends('layouts.main')

@section('title', 'Edit Project - ' . $project->name)

@push('styles')
<link href="{{ asset('libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}" rel="stylesheet" />
<style>
    .ts-dropdown, .ts-dropdown.form-control, .ts-dropdown.form-select {
        background-color: #fff;
    }
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
            <!-- Registration Status Alert -->
            @if($project->is_manual_registration)
                @if($project->registration_status === 'pending')
                    <div class="alert alert-warning mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-clock me-2"></i>
                            <div>
                                <strong>Project Menunggu Persetujuan</strong><br>
                                Project ini sedang menunggu persetujuan dari Super Admin. Anda tidak dapat mengedit project saat status pending.
                            </div>
                        </div>
                    </div>
                @elseif($project->registration_status === 'rejected')
                    <div class="alert alert-danger mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <div>
                                <strong>Project Ditolak</strong><br>
                                @if($project->latestRegistration && $project->latestRegistration->review_notes)
                                    <em>Alasan penolakan:</em> {{ $project->latestRegistration->review_notes }}
                                @endif
                                <div class="mt-2">
                                    <a href="{{ route('admin.projects.resubmit', $project) }}" class="btn btn-sm btn-warning">
                                        <i class="ti ti-refresh me-1"></i>
                                        Ajukan Ulang untuk Persetujuan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-folder me-2"></i>Edit Project - {{ $project->name }}</h3>
                </div>
                <div class="card-body">
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
                                <label class="form-label">Nama Project <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', $project->name) }}" id="project-name-input"
                                       placeholder="Masukkan nama project">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    <span id="name-count">{{ strlen($project->name) }}</span>/255 karakter
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Developer</label>
                                <input type="text" class="form-control @error('developer_name') is-invalid @enderror" 
                                       name="developer_name" value="{{ old('developer_name', $project->developer_name) }}"
                                       placeholder="Nama perusahaan developer">
                                @error('developer_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    Misal: PT Hunian Harmonis Ceria
                                </small>
                            </div>
                        </div>
                    </div>

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
                                <label class="form-label">Website URL</label>
                                <input type="url" class="form-control @error('website_url') is-invalid @enderror" 
                                       name="website_url" value="{{ old('website_url', $project->website_url) }}"
                                       placeholder="https://example.com">
                                @error('website_url')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    Link website resmi project (opsional)
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Files Section -->
                    <div class="row">
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">File Brosur</label>
                                @if($project->brochure_file)
                                    <div class="mb-2">
                                        <a href="{{ $project->brochure_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-file-text me-1"></i>Lihat File Saat Ini
                                        </a>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('brochure_file') is-invalid @enderror" 
                                       name="brochure_file" accept=".pdf" id="brochure-input">
                                @error('brochure_file')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    Format: PDF. Maksimal 10MB. Kosongkan jika tidak ingin mengubah.
                                </small>
                                <div class="mt-2" id="brochure-preview"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">File Price List</label>
                                @if($project->price_list_file)
                                    <div class="mb-2">
                                        <a href="{{ $project->price_list_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-file-text me-1"></i>Lihat File Saat Ini
                                        </a>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('price_list_file') is-invalid @enderror" 
                                       name="price_list_file" accept=".pdf" id="pricelist-input">
                                @error('price_list_file')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    Format: PDF. Maksimal 10MB. Kosongkan jika tidak ingin mengubah.
                                </small>
                                <div class="mt-2" id="pricelist-preview"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Period -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       name="start_date" value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}" 
                                       id="start-date-input">
                                @error('start_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    Tanggal project mulai aktif
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Berakhir</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                       name="end_date" value="{{ old('end_date', $project->end_date ? $project->end_date->format('Y-m-d') : '') }}" 
                                       id="end-date-input">
                                @error('end_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    Kosongkan jika project tidak memiliki batas waktu
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Commission Payment Trigger -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Komisi Dibayar Setelah</label>
                                <select class="form-select @error('commission_payment_trigger') is-invalid @enderror" 
                                        name="commission_payment_trigger">
                                    <option value="">Pilih trigger pembayaran komisi</option>
                                    <option value="booking_fee" {{ old('commission_payment_trigger', $project->commission_payment_trigger) == 'booking_fee' ? 'selected' : '' }}>Booking Fee</option>
                                    <option value="akad_kredit" {{ old('commission_payment_trigger', $project->commission_payment_trigger) == 'akad_kredit' ? 'selected' : '' }}>Akad Kredit</option>
                                    <option value="spk" {{ old('commission_payment_trigger', $project->commission_payment_trigger) == 'spk' ? 'selected' : '' }}>SPK (Surat Perjanjian Kerja)</option>
                                </select>
                                @error('commission_payment_trigger')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    Kapan komisi akan dibayarkan kepada affiliator
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Project</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" id="description-editor" rows="6" 
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

                    @if($project->registration_status === 'approved' || !$project->is_manual_registration)
                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" 
                                       value="1" {{ old('is_active', $project->is_active) ? 'checked' : '' }}>
                                <span class="form-check-label">Project Aktif</span>
                            </label>
                            <small class="form-hint">Centang untuk mengaktifkan project.</small>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Status Project:</strong><br>
                            Project harus disetujui terlebih dahulu sebelum dapat diaktifkan.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Project Statistics -->
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
                                <div class="h3 mb-0 text-purple">{{ $project->units()->count() }}</div>
                                <div class="text-secondary small">Total Unit</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registration Info -->
            @if($project->is_manual_registration && $project->latestRegistration)
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-file-text me-2"></i>Info Registration</h3>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Status:</strong>
                        <br>
                        <span class="badge bg-{{ $project->registration_status_color }}-lt ms-1">
                            {{ $project->registration_status_label }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Pendaftar:</strong>
                        <div class="text-secondary">{{ $project->latestRegistration->submittedBy->name }}</div>
                    </div>
                    <div class="mb-2">
                        <strong>Tanggal Daftar:</strong>
                        <div class="text-secondary">{{ $project->latestRegistration->created_at->format('d M Y') }}</div>
                    </div>
                    @if($project->latestRegistration->reviewed_at)
                    <div class="mb-2">
                        <strong>Direview:</strong>
                        <div class="text-secondary">{{ $project->latestRegistration->reviewed_at->format('d M Y') }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Submit Buttons -->
        <div class="col-12">
            <div class="card">
                <div class="card-footer text-end">
                    <div class="d-flex">
                        <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-link">
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
<script src="{{ asset('libs/tom-select/dist/js/tom-select.base.min.js') }}" defer></script>
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
    
    // Name character counter
    const nameInput = document.querySelector('input[name="name"]');
    const nameCount = document.getElementById('name-count');
    
    if (nameInput && nameCount) {
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
    }
    
    // File preview functions
    function setupFilePreview(inputId, previewId, isImage = false) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        
        if (!input || !preview) return;
        
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            preview.innerHTML = '';
            
            if (file) {
                if (isImage) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = `
                            <div class="mt-2">
                                <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                                <div class="small text-muted mt-1">${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</div>
                                <div class="mt-2">
                                    <small class="text-warning">
                                        <i class="ti ti-alert-triangle me-1"></i>
                                        Akan mengganti file yang ada
                                    </small>
                                </div>
                            </div>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.innerHTML = `
                        <div class="mt-2">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-file-text me-2 text-danger"></i>
                                <div>
                                    <div class="small">${file.name}</div>
                                    <div class="small text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                                </div>
                            </div>
                            <div class="mt-1">
                                <small class="text-warning">
                                    <i class="ti ti-alert-triangle me-1"></i>
                                    Akan mengganti file yang ada
                                </small>
                            </div>
                        </div>
                    `;
                }
            }
        });
    }

    // Setup file previews
    setupFilePreview('logo-input', 'logo-preview', true);
    setupFilePreview('brochure-input', 'brochure-preview', false);
    setupFilePreview('pricelist-input', 'pricelist-preview', false);

    // Date validation
    const startDateInput = document.getElementById('start-date-input');
    const endDateInput = document.getElementById('end-date-input');
    
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            endDateInput.setAttribute('min', this.value);
        });
        
        // Set initial min date for end date
        if (startDateInput.value) {
            endDateInput.setAttribute('min', startDateInput.value);
        }
    }

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