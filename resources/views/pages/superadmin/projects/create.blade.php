@extends('layouts.main')

@section('title', 'Tambah Project')

@push('styles')
<link href="{{ asset('libs/tom-select/dist/css/tom-select.bootstrap5.min.css') }}" rel="stylesheet" />
<style>
    .ts-dropdown, .ts-dropdown.form-control, .ts-dropdown.form-select {
        background-color: #fff;
    }
</style>
@endpush

@section('content')
{{-- Alert Messages --}}
@include('components.alert')

<form action="{{ route('superadmin.projects.store') }}" id="create-project-form" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="row g-3">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Project Source Selection -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-source me-2"></i>Sumber Project</h3>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column" id="new-project-section">
                                <label class="form-selectgroup-item flex-fill">
                                    <input type="radio" name="project_source" class="form-selectgroup-input"  value="new" {{ old('project_source', 'new') == 'new' ? 'checked' : '' }} />
                                    <div class="form-selectgroup-label d-flex align-items-center p-3">
                                        <div class="me-3">
                                            <span class="form-selectgroup-check"></span>
                                        </div>
                                        <div class="form-selectgroup-label-content d-flex align-items-center">
                                            <div>
                                                <strong>Buat Project Baru</strong>
                                                <p class="text-secondary small mb-0 mt-1">Buat project baru dengan nama dan informasi yang Anda tentukan sendiri.</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column" id="existing-project-section">
                                <label class="form-selectgroup-item flex-fill">
                                    <input type="radio" name="project_source" class="form-selectgroup-input"  value="existing" {{ old('project_source') == 'existing' ? 'checked' : '' }} />
                                    <div class="form-selectgroup-label d-flex align-items-center p-3">
                                        <div class="me-3">
                                            <span class="form-selectgroup-check"></span>
                                        </div>
                                        <div class="form-selectgroup-label-content d-flex align-items-center">
                                            <div>
                                                <strong>Pilih dari CRM</strong>
                                                <p class="text-secondary small mb-0 mt-1">Pilih project yang sudah ada di sistem CRM sebagai basis.</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-folder me-2"></i>Informasi Project</h3>
                </div>
                <div class="card-body">
                    <!-- Existing Project Selection -->
                    <div id="existing-project-fields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Pilih Project dari CRM <span class="text-danger">*</span></label>
                            <select class="form-select @error('existing_project_id') is-invalid @enderror" name="existing_project_id" id="crm-project-select">
                                <option value="">Pilih project...</option>
                            </select>
                            @error('existing_project_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            <small class="form-hint">Ketik untuk mencari project berdasarkan nama</small>
                        </div>
                    </div>

                    <!-- New Project Fields -->
                    <div id="new-project-fields">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Project <span class="text-danger new-required">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           name="name" value="{{ old('name') }}" id="project-name-input"
                                           placeholder="Masukkan nama project" autocomplete="off">
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
                                    <label class="form-label">Developer</label>
                                    <input type="text" class="form-control @error('developer_name') is-invalid @enderror" 
                                           name="developer_name" value="{{ old('developer_name') }}"
                                           placeholder="Nama perusahaan developer" autocomplete="off">
                                    @error('developer_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                    <small class="form-hint">
                                        Misal: PT Hunian Harmonis Ceria
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Lokasi</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       name="location" value="{{ old('location') }}" 
                                       placeholder="Jakarta Selatan" autocomplete="off">
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
                                       name="website_url" value="{{ old('website_url') }}"
                                       placeholder="https://example.com" autocomplete="off">
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
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                       name="logo" accept="image/*" id="logo-input">
                                @error('logo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    <i class="ti ti-info-circle me-1"></i>
                                    Format: JPG, PNG, GIF. Maksimal 2MB
                                </small>
                                <div class="mt-2" id="logo-preview"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">File Brosur</label>
                                <input type="file" class="form-control @error('brochure_file') is-invalid @enderror" 
                                       name="brochure_file" accept=".pdf" id="brochure-input">
                                @error('brochure_file')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    Format: PDF. Maksimal 10MB
                                </small>
                                <div class="mt-2" id="brochure-preview"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">File Price List</label>
                                <input type="file" class="form-control @error('price_list_file') is-invalid @enderror" 
                                       name="price_list_file" accept=".pdf" id="pricelist-input">
                                @error('price_list_file')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">
                                    Format: PDF. Maksimal 10MB
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
                                       name="start_date" value="{{ old('start_date') }}" id="start-date-input">
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
                                       name="end_date" value="{{ old('end_date') }}" id="end-date-input">
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
                                    <option value="booking_fee" {{ old('commission_payment_trigger') == 'booking_fee' ? 'selected' : '' }}>Booking Fee</option>
                                    <option value="akad_kredit" {{ old('commission_payment_trigger') == 'akad_kredit' ? 'selected' : '' }}>Akad Kredit</option>
                                    <option value="spk" {{ old('commission_payment_trigger') == 'spk' ? 'selected' : '' }}>SPK (Surat Perjanjian Kerja)</option>
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

                    <!-- PIC Information -->
                    <h5 class="mb-3">Informasi PIC (Person In Charge)</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Nama PIC</label>
                                <input type="text" class="form-control @error('pic_name') is-invalid @enderror" 
                                       name="pic_name" value="{{ old('pic_name') }}"
                                       placeholder="Nama lengkap PIC" autocomplete="off">
                                @error('pic_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">PIC akan dibuatkan akun admin jika belum ada</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Phone PIC</label>
                                <input type="tel" class="form-control @error('pic_phone') is-invalid @enderror" 
                                       name="pic_phone" value="{{ old('pic_phone') }}"
                                       placeholder="08123456789" autocomplete="off">
                                @error('pic_phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Email PIC</label>
                                <input type="email" class="form-control @error('pic_email') is-invalid @enderror" 
                                       name="pic_email" value="{{ old('pic_email') }}"
                                       placeholder="pic@example.com" autocomplete="off">
                                @error('pic_email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="form-hint">Email ini akan digunakan untuk login sebagai admin project</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Project</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" id="description-editor" rows="6" 
                                  placeholder="Masukkan deskripsi project...">{{ old('description') }}</textarea>
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
                                  placeholder="Masukkan syarat dan ketentuan...">{{ old('terms_and_conditions') }}</textarea>
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
                                  placeholder="Masukkan informasi tambahan...">{{ old('additional_info') }}</textarea>
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
                                   value="1" {{ old('require_digital_signature', true) ? 'checked' : '' }}>
                            <span class="form-check-label">Wajib Tanda Tangan Digital</span>
                        </label>
                        <small class="form-hint">Centang jika affiliator wajib memberikan tanda tangan digital.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" 
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="form-check-label">Project Aktif</span>
                        </label>
                        <small class="form-hint">Centang untuk mengaktifkan project setelah dibuat.</small>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Info Penting</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="ti ti-bulb icon icon-1"></i>
                        <div>
                            <strong>Admin Project:</strong><br>
                            Setelah project dibuat, Anda dapat menambahkan admin untuk mengelola project ini melalui menu "Admin" pada detail project.
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mb-0">
                        <i class="ti ti-alert-triangle icon icon-1"></i>
                        <div>
                            <strong>Unit Project:</strong><br>
                            Jangan lupa untuk menambahkan unit project setelah project dibuat agar affiliator dapat menambahkan lead.
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
                        <a href="{{ route('superadmin.projects.index') }}" class="btn btn-link">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary ms-auto" id="submit-btn">
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Project
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
    
    // Initialize Tom Select for CRM projects
    const crmProjectSelect = new TomSelect('#crm-project-select', {
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        placeholder: 'Pilih project dari CRM...',
        load: function(query, callback) {
            const url = '{{ route("superadmin.projects.crm-projects") }}?' + new URLSearchParams({
                q: query,
                page: 1
            });
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    callback(data.results);
                })
                .catch(error => {
                    console.error('Error:', error);
                    callback();
                });
        },
        render: {
            option: function(data, escape) {
                return `<div class="py-2 px-3">
                    <div class="fw-bold">${escape(data.text)}</div>
                </div>`;
            },
            item: function(data, escape) {
                return `<div>${escape(data.text)}</div>`;
            }
        }
    });

    // Project source radio handlers
    const newProjectRadio = document.querySelector('input[name="project_source"][value="new"]');
    const existingProjectRadio = document.querySelector('input[name="project_source"][value="existing"]');
    const newProjectFields = document.getElementById('new-project-fields');
    const existingProjectFields = document.getElementById('existing-project-fields');
    const newProjectSection = document.getElementById('new-project-section');
    const existingProjectSection = document.getElementById('existing-project-section');

    function toggleProjectSourceFields() {
        const isNewProject = newProjectRadio.checked;
        
        newProjectFields.style.display = isNewProject ? 'block' : 'none';
        existingProjectFields.style.display = isNewProject ? 'none' : 'block';
        
        // Update required attributes
        const nameInput = document.getElementById('project-name-input');
        
        if (isNewProject) {
            nameInput.setAttribute('required', 'required');
            crmProjectSelect.settings.required = false;
            newProjectSection.classList.add('active');
            existingProjectSection.classList.remove('active');
        } else {
            nameInput.removeAttribute('required');
            crmProjectSelect.settings.required = true;
            newProjectSection.classList.remove('active');
            existingProjectSection.classList.add('active');
        }

        // Update required indicators
        document.querySelectorAll('.new-required').forEach(el => {
            el.style.display = isNewProject ? 'inline' : 'none';
        });
    }

    newProjectRadio.addEventListener('change', toggleProjectSourceFields);
    existingProjectRadio.addEventListener('change', toggleProjectSourceFields);

    // Initialize on page load
    toggleProjectSourceFields();
    
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
    }

    // Form submission
    const form = document.getElementById('create-project-form');
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