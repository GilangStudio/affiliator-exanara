<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Join Project - {{ config('app.name') }}</title>
  @include('partials.style')
</head>

<body>
  <div class="page page-center">
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
                          <div class="badge badge-sm bg-primary-lt">
                            {{ $project['commission_display'] }}
                          </div>
                          @if($project['require_digital_signature'])
                            <div class="badge badge-sm bg-info-lt">
                              <i class="ti ti-writing"></i>
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
                <div class="card bg-light">
                  <div class="card-body">
                    <h4>Informasi Komisi</h4>
                    <div id="commission-info"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="mb-4">
              <h4>Syarat & Ketentuan</h4>
              <div class="card">
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                  <div class="markdown" id="terms-and-conditions"></div>
                </div>
              </div>
              
              <div class="mt-3">
                <label class="form-check">
                  <input type="checkbox" class="form-check-input" id="terms-checkbox" name="terms" required>
                  <span class="form-check-label">
                    Saya telah membaca dan menyetujui syarat & ketentuan project ini
                  </span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- KTP Card -->
        <div class="card card-md" id="ktp-card" style="display: none;">
          <div class="card-header">
            <h3 class="card-title">Data KTP</h3>
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
          </div>
          <div class="card-body">
            <div class="mb-3">
              <p class="text-secondary text-center">
                Berikan tanda tangan digital Anda sebagai komitmen bergabung dengan project ini
              </p>
              
              <div class="signature position-relative">
                <div class="position-absolute top-0 end-0 p-2">
                  <div class="btn btn-icon" id="signature-clear" title="Hapus tanda tangan" data-bs-toggle="tooltip">
                    <i class="ti ti-trash icon"></i>
                  </div>
                </div>
                <canvas id="signature-canvas" width="600" height="300" class="signature-canvas border rounded"></canvas>
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
          <div class="col-4">
            <div class="progress">
              <div class="progress-bar" id="progress-bar" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
              </div>
            </div>
          </div>
          <div class="col">
            <div class="btn-list justify-content-end">
              <button type="button" class="btn btn-link link-secondary" id="back-btn" style="display: none;">
                Kembali
              </button>
              <button type="button" class="btn btn-primary" id="next-btn" disabled>
                Lanjut
              </button>
              <button type="submit" class="btn btn-success" id="join-btn" style="display: none;">
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
              <a href="{{ route('support.index') }}" class="btn btn-outline-primary">
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
      
      // DOM Elements
      const radioButtons = document.querySelectorAll('input[name="selected_project"]');
      const backButton = document.getElementById('back-btn');
      const nextButton = document.getElementById('next-btn');
      const joinButton = document.getElementById('join-btn');
      const welcomeCard = document.getElementById('welcome-card');
      const projectSelectionCard = document.getElementById('project-selection-card');
      const projectDetailsCard = document.getElementById('project-details-card');
      const ktpCard = document.getElementById('ktp-card');
      const signatureCard = document.getElementById('signature-card');
      const progressBar = document.getElementById('progress-bar');
      const termsCheckbox = document.getElementById('terms-checkbox');
      const ktpNumber = document.getElementById('ktp-number');
      const ktpPhoto = document.getElementById('ktp-photo');
      const ktpPreview = document.getElementById('ktp-preview');

      // Steps configuration
      const steps = [
        { progress: 20, showBack: false },  // Step 1: Project selection
        { progress: 40, showBack: true },   // Step 2: Terms
        { progress: 60, showBack: true },   // Step 3: KTP
        { progress: 80, showBack: true },   // Step 4: Signature
        { progress: 100, showBack: false }  // Step 5: Complete
      ];

      function updateProgress(step) {
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

      // Back button handler
      backButton.addEventListener('click', function() {
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
          // Back to terms
          projectDetailsCard.style.display = 'block';
          ktpCard.style.display = 'none';
          currentStep = 2;
          updateProgress(currentStep);
          nextButton.style.display = 'inline-block';
          nextButton.disabled = !termsCheckbox.checked;
          document.getElementById('join-project-form').classList.remove('container-tight');
        } else if (currentStep === 4) {
          // Back to KTP
          ktpCard.style.display = 'block';
          signatureCard.style.display = 'none';
          currentStep = 3;
          updateProgress(currentStep);
          nextButton.style.display = 'inline-block';
          joinButton.style.display = 'none';
          validateKtpStep();
        }
      });

      // Next button handler
      nextButton.addEventListener('click', function() {
        if (currentStep === 1 && selectedProjectId) {
          loadProjectDetailsAndTerms(selectedProjectId);
        } else if (currentStep === 2 && termsCheckbox.checked) {
          showKtpStep();
        } else if (currentStep === 3 && validateKtpStep()) {
          showSignature();
        }
      });

      // Terms checkbox handler
      if (termsCheckbox) {
        termsCheckbox.addEventListener('change', function() {
          if (currentStep === 2) {
            nextButton.disabled = !this.checked;
          }
        });
      }

      // KTP validation
      function validateKtpStep() {
        const ktpNumberValid = ktpNumber.value.length === 16 && /^\d+$/.test(ktpNumber.value);
        const ktpPhotoValid = ktpPhoto.files.length > 0;
        const isValid = ktpNumberValid && ktpPhotoValid;
        
        if (currentStep === 3) {
          nextButton.disabled = !isValid;
        }
        
        return isValid;
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
      function loadProjectDetailsAndTerms(projectId) {
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
          displayProjectDetails(data);
          currentStep = 2;
          updateProgress(currentStep);
          nextButton.disabled = true;

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
        
        document.getElementById('commission-info').innerHTML = `
          <div class="row">
            <div class="col-12 mb-2">
              <div class="text-success h4">${data.commission_info.display}</div>
              <div class="text-muted small">${data.commission_info.description}</div>
            </div>
          </div>
        `;

        document.getElementById('terms-and-conditions').innerHTML = data.terms_and_conditions.replace(/\n/g, '<br>');
        projectDetailsCard.scrollIntoView({ behavior: 'smooth' });
      }

      // Show KTP step
      function showKtpStep() {
        currentStep = 3;
        updateProgress(currentStep);
        
        projectDetailsCard.style.display = 'none';
        ktpCard.style.display = 'block';
        document.getElementById('join-project-form').classList.add('container-tight');
        ktpCard.scrollIntoView({ behavior: 'smooth' });
        
        validateKtpStep();
      }

      // Show signature step
      function showSignature() {
        currentStep = 4;
        updateProgress(currentStep);

        nextButton.style.display = 'none';
        joinButton.style.display = 'inline-block';

        ktpCard.style.display = 'none';
        signatureCard.style.display = 'block';
        signatureCard.scrollIntoView({ behavior: 'smooth' });
        
        initializeSignaturePad();
      }

      // Initialize signature pad
      function initializeSignaturePad() {
        const canvas = document.getElementById("signature-canvas");
        if (canvas && !signaturePad) {
          signaturePad = new SignaturePad(canvas, {
            backgroundColor: "transparent",
            penColor: getComputedStyle(canvas).color || "#000000",
            minWidth: 1,
            maxWidth: 3
          });

          document.getElementById("signature-clear").addEventListener("click", function () {
            signaturePad.clear();
            updateJoinButton();
          });

          signaturePad.addEventListener("afterUpdateStroke", updateJoinButton);

          function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
          }
          
          window.addEventListener("resize", resizeCanvas);
          resizeCanvas();
        }
      }

      // Update join button state
      function updateJoinButton() {
        if (signaturePad && !signaturePad.isEmpty()) {
          joinButton.disabled = false;
        } else {
          joinButton.disabled = true;
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

        if (!signaturePad || signaturePad.isEmpty()) {
          showAlert('Berikan tanda tangan digital', 'warning');
          return;
        }

        joinButton.disabled = true;
        joinButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

        const formData = new FormData();
        formData.append('project_id', selectedProjectId);
        formData.append('ktp_number', ktpNumber.value);
        formData.append('ktp_photo', ktpPhoto.files[0]);
        formData.append('digital_signature', signaturePad.toSVG());
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
            currentStep = 5;
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