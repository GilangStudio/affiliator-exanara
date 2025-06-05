<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Join Project - {{ config('app.name') }}</title>
  @include('partials.style')
  
  <!-- Custom styles for join project -->
  <style>
    #terms-and-conditions * {
      all: revert;
      font-family: inherit;
      color: inherit;
    }

    .signature-canvas {
      width: 100%;
      height: 300px;
      border: 2px dashed #e9ecef;
      border-radius: 0.375rem;
      cursor: crosshair;
      transition: border-color 0.15s ease-in-out;
    }

    .units-scroll {
      display: flex;
      overflow-x: auto;
      gap: 1rem;
      padding: 0.5rem 0;
      scroll-behavior: smooth;
    }

    .units-scroll::-webkit-scrollbar {
      height: 6px;
    }

    .units-scroll::-webkit-scrollbar-track {
      background: #f1f3f4;
      border-radius: 10px;
    }

    .units-scroll::-webkit-scrollbar-thumb {
      background: #c1c8cd;
      border-radius: 10px;
    }

    .unit-card {
      width: 18rem;
      /* min-width: 280px; */
      flex-shrink: 0;
    }

    .commission-info-card {
      background: linear-gradient(135deg, #54d12b 0%, #1d7a1a 100%);
      color: white;
      border: none;
    }

    .commission-value {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .commission-desc {
      opacity: 0.9;
      font-size: 0.875rem;
    }

    .terms-content {
      max-height: 400px;
      overflow-y: auto;
      padding: 1rem;
      background: #f8f9fa;
      border-radius: 0.375rem;
      line-height: 1.6;
    }

    .card-transition {
      animation: slideIn 0.3s ease-in-out;
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

    @media (max-width: 768px) {
      .units-scroll {
        gap: 0.5rem;
      }
      
      .unit-card {
        min-width: 250px;
      }
      
      .commission-value {
        font-size: 1.25rem;
      }
    }
  </style>
</head>

<body>
  <div class="page page-center">

    @if ($userProjects->count() == 0)
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1000;">
      <form action="{{ route('logout') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-link" title="Keluar dari sistem">
          Logout
        </button>
      </form>
    </div>
    @else
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1000;">
      <a href="{{ route('affiliator.dashboard') }}" class="btn btn-link" title="Kembali ke dashboard">
        Dashboard
      </a>
    </div>
    @endif

    <form class="container container-tight py-4" id="join-project-form">
      <div class="text-center mb-4">
        <!-- BEGIN NAVBAR LOGO -->
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
        </div><!-- END NAVBAR LOGO -->
      </div>
      
      @include('components.alert')
      
      <!-- Welcome Card - Only show on step 1 -->
      <div id="welcome-card">
        <div class="text-center">
          <h1>Halo {{ strtoupper(Auth::user()->name) }}!</h1>
          <p class="text-secondary">
            @if($availableProjects->count() > 0)
              Silakan pilih project yang ingin Anda ikuti untuk memulai perjalanan sebagai affiliator
            @else
              Saat ini tidak ada project yang tersedia untuk diikuti
            @endif
          </p>
          
          @if($userProjects->count() > 0)
            <div class="alert alert-info text-start">
              <strong>Project Anda Saat Ini:</strong>
              <ul class="mb-0 mt-2">
                @foreach($userProjects as $userProject)
                  <li>
                    {{ $userProject['project']->name }} - 
                    <span class="badge bg-{{ $userProject['status'] === 'active' ? 'success' : 'warning' }}-lt">
                      {{ ucfirst($userProject['status']) }}
                    </span>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>
      </div>

      @if($availableProjects->count() > 0)
        <!-- Project Selection Card -->
        <div class="card card-md" id="project-selection-card">
          <div class="card-header">
            <h3 class="card-title">Pilih Project</h3>
            <div class="card-actions">
              <span class="badge bg-primary-lt">{{ $availableProjects->count() }} tersedia</span>
            </div>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                @foreach($availableProjects as $project)
                <label class="form-selectgroup-item flex-fill">
                  <input type="radio" name="selected_project" value="{{ $project['id'] }}" class="form-selectgroup-input" data-project-id="{{ $project['id'] }}" />
                  <div class="form-selectgroup-label d-flex align-items-center p-3">
                    <div class="me-3">
                      <span class="form-selectgroup-check"></span>
                    </div>

                    <div class="form-selectgroup-label-content d-flex align-items-center w-100">
                      <span class="avatar avatar-lg me-3" style="background-image: url('{{ $project['logo_url'] }}')"></span>
                      <div>
                        <div class="font-weight-medium">{{ strtoupper($project['name']) }}</div>
                        <div class="text-secondary small mb-1">{{ $project['location'] }}</div>
                        <div class="d-flex gap-2">
                          {{-- <div class="badge badge-sm bg-primary-lt">
                            Komisi tersedia
                          </div> --}}
                          @if($project['require_digital_signature'])
                            <div class="badge badge-sm bg-info-lt">
                              <i class="ti ti-writing"></i>
                              Tanda tangan digital
                            </div>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </label>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        <!-- Project Details Card -->
        <div class="card card-md" id="project-details-card" style="display: none;">
          <div class="card-header">
            <h3 class="card-title" id="project-details-title">Detail Project</h3>
          </div>
          <div class="card-body">
            <!-- Project Information -->
            <div class="row mb-4">
              <div class="col-12">
                <div class="commission-info-card card">
                  <div class="card-body">
                    <h4 class="mb-3 text-white">
                      <i class="ti ti-coin me-2"></i>
                      Informasi Komisi
                    </h4>
                    <div id="commission-info">
                      <div class="commission-value" id="commission-range">Loading...</div>
                      <div class="commission-desc" id="commission-description">Memuat informasi komisi...</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Units Information -->
            <div class="mb-4">
              <h4 class="mb-3">
                <i class="ti ti-building me-2"></i>
                Unit Tersedia
              </h4>
              <div class="units-scroll" id="units-container">
                <!-- Units will be loaded here -->
              </div>
            </div>

            <!-- Additional Info -->
            <div class="mb-4" id="additional-info-section" style="display: none;">
              <h4 class="mb-3">
                <i class="ti ti-info-circle me-2"></i>
                Informasi Tambahan
              </h4>
              <div class="card">
                <div class="card-body">
                  <div id="additional-info-content"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Terms and Conditions Card -->
        <div class="card card-md card-transition" id="terms-card" style="display: none;">
          <div class="card-header">
            <h3 class="card-title">
              <i class="ti ti-file-text me-2"></i>
              Syarat & Ketentuan
            </h3>
            <div class="card-subtitle ms-auto">Bacalah dengan teliti sebelum melanjutkan</div>
          </div>
          <div class="card-body">
            <div class="terms-content" id="terms-and-conditions">
              <!-- Terms content will be loaded here -->
            </div>
            
            <div class="mt-4">
              <label class="form-check">
                <input type="checkbox" class="form-check-input" id="terms-checkbox" name="terms" required>
                <span class="form-check-label">
                  <strong>Saya telah membaca dan menyetujui syarat & ketentuan project ini</strong>
                </span>
              </label>
            </div>
          </div>
        </div>

        <!-- KTP Card -->
        <div class="card card-md" id="ktp-card" style="display: none;">
          <div class="card-header">
            <h3 class="card-title">Data KTP</h3>
            <div class="card-subtitle">Lengkapi data KTP untuk verifikasi identitas</div>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Nomor KTP <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="ktp-number" name="ktp_number" required 
                     placeholder="Masukkan nomor KTP" maxlength="16" pattern="[0-9]{16}">
              <small class="form-hint">16 digit nomor KTP</small>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Foto KTP <span class="text-danger">*</span></label>
              <input type="file" class="form-control" id="ktp-photo" name="ktp_photo" accept="image/*" required>
              <small class="form-hint">Format: JPG, PNG. Maksimal 2MB</small>
              <div class="mt-2" id="ktp-preview"></div>
            </div>
          </div>
        </div>

        <!-- Digital Signature Card -->
        <div class="card card-md" id="signature-card" style="display: none;">
          <div class="card-header">
            <h3 class="card-title">Tanda Tangan Digital</h3>
            <div class="card-subtitle">Berikan tanda tangan digital sebagai komitmen bergabung</div>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <div class="signature position-relative">
                <div class="position-absolute top-0 end-0 p-2 z-index-1">
                  <div class="btn btn-icon btn-sm" id="signature-clear" title="Hapus tanda tangan" data-bs-toggle="tooltip">
                    <i class="ti ti-trash icon"></i>
                  </div>
                </div>
                <canvas id="signature-canvas" width="600" height="300" class="signature-canvas border rounded bg-white"></canvas>
              </div>
              
              <div class="text-secondary text-center mt-2">
                <small>
                  <i class="ti ti-info-circle me-1"></i>
                  Gunakan mouse/jari untuk menggambar tanda tangan
                </small>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Progress and Navigation -->
        <div class="row align-items-center mt-3">
          <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="progress" style="width: 100px;">
              <div class="progress-bar" id="progress-bar" style="width: 16%" role="progressbar" aria-valuenow="16" aria-valuemin="0" aria-valuemax="100">
              </div>
            </div>

            <div class="btn-list justify-content-end">
              <button type="button" class="btn btn-link link-secondary" id="back-btn" style="display: none;">
                Kembali
              </button>
              <button type="button" class="btn btn-primary" id="next-btn" disabled>
                Lanjut
              </button>
              <button type="submit" class="btn btn-primary" id="join-btn" style="display: none;">
                <i class="ti ti-user-plus me-1"></i>
                Bergabung Project
              </button>
            </div>
          </div>
        </div>

      @else
        <!-- No Projects Available -->
        <div class="card card-md">
          <div class="card-body text-center py-5">
            <div class="mb-3">
              <i class="ti ti-folder-off icon icon-xl text-muted"></i>
            </div>
            <h3>Tidak Ada Project Tersedia</h3>
            <p class="text-secondary">
              Saat ini tidak ada project yang dapat Anda ikuti. 
              Silakan hubungi admin atau coba lagi nanti.
            </p>
            <div class="mt-4">
              <a href="#" class="btn btn-outline-primary">
                <i class="ti ti-help me-1"></i>
                Hubungi Support
              </a>
              <a href="{{ route('affiliator.dashboard') }}" class="btn btn-primary">
                <i class="ti ti-arrow-left me-1"></i>
                Kembali
              </a>
            </div>
          </div>
        </div>
      @endif
    </form>
  </div>

  @include('partials.script')
  <script src="{{ asset('libs/signature_pad/dist/signature_pad.umd.min.js') }}" defer></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      let currentStep = 1;
      let selectedProjectId = null;
      let projectData = null;
      let signaturePad = null;
      let requireDigitalSignature = false; // **HIGHLIGHT: Added variable to track signature requirement**
      
      // DOM Elements
      const radioButtons = document.querySelectorAll('input[name="selected_project"]');
      const backButton = document.getElementById('back-btn');
      const nextButton = document.getElementById('next-btn');
      const joinButton = document.getElementById('join-btn');
      const welcomeCard = document.getElementById('welcome-card');
      const projectSelectionCard = document.getElementById('project-selection-card');
      const projectDetailsCard = document.getElementById('project-details-card');
      const termsCard = document.getElementById('terms-card');
      const ktpCard = document.getElementById('ktp-card');
      const signatureCard = document.getElementById('signature-card');
      const progressBar = document.getElementById('progress-bar');
      const termsCheckbox = document.getElementById('terms-checkbox');
      const ktpNumber = document.getElementById('ktp-number');
      const ktpPhoto = document.getElementById('ktp-photo');
      const ktpPreview = document.getElementById('ktp-preview');

      // **HIGHLIGHT: Updated steps configuration for proper completion step**
      function getStepsConfig() {
        if (requireDigitalSignature) {
          return [
            { progress: 14, showBack: false },  // Step 1: Project selection
            { progress: 28, showBack: true },   // Step 2: Project details
            { progress: 42, showBack: true },   // Step 3: Terms
            { progress: 56, showBack: true },   // Step 4: KTP
            { progress: 70, showBack: true },   // Step 5: Signature
            { progress: 85, showBack: true },   // Step 6: Completion
            { progress: 100, showBack: false } // Step 7: Complete
          ];
        } else {
          return [
            { progress: 16, showBack: false },  // Step 1: Project selection
            { progress: 33, showBack: true },   // Step 2: Project details
            { progress: 50, showBack: true },   // Step 3: Terms
            { progress: 66, showBack: true },   // Step 4: KTP
            { progress: 83, showBack: true },   // Step 5: Completion
            { progress: 100, showBack: false } // Step 6: Complete
          ];
        }
      }

      function updateProgress(step) {
        // **HIGHLIGHT: Use dynamic steps configuration**
        const steps = getStepsConfig();
        const stepData = steps[step - 1];
        progressBar.style.width = stepData.progress + '%';
        progressBar.setAttribute('aria-valuenow', stepData.progress);
        
        // Show/hide back button
        backButton.style.display = stepData.showBack ? 'inline-block' : 'none';
        
        // Show/hide welcome card - only show on step 1
        welcomeCard.style.display = step === 1 ? 'block' : 'none';
      }

      // Project selection handler
      radioButtons.forEach(radio => {
        radio.addEventListener('change', function () {
          if (this.checked) {
            selectedProjectId = this.value;
            nextButton.disabled = false;
          }
        });
      });

      // **HIGHLIGHT: Fixed back button handler**
      backButton.addEventListener('click', function() {
        console.log('Current step:', currentStep, 'requireDigitalSignature:', requireDigitalSignature);
        
        if (currentStep === 2) {
          // Back to project selection
          projectSelectionCard.style.display = 'block';
          projectDetailsCard.style.display = 'none';
          radioButtons.forEach(radio => {
            radio.checked = false;
          });
          selectedProjectId = null;
          currentStep = 1;
          updateProgress(currentStep);
          nextButton.disabled = true;
          document.getElementById('join-project-form').classList.add('container-tight');
          
        } else if (currentStep === 3) {
          // Back to project details
          projectDetailsCard.style.display = 'block';
          termsCard.style.display = 'none';
          currentStep = 2;
          updateProgress(currentStep);
          nextButton.disabled = false;
          
        } else if (currentStep === 4) {
          // Back to terms
          termsCard.style.display = 'block';
          ktpCard.style.display = 'none';
          currentStep = 3;
          updateProgress(currentStep);
          nextButton.disabled = !termsCheckbox.checked;
          
        } else if (currentStep === 5 && requireDigitalSignature) {
          // **HIGHLIGHT: Back from signature to KTP (when signature required)**
          ktpCard.style.display = 'block';
          signatureCard.style.display = 'none';
          currentStep = 4;
          updateProgress(currentStep);
          nextButton.style.display = 'inline-block';
          joinButton.style.display = 'none';
          validateKtpStep();
          
        } else if (currentStep === 5 && !requireDigitalSignature) {
          // **HIGHLIGHT: Back from completion to KTP (when no signature required)**
          hideCompletionMessage();
          ktpCard.style.display = 'block';
          currentStep = 4;
          updateProgress(currentStep);
          nextButton.style.display = 'inline-block';
          joinButton.style.display = 'none';
          validateKtpStep();
          
        } else if (currentStep === 6 && requireDigitalSignature) {
          // **HIGHLIGHT: Back from completion to signature (when signature required)**
          hideCompletionMessage();
          signatureCard.style.display = 'block';
          currentStep = 5;
          updateProgress(currentStep);
          nextButton.style.display = 'inline-block';
          joinButton.style.display = 'none';
          validateSignatureStep();
        }
      });

      // Next button handler
      nextButton.addEventListener('click', function() {
        if (currentStep === 1 && selectedProjectId) {
          loadProjectDetails(selectedProjectId);
        } else if (currentStep === 2) {
          showTermsStep();
        } else if (currentStep === 3 && termsCheckbox.checked) {
          showKtpStep();
        } else if (currentStep === 4 && validateKtpStep()) {
          // **HIGHLIGHT: Check if signature is required before showing signature step**
          if (requireDigitalSignature) {
            showSignature();
          } else {
            // Skip signature step and show completion
            showCompletionStep();
          }
        } else if (currentStep === 5 && requireDigitalSignature && validateSignatureStep()) {
          // **HIGHLIGHT: From signature to completion**
          showCompletionStep();
        }
      });

      // Terms checkbox handler
      if (termsCheckbox) {
        termsCheckbox.addEventListener('change', function() {
          if (currentStep === 3) {
            nextButton.disabled = !this.checked;
          }
        });
      }

      // KTP validation
      function validateKtpStep() {
        const ktpNumberValid = ktpNumber.value.length === 16 && /^\d+$/.test(ktpNumber.value);
        const ktpPhotoValid = ktpPhoto.files.length > 0;
        const isValid = ktpNumberValid && ktpPhotoValid;
        
        if (currentStep === 4) {
          nextButton.disabled = !isValid;
        }
        
        return isValid;
      }

      // **HIGHLIGHT: New signature validation function**
      function validateSignatureStep() {
        if (requireDigitalSignature && currentStep === 5) {
          const isValid = signaturePad && !signaturePad.isEmpty();
          nextButton.disabled = !isValid;
          return isValid;
        }
        return true;
      }

      // KTP number validation
      ktpNumber.addEventListener('input', function() {
        // Only allow numbers
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 16) {
          this.value = this.value.substring(0, 16);
        }
        validateKtpStep();
      });

      // KTP photo preview
      ktpPhoto.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          // Validate file size (2MB)
          if (file.size > 2 * 1024 * 1024) {
            showAlert('File terlalu besar. Maksimal 2MB.', 'danger');
            this.value = '';
            ktpPreview.innerHTML = '';
            validateKtpStep();
            return;
          }

          // Validate file type
          if (!file.type.startsWith('image/')) {
            showAlert('Pilih file gambar yang valid.', 'danger');
            this.value = '';
            ktpPreview.innerHTML = '';
            validateKtpStep();
            return;
          }

          // Show preview
          const reader = new FileReader();
          reader.onload = function(e) {
            ktpPreview.innerHTML = `
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
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearKtpPreview()">
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
          ktpPreview.innerHTML = '';
        }
        validateKtpStep();
      });

      // Clear KTP preview
      window.clearKtpPreview = function() {
        ktpPhoto.value = '';
        ktpPreview.innerHTML = '';
        validateKtpStep();
      };

      // Load project details
      function loadProjectDetails(projectId) {
        fetch(`/ajax/project/${projectId}/details`, {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            showAlert(data.error, 'danger');
            return;
          }
          
          projectData = data;
          // **HIGHLIGHT: Set signature requirement based on project settings**
          requireDigitalSignature = data.require_digital_signature;
          
          displayProjectDetails(data);
          currentStep = 2;
          updateProgress(currentStep);

          projectSelectionCard.style.display = 'none';
          projectDetailsCard.style.display = 'block';
          document.getElementById('join-project-form').classList.remove('container-tight');
        })
        .catch(error => {
          console.error('Error:', error);
          showAlert('Gagal memuat detail project', 'danger');
        });
      }

      // Display project details
      function displayProjectDetails(data) {
        document.getElementById('project-details-title').textContent = `Detail Project - ${data.name}`;
        
        // Commission info
        document.getElementById('commission-range').textContent = data.commission_range || 'Komisi Bervariasi';
        document.getElementById('commission-description').textContent = data.commission_description || 'Komisi disesuaikan berdasarkan unit yang dipilih';

        // Units
        if (data.units && data.units.length > 0) {
          displayUnits(data.units);
        } else {
          document.getElementById('units-container').innerHTML = `
            <div class="text-center p-4">
              <i class="ti ti-building-off icon icon-lg text-muted mb-2"></i>
              <p class="text-muted">Belum ada unit tersedia untuk project ini</p>
            </div>
          `;
        }

        // Additional info
        if (data.additional_info) {
          document.getElementById('additional-info-section').style.display = 'block';
          document.getElementById('additional-info-content').innerHTML = data.additional_info.replace(/\n/g, '<br>');
        }

        projectDetailsCard.classList.add('card-transition');
        projectDetailsCard.scrollIntoView({ behavior: 'smooth' });
      }

      // Display units
      function displayUnits(units) {
        const unitsHtml = units.map(unit => `
          <div class="unit-card card h-100">
            ${unit.image ? `<img src="${unit.image}" class="card-img-top" style="height: 150px; object-fit: cover;">` : ''}
            <div class="card-body">
              <h5 class="card-title">${unit.name}</h5>
              ${unit.description ? `<p class="card-text text-muted small">${unit.description}</p>` : ''}
              
              <div class="row mb-2">
                <div class="col-6">
                  <div class="text-muted small">Harga</div>
                  <div class="fw-bold">${unit.price_formatted}</div>
                </div>
                <div class="col-6">
                  <div class="text-muted small">Komisi</div>
                  <div class="fw-bold text-success">${unit.commission_display}</div>
                </div>
              </div>
              
              ${unit.specs ? `
                <div class="mt-2">
                  <small class="text-muted">${unit.specs}</small>
                </div>
              ` : ''}
            </div>
          </div>
        `).join('');
        
        document.getElementById('units-container').innerHTML = unitsHtml;
      }

      // Show terms step
      function showTermsStep() {
        currentStep = 3;
        updateProgress(currentStep);
        
        projectDetailsCard.style.display = 'none';
        termsCard.style.display = 'block';
        termsCard.classList.add('card-transition');
        
        // Load terms
        document.getElementById('terms-and-conditions').innerHTML = projectData.terms_and_conditions.replace(/\n/g, '<br>');
        
        termsCard.scrollIntoView({ behavior: 'smooth' });
        nextButton.disabled = !termsCheckbox.checked;
      }

      // Show KTP step
      function showKtpStep() {
        currentStep = 4;
        updateProgress(currentStep);
        
        termsCard.style.display = 'none';
        ktpCard.style.display = 'block';
        document.getElementById('join-project-form').classList.add('container-tight');
        ktpCard.scrollIntoView({ behavior: 'smooth' });
        
        validateKtpStep();
      }

      // Show signature step
      function showSignature() {
        currentStep = 5;
        updateProgress(currentStep);

        // **HIGHLIGHT: Keep next button visible**
        nextButton.style.display = 'inline-block';
        joinButton.style.display = 'none';

        ktpCard.style.display = 'none';
        signatureCard.style.display = 'block';
        signatureCard.scrollIntoView({ behavior: 'smooth' });
        
        initializeSignaturePad();
        validateSignatureStep();
      }

      // **HIGHLIGHT: New completion step function**
      function showCompletionStep() {
        // **HIGHLIGHT: Set step based on signature requirement**
        currentStep = requireDigitalSignature ? 6 : 5;
        updateProgress(currentStep);

        nextButton.style.display = 'none';
        joinButton.style.display = 'inline-block';
        joinButton.disabled = false;

        // Hide previous cards
        if (requireDigitalSignature) {
          signatureCard.style.display = 'none';
        } else {
          ktpCard.style.display = 'none';
        }
        
        showCompletionMessage();
      }

      // **HIGHLIGHT: Enhanced completion message function**
      function showCompletionMessage() {
        // **HIGHLIGHT: Dynamic content based on signature requirement**
        const signatureStatus = requireDigitalSignature ? 
          '<div class="text-success small"><i class="ti ti-check me-1"></i>Tanda tangan digital tersimpan</div>' :
          '<div class="text-info small"><i class="ti ti-info-circle me-1"></i>Project ini tidak memerlukan tanda tangan digital</div>';

        const completionHtml = `
          <div class="card card-md card-transition" id="completion-card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="ti ti-check-circle me-2"></i>
                Siap Bergabung
              </h3>
              <div class="card-subtitle">Semua data telah lengkap</div>
            </div>
            <div class="card-body text-center">
              <div class="mb-3">
                <i class="ti ti-circle-check icon icon-xl text-success"></i>
              </div>
              <h4>Data Anda Sudah Lengkap!</h4>
              <p class="text-secondary">
                Semua persyaratan telah terpenuhi. Klik tombol "Bergabung Project" 
                untuk menyelesaikan proses bergabung dengan project ${projectData.name}.
              </p>
              
              <div class="mt-4">
                <div class="row mb-3">
                  <div class="col-6">
                    <div class="text-muted small">Project</div>
                    <div class="fw-bold">${projectData.name}</div>
                  </div>
                  <div class="col-6">
                    <div class="text-muted small">Komisi</div>
                    <div class="fw-bold text-success">${projectData.commission_range}</div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-6">
                    <div class="text-muted small">Status KTP</div>
                    <div class="text-warning small"><i class="ti ti-clock me-1"></i>Menunggu verifikasi</div>
                  </div>
                  <div class="col-6">
                    <div class="text-muted small">Tanda Tangan</div>
                    ${signatureStatus}
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;
        
        // **HIGHLIGHT: Remove existing completion card first**
        hideCompletionMessage();
        
        // Insert completion card before navigation
        const form = document.getElementById('join-project-form');
        const navigation = form.querySelector('.row.align-items-center.mt-3');
        navigation.insertAdjacentHTML('beforebegin', completionHtml);
        
        // Scroll to completion card
        setTimeout(() => {
          const completionCard = document.getElementById('completion-card');
          if (completionCard) {
            completionCard.scrollIntoView({ behavior: 'smooth' });
          }
        }, 100);
      }

      // **HIGHLIGHT: Function to hide completion message**
      function hideCompletionMessage() {
        const completionCard = document.getElementById('completion-card');
        if (completionCard) {
          completionCard.remove();
        }
      }

      // Initialize signature pad
      function initializeSignaturePad() {
        const canvas = document.getElementById("signature-canvas");
        if (canvas && !signaturePad) {
          signaturePad = new SignaturePad(canvas, {
            backgroundColor: "rgba(255,255,255,0)",
            penColor: getComputedStyle(canvas).color || "#000000",
            minWidth: 1,
            maxWidth: 3
          });

          document.getElementById("signature-clear").addEventListener("click", function () {
            signaturePad.clear();
            validateSignatureStep();
          });

          // **HIGHLIGHT: Only validate, don't auto-show completion**
          signaturePad.addEventListener("afterUpdateStroke", function() {
            validateSignatureStep();
          });

          function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
          }
          
          window.addEventListener("resize", resizeCanvas);
          resizeCanvas();

          validateSignatureStep();
        }
      }

      // Form submission
      document.getElementById('join-project-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!selectedProjectId) {
          showAlert('Pilih project terlebih dahulu', 'warning');
          return;
        }

        if (!termsCheckbox.checked) {
          showAlert('Anda harus menyetujui syarat & ketentuan', 'warning');
          return;
        }

        if (!validateKtpStep()) {
          showAlert('Lengkapi data KTP', 'warning');
          return;
        }

        // **HIGHLIGHT: Only validate signature if required**
        if (requireDigitalSignature && (!signaturePad || signaturePad.isEmpty())) {
          showAlert('Berikan tanda tangan digital', 'warning');
          return;
        }

        joinButton.disabled = true;
        joinButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

        const formData = new FormData();
        formData.append('project_id', selectedProjectId);
        formData.append('ktp_number', ktpNumber.value);
        formData.append('ktp_photo', ktpPhoto.files[0]);
        // **HIGHLIGHT: Only append signature if required**
        if (requireDigitalSignature && signaturePad) {
          formData.append('digital_signature', signaturePad.toSVG());
        }
        formData.append('terms_accepted', '1');

        fetch('{{ route("affiliator.project.join.store") }}', {
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
            showAlert(data.message, 'success');
            // **HIGHLIGHT: Use appropriate final step based on signature requirement**
            currentStep = requireDigitalSignature ? 7 : 6;
            updateProgress(currentStep);
            
            setTimeout(() => {
              if (data.redirect_url) {
                window.location.href = data.redirect_url;
              } else {
                window.location.href = '{{ route("affiliator.dashboard") }}';
              }
            }, 2000);
          } else {
            showAlert(data.message || 'Terjadi kesalahan', 'danger');
            joinButton.disabled = false;
            joinButton.innerHTML = '<i class="ti ti-user-plus me-1"></i>Bergabung Project';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showAlert('Terjadi kesalahan saat bergabung project', 'danger');
          joinButton.disabled = false;
          joinButton.innerHTML = '<i class="ti ti-user-plus me-1"></i>Bergabung Project';
        });
      });

      // Alert helper function
      function showAlert(message, type = 'info') {
        const alertContainer = document.querySelector('.container-tight');
        const alertHtml = `
          <div class="alert alert-${type} alert-dismissible" role="alert">
            <div class="d-flex">
              <div>
                <i class="ti ti-${type === 'success' ? 'check' : type === 'danger' ? 'x' : 'info-circle'} me-2"></i>
              </div>
              <div>${message}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
          </div>
        `;
        
        const existingAlerts = alertContainer.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        alertContainer.insertAdjacentHTML('afterbegin', alertHtml);
        
        setTimeout(() => {
          const newAlert = alertContainer.querySelector('.alert');
          if (newAlert) {
            newAlert.remove();
          }
        }, 5000);
      }

      // Initialize tooltips
      if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl);
        });
      }

      // Initial state
      updateProgress(1);
    });
</script>

</body>
</html>