<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Daftar Project - {{ config('app.name') }}</title>
  @include('partials.style')
  
  <!-- Custom styles -->
  <style>
    /* .step-indicator {
      display: flex;
      justify-content: center;
      margin-bottom: 2rem;
    }

    .step-item {
      display: flex;
      align-items: center;
      color: #6c757d;
    }

    .step-item.active {
      color: #0054a6;
    }

    .step-item.completed {
      color: #28a745;
    }

    .step-number {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 2rem;
      height: 2rem;
      border-radius: 50%;
      background: #e9ecef;
      font-weight: 600;
      margin-right: 0.5rem;
    }

    .step-item.active .step-number {
      background: #0054a6;
      color: white;
    }

    .step-item.completed .step-number {
      background: #28a745;
      color: white;
    }

    .step-line {
      width: 3rem;
      height: 2px;
      background: #e9ecef;
      margin: 0 1rem;
    }

    .step-item.completed + .step-line {
      background: #28a745;
    } */

    .card-step {
      display: none;
      animation: slideIn 0.3s ease-in-out;
    }

    .card-step.active {
      display: block;
    }

    .unit-form {
      border: 1px solid #e9ecef;
      border-radius: 0.375rem;
      padding: 1.5rem;
      margin-bottom: 1rem;
      position: relative;
    }

    .unit-form .remove-unit {
      position: absolute;
      top: 0.5rem;
      right: 0.5rem;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .file-preview {
      margin-top: 0.5rem;
    }

    .file-preview img {
      max-width: 200px;
      max-height: 150px;
      object-fit: cover;
      border-radius: 0.375rem;
    }

    .completion-message {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      border: none;
    }

    @media (max-width: 768px) {
      .step-indicator {
        overflow-x: auto;
        padding-bottom: 1rem;
      }
      
      .step-line {
        width: 2rem;
        margin: 0 0.5rem;
      }
    }
  </style>
</head>

<body>
  <div class="page page-center">
    {{-- <div class="position-fixed top-0 end-0 p-3" style="z-index: 1000;">
      <a href="{{ route('affiliator.dashboard') }}" class="btn btn-link" title="Kembali ke dashboard">
        Dashboard
      </a>
    </div> --}}

    <form class="container py-4" id="project-registration-form">
      <div class="text-center mb-4">
        <div class="navbar-brand navbar-brand-autodark">
          @php
          $siteLogo = \App\Models\SystemSetting::getValue('site_logo');
          $siteName = \App\Models\SystemSetting::getValue('site_name', 'Affiliator System');
          @endphp

          @if($siteLogo)
          <img src="{{ asset('storage/' . $siteLogo) }}" width="110" height="32" alt="{{ $siteName }}"
              class="navbar-brand-image">
          @else
          {{ $siteName }}
          @endif
        </div>
      </div>
      
      @include('components.alert')
      
      <!-- Welcome Section -->
      <div class="text-center mb-4">
        <h1>Daftarkan Project Anda</h1>
        <p class="text-secondary">
          Lengkapi formulir berikut untuk mendaftarkan project baru ke dalam sistem affiliator
        </p>
      </div>

      <!-- Step Indicator -->
      {{-- <div class="step-indicator">
        <div class="step-item active" id="step-indicator-1">
          <div class="step-number">1</div>
          <span class="d-none d-sm-inline">Info Project</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item" id="step-indicator-2">
          <div class="step-number">2</div>
          <span class="d-none d-sm-inline">File</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item" id="step-indicator-3">
          <div class="step-number">3</div>
          <span class="d-none d-sm-inline">Unit</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item" id="step-indicator-4">
          <div class="step-number">4</div>
          <span class="d-none d-sm-inline">Komisi & PIC</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item" id="step-indicator-5">
          <div class="step-number">5</div>
          <span class="d-none d-sm-inline">Periode</span>
        </div>
      </div> --}}

      <!-- Step 1: Project Information -->
      <div class="card card-md card-step active" id="step-1">
        <div class="card-header justify-content-between align-items-center">
          <h3 class="card-title">
            <i class="ti ti-info-circle me-2"></i>
            Informasi Project
          </h3>
          <div class="card-subtitle">Langkah 1 dari 5</div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Nama Project <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="project_name" name="project_name" required
                       placeholder="Contoh: Green Valley Residence">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Nama Developer <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="developer_name" name="developer_name" required
                       placeholder="Contoh: PT Hunian Harmonis Ceria">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="location" name="location" required
                       placeholder="Contoh: Jakarta Selatan">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Link Website</label>
                <input type="url" class="form-control" id="website_url" name="website_url"
                       placeholder="https://example.com">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Deskripsi Project <span class="text-danger">*</span></label>
            <textarea class="form-control" id="description" name="description" required rows="6"
                      placeholder="Jelaskan detail project, fasilitas, dan keunggulan yang ditawarkan..."></textarea>
          </div>
        </div>
      </div>

      <!-- Step 2: File Uploads -->
      <div class="card card-md card-step" id="step-2">
        <div class="card-header justify-content-between align-items-center">
          <h3 class="card-title">
            <i class="ti ti-upload me-2"></i>
            Upload File
          </h3>
          <div class="card-subtitle">Langkah 2 dari 5</div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Logo Project</label>
                <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                <small class="form-hint">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                <div class="file-preview" id="logo-preview"></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">File Brosur</label>
                <input type="file" class="form-control" id="brochure_file" name="brochure_file" accept=".pdf">
                <small class="form-hint">Format: PDF. Maksimal 10MB</small>
                <div class="file-preview" id="brochure-preview"></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">File Price List</label>
                <input type="file" class="form-control" id="price_list_file" name="price_list_file" accept=".pdf">
                <small class="form-hint">Format: PDF. Maksimal 10MB</small>
                <div class="file-preview" id="pricelist-preview"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 3: Units -->
      <div class="card card-md card-step" id="step-3">
        <div class="card-header justify-content-between align-items-center">
          <h3 class="card-title">
            <i class="ti ti-building me-2"></i>
            Data Unit
          </h3>
          <div class="card-subtitle">Langkah 3 dari 5</div>
        </div>
        <div class="card-body">
          <div id="units-container">
            <!-- Units will be added here -->
          </div>
          
          <div class="text-center">
            <button type="button" class="btn btn-outline-primary" id="add-unit-btn">
              <i class="ti ti-plus me-1"></i>
              Tambah Unit
            </button>
          </div>
        </div>
      </div>

      <!-- Step 4: Commission & PIC -->
      <div class="card card-md card-step" id="step-4">
        <div class="card-header justify-content-between align-items-center">
          <h3 class="card-title">
            <i class="ti ti-percentage me-2"></i>
            Skema Komisi & PIC
          </h3>
          <div class="card-subtitle">Langkah 4 dari 5</div>
        </div>
        <div class="card-body">
          <h4 class="mb-3">Skema Pembayaran Komisi</h4>
          <div class="mb-4">
            <label class="form-label">Komisi dibayar setelah <span class="text-danger">*</span></label>
            <select class="form-select" id="commission_payment_trigger" name="commission_payment_trigger" required>
              <option value="">Pilih trigger pembayaran komisi</option>
              <option value="booking_fee">Booking Fee</option>
              <option value="akad_kredit">Akad Kredit</option>
              <option value="spk">SPK (Surat Perjanjian Kerja)</option>
            </select>
          </div>

          <h4 class="mb-3">Informasi PIC (Person In Charge)</h4>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Nama PIC <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="pic_name" name="pic_name" required
                       placeholder="Nama lengkap PIC">
                <small class="form-hint">PIC akan dibuatkan akun admin untuk mengelola project ini</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Phone PIC <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="pic_phone" name="pic_phone" required
                       placeholder="08123456789">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Email PIC <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="pic_email" name="pic_email" required
                   placeholder="pic@example.com">
            <small class="form-hint">Email ini akan digunakan untuk login sebagai admin project</small>
          </div>
        </div>
      </div>

      <!-- Step 5: Project Period -->
      <div class="card card-md card-step" id="step-5">
        <div class="card-header justify-content-between align-items-center">
          <h3 class="card-title">
            <i class="ti ti-calendar me-2"></i>
            Periode Project
          </h3>
          <div class="card-subtitle">Langkah 5 dari 5</div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="start_date" name="start_date" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Tanggal Berakhir</label>
                <input type="date" class="form-control" id="end_date" name="end_date">
                <small class="form-hint">Kosongkan jika project tidak memiliki batas waktu</small>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <h4 class="mb-3">Pernyataan dan Persetujuan</h4>
          
          <div class="mb-3">
            <label class="form-check">
              <input type="checkbox" class="form-check-input" id="data_accuracy" name="data_accuracy" required>
              <span class="form-check-label">
                <strong>Saya menyatakan bahwa semua data yang saya input adalah benar dan dapat dipertanggungjawabkan</strong>
              </span>
            </label>
          </div>

          <div class="mb-3">
            <label class="form-check">
              <input type="checkbox" class="form-check-input" id="terms_agreement" name="terms_agreement" required>
              <span class="form-check-label">
                <strong>Saya menyetujui aturan program afiliasi yang berlaku</strong>
              </span>
            </label>
          </div>

          <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            <strong>Informasi:</strong> Setelah Anda submit, project akan masuk ke dalam review admin. 
            Anda akan mendapat notifikasi jika project disetujui atau ditolak.
          </div>
        </div>
      </div>

      <!-- Completion Message -->
      <div class="card card-md completion-message" id="completion-card" style="display: none;">
        <div class="card-body text-center py-4">
          <div class="mb-3">
            <i class="ti ti-circle-check icon icon-xl"></i>
          </div>
          <h3>Project Berhasil Didaftarkan!</h3>
          <p class="mb-0">
            Pendaftaran project Anda telah berhasil dikirim dan sedang menunggu persetujuan admin. 
            Anda akan mendapat notifikasi melalui email ketika ada update status.
          </p>
        </div>
      </div>

      <!-- Navigation Buttons -->
      <div class="row align-items-center mt-3">
        <div class="d-flex align-items-center justify-content-between gap-3">
          <div class="progress" style="width: 150px;">
            <div class="progress-bar" id="progress-bar" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>

          <div class="btn-list justify-content-end">
            <button type="button" class="btn btn-link link-secondary" id="back-btn" style="display: none;">
              Kembali
            </button>
            <button type="button" class="btn btn-primary" id="next-btn">
              Lanjut
            </button>
            <button type="submit" class="btn btn-success" id="submit-btn" style="display: none;">
              <i class="ti ti-send me-1"></i>
              Daftar Project
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>

  @include('partials.script')
  @include('components.toast')
  @include('components.scripts.wysiwyg')

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      let currentStep = 1;
      let totalSteps = 5;
      let unitCounter = 0;

      // Initialize HugeRTE for description
      options.selector = '#description';
      hugeRTE.init(options);

      // DOM Elements
      const backButton = document.getElementById('back-btn');
      const nextButton = document.getElementById('next-btn');
      const submitButton = document.getElementById('submit-btn');
      const progressBar = document.getElementById('progress-bar');
      const addUnitBtn = document.getElementById('add-unit-btn');
      const unitsContainer = document.getElementById('units-container');

      // Step navigation functions
      function updateProgress(step) {
        const progress = (step / totalSteps) * 100;
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
        
        // Update step indicators
        for (let i = 1; i <= totalSteps; i++) {
          const indicator = document.getElementById(`step-indicator-${i}`);
          indicator.classList.remove('active', 'completed');
          
          if (i < step) {
            indicator.classList.add('completed');
          } else if (i === step) {
            indicator.classList.add('active');
          }
        }
        
        // Show/hide navigation buttons
        backButton.style.display = step > 1 ? 'inline-block' : 'none';
        nextButton.style.display = step < totalSteps ? 'inline-block' : 'none';
        submitButton.style.display = step === totalSteps ? 'inline-block' : 'none';
      }

      function showStep(step) {
        // Hide all steps
        document.querySelectorAll('.card-step').forEach(card => {
          card.classList.remove('active');
        });
        
        // Show current step
        const currentStepCard = document.getElementById(`step-${step}`);
        if (currentStepCard) {
          currentStepCard.classList.add('active');
          currentStepCard.scrollIntoView({ behavior: 'smooth' });
        }
        
        updateProgress(step);
        validateCurrentStep();
      }

      function validateCurrentStep() {
        let isValid = false;
        
        switch (currentStep) {
          case 1:
            const projectName = document.getElementById('project_name').value.trim();
            const developerName = document.getElementById('developer_name').value.trim();
            const location = document.getElementById('location').value.trim();
            const description = hugeRTE.get('description').getContent().trim();
            
            isValid = projectName && developerName && location && 
                     description && description !== '<p></p>' && description !== '<p><br></p>';
            break;
            
          case 2:
            isValid = true; // Files are optional
            break;
            
          case 3:
            isValid = document.querySelectorAll('.unit-form').length > 0 && validateAllUnits();
            break;
            
          case 4:
            const trigger = document.getElementById('commission_payment_trigger').value;
            const picName = document.getElementById('pic_name').value.trim();
            const picPhone = document.getElementById('pic_phone').value.trim();
            const picEmail = document.getElementById('pic_email').value.trim();
            
            isValid = trigger && picName && picPhone && picEmail;
            break;
            
          case 5:
            const startDate = document.getElementById('start_date').value;
            const dataAccuracy = document.getElementById('data_accuracy').checked;
            const termsAgreement = document.getElementById('terms_agreement').checked;
            
            isValid = startDate && dataAccuracy && termsAgreement;
            break;
        }
        
        nextButton.disabled = !isValid;
        submitButton.disabled = !isValid;
      }

      // Navigation handlers
      nextButton.addEventListener('click', function() {
        if (currentStep < totalSteps) {
          currentStep++;
          showStep(currentStep);
        }
      });

      backButton.addEventListener('click', function() {
        if (currentStep > 1) {
          currentStep--;
          showStep(currentStep);
        }
      });

      // Form field listeners for validation
      document.querySelectorAll('#step-1 input, #step-1 textarea').forEach(input => {
        input.addEventListener('input', () => {
          if (currentStep === 1) validateCurrentStep();
        });
      });

      document.querySelectorAll('#step-4 input, #step-4 select').forEach(input => {
        input.addEventListener('input', () => {
          if (currentStep === 4) validateCurrentStep();
        });
      });

      document.querySelectorAll('#step-5 input').forEach(input => {
        input.addEventListener('change', () => {
          if (currentStep === 5) validateCurrentStep();
        });
      });

      // File preview functions
      function setupFilePreview(inputId, previewId, isImage = false) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        
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
      setupFilePreview('logo', 'logo-preview', true);
      setupFilePreview('brochure_file', 'brochure-preview', false);
      setupFilePreview('price_list_file', 'pricelist-preview', false);

      // Unit management
      function createUnitForm() {
        unitCounter++;
        const unitHtml = `
          <div class="unit-form" data-unit="${unitCounter}">
            <button type="button" class="btn btn-icon btn-sm btn-outline-danger remove-unit">
              <i class="ti ti-x"></i>
            </button>
            
            <h5 class="mb-3">Unit #${unitCounter}</h5>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Nama Unit <span class="text-danger">*</span></label>
                  <input type="text" class="form-control unit-field" name="units[${unitCounter}][name]" required
                         placeholder="Contoh: Type A">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Harga <span class="text-danger">*</span></label>
                  <input type="number" class="form-control unit-field" name="units[${unitCounter}][price]" required min="0"
                         placeholder="500000000">
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Tipe Unit <span class="text-danger">*</span></label>
                  <select class="form-select unit-field" name="units[${unitCounter}][unit_type]" required>
                    <option value="">Pilih Tipe Unit</option>
                    <option value="residential">Residential</option>
                    <option value="commercial">Komersial</option>
                    <option value="office">Perkantoran</option>
                    <option value="retail">Retail</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Tipe Komisi <span class="text-danger">*</span></label>
                  <select class="form-select unit-field commission-type" name="units[${unitCounter}][commission_type]" required>
                    <option value="">Pilih Tipe Komisi</option>
                    <option value="percentage">Persentase (%)</option>
                    <option value="fixed">Nominal Tetap (Rp)</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label class="form-label">Nilai Komisi <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text commission-prefix">Rp</span>
                    <input type="number" class="form-control unit-field" name="units[${unitCounter}][commission_value]" required min="0" step="0.1">
                  </div>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Deskripsi Unit</label>
              <textarea class="form-control unit-field" name="units[${unitCounter}][description]" rows="2"
                        placeholder="Deskripsi singkat unit..."></textarea>
            </div>
            
            <div class="row">
              <div class="col-md-3">
                <div class="mb-3">
                  <label class="form-label">Luas Bangunan (m²)</label>
                  <input type="number" class="form-control unit-field" name="units[${unitCounter}][building_area]" min="0">
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-3">
                  <label class="form-label">Luas Tanah (m²)</label>
                  <input type="number" class="form-control unit-field" name="units[${unitCounter}][land_area]" min="0">
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-3">
                  <label class="form-label">Kamar Tidur</label>
                  <input type="number" class="form-control unit-field" name="units[${unitCounter}][bedrooms]" min="0">
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-3">
                  <label class="form-label">Kamar Mandi</label>
                  <input type="number" class="form-control unit-field" name="units[${unitCounter}][bathrooms]" min="0">
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-3">
                <div class="mb-3">
                  <label class="form-label">Carport</label>
                  <input type="number" class="form-control unit-field" name="units[${unitCounter}][carport]" min="0">
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-3">
                  <label class="form-label">Lantai</label>
                  <input type="number" class="form-control unit-field" name="units[${unitCounter}][floor]" min="0">
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-3">
                  <label class="form-label">Daya Listrik (VA)</label>
                  <input type="number" class="form-control unit-field" name="units[${unitCounter}][power_capacity]" min="0">
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-3">
                  <label class="form-label">Sertifikat</label>
                  <select class="form-select unit-field" name="units[${unitCounter}][certificate_type]">
                    <option value="">Pilih Sertifikat</option>
                    <option value="SHM">SHM</option>
                    <option value="HGB">HGB</option>
                    <option value="AJB">AJB</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Status Unit <span class="text-danger">*</span></label>
                  <select class="form-select unit-field" name="units[${unitCounter}][unit_status]" required>
                    <option value="">Pilih Status</option>
                    <option value="ready">Ready</option>
                    <option value="indent">Indent</option>
                    <option value="sold_out">Sold Out</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Gambar Unit</label>
                  <input type="file" class="form-control unit-field unit-image" name="units[${unitCounter}][image]" accept="image/*">
                  <div class="unit-image-preview"></div>
                </div>
              </div>
            </div>
          </div>
        `;
        
        unitsContainer.insertAdjacentHTML('beforeend', unitHtml);
        setupUnitEventListeners(unitCounter);
        validateCurrentStep();
      }

      function setupUnitEventListeners(unitId) {
        const unitForm = document.querySelector(`[data-unit="${unitId}"]`);
        
        // Remove unit button
        unitForm.querySelector('.remove-unit').addEventListener('click', function() {
          if (document.querySelectorAll('.unit-form').length > 1) {
            unitForm.remove();
            validateCurrentStep();
          } else {
            showToast('Minimal harus ada 1 unit', 'warning');
          }
        });
        
        // Commission type change
        unitForm.querySelector('.commission-type').addEventListener('change', function() {
          const prefix = unitForm.querySelector('.commission-prefix');
          prefix.textContent = this.value === 'percentage' ? '%' : 'Rp';
        });
        
        // Unit field validation
        unitForm.querySelectorAll('.unit-field').forEach(field => {
          field.addEventListener('input', () => {
            if (currentStep === 3) validateCurrentStep();
          });
        });
        
        // Unit image preview
        const imageInput = unitForm.querySelector('.unit-image');
        const imagePreview = unitForm.querySelector('.unit-image-preview');
        
        imageInput.addEventListener('change', function(e) {
          const file = e.target.files[0];
          imagePreview.innerHTML = '';
          
          if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
              imagePreview.innerHTML = `
                <div class="mt-2">
                  <img src="${e.target.result}" class="img-thumbnail" style="max-width: 150px; max-height: 100px;">
                  <div class="small text-muted mt-1">${file.name}</div>
                </div>
              `;
            };
            reader.readAsDataURL(file);
          }
        });
      }

      function validateAllUnits() {
        const units = document.querySelectorAll('.unit-form');
        if (units.length === 0) return false;
        
        for (let unit of units) {
          const requiredFields = unit.querySelectorAll('.unit-field[required]');
          for (let field of requiredFields) {
            if (!field.value.trim()) {
              return false;
            }
          }
        }
        return true;
      }

      // Add first unit and add unit button listener
      addUnitBtn.addEventListener('click', createUnitForm);
      
      // Create first unit by default
      createUnitForm();

      // Form submission
      document.getElementById('project-registration-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateCurrentStep()) {
          showToast('Lengkapi semua field yang wajib diisi', 'warning');
          return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

        const formData = new FormData();
        
        // Step 1 data
        formData.append('project_name', document.getElementById('project_name').value);
        formData.append('developer_name', document.getElementById('developer_name').value);
        formData.append('location', document.getElementById('location').value);
        formData.append('website_url', document.getElementById('website_url').value);
        formData.append('description', hugeRTE.get('description').getContent());
        
        // Step 2 data
        const logoFile = document.getElementById('logo').files[0];
        const brochureFile = document.getElementById('brochure_file').files[0];
        const priceListFile = document.getElementById('price_list_file').files[0];
        
        if (logoFile) formData.append('logo', logoFile);
        if (brochureFile) formData.append('brochure_file', brochureFile);
        if (priceListFile) formData.append('price_list_file', priceListFile);
        
        // Step 3 data (units)
        const units = document.querySelectorAll('.unit-form');
        units.forEach((unit, index) => {
          const unitId = unit.getAttribute('data-unit');
          const fields = unit.querySelectorAll('.unit-field');
          
          fields.forEach(field => {
            if (field.type === 'file' && field.files[0]) {
              formData.append(`units[${index}][image]`, field.files[0]);
            } else if (field.type !== 'file') {
              const fieldName = field.name.replace(`units[${unitId}]`, `units[${index}]`);
              formData.append(fieldName, field.value);
            }
          });
        });
        
        // Step 4 data
        formData.append('commission_payment_trigger', document.getElementById('commission_payment_trigger').value);
        formData.append('pic_name', document.getElementById('pic_name').value);
        formData.append('pic_phone', document.getElementById('pic_phone').value);
        formData.append('pic_email', document.getElementById('pic_email').value);
        
        // Step 5 data
        formData.append('start_date', document.getElementById('start_date').value);
        formData.append('end_date', document.getElementById('end_date').value);
        formData.append('data_accuracy', document.getElementById('data_accuracy').checked ? '1' : '0');
        formData.append('terms_agreement', document.getElementById('terms_agreement').checked ? '1' : '0');

        fetch('{{ route("affiliator.project-registration.store") }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Show completion
            document.querySelectorAll('.card-step').forEach(card => {
              card.style.display = 'none';
            });
            document.querySelector('.row.align-items-center.mt-3').style.display = 'none';
            
            const completionCard = document.getElementById('completion-card');
            completionCard.style.display = 'block';
            completionCard.scrollIntoView({ behavior: 'smooth' });
            
            showToast(data.message, 'success');
            
            // Update progress to 100%
            progressBar.style.width = '100%';
            progressBar.setAttribute('aria-valuenow', '100');
            
            // Update all step indicators to completed
            for (let i = 1; i <= totalSteps; i++) {
              const indicator = document.getElementById(`step-indicator-${i}`);
              indicator.classList.remove('active');
              indicator.classList.add('completed');
            }
            
            setTimeout(() => {
              if (data.redirect_url) {
                window.location.href = data.redirect_url;
              }
            }, 3000);
          } else {
            showToast(data.message || 'Terjadi kesalahan', 'danger');
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="ti ti-send me-1"></i>Daftar Project';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Terjadi kesalahan saat mendaftarkan project', 'danger');
          submitButton.disabled = false;
          submitButton.innerHTML = '<i class="ti ti-send me-1"></i>Daftar Project';
        });
      });

      // Initialize
      showStep(1);

      // Set minimum date for start_date to today
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('start_date').setAttribute('min', today);
      
      // Update end_date minimum when start_date changes
      document.getElementById('start_date').addEventListener('change', function() {
        document.getElementById('end_date').setAttribute('min', this.value);
      });
    });
  </script>
</body>
</html>