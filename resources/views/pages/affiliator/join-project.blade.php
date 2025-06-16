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
      flex-shrink: 0;
    }

    .commission-info-card {
      background: #000;
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
      min-height: 400px;
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

    /* Filter & Search Styles */
    .filter-section {
      background: #f8f9fa;
      border-radius: 0.5rem;
      padding: 1rem;
      margin-bottom: 1.5rem;
    }

    .filter-toggle {
      cursor: pointer;
      border: none;
      background: none;
      color: #495057;
      font-weight: 500;
    }

    .filter-toggle:hover {
      color: var(--tblr-primary);
    }

    .filter-content {
      display: none;
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid #dee2e6;
    }

    .filter-content.show {
      display: block;
    }

    .project-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .project-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border: 1px solid #e9ecef;
      border-radius: 0.5rem;
      overflow: hidden;
    }

    .project-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .loading-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255,255,255,0.8);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(2px);
    }

    .loading-overlay.show {
      display: flex;
    }

    .loading-skeleton {
      animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {
      0% {
        opacity: 1;
      }
      50% {
        opacity: 0.5;
      }
      100% {
        opacity: 1;
      }
    }

    .pagination-info {
      color: #6c757d;
      font-size: 0.875rem;
      margin-bottom: 1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .no-results {
      text-align: center;
      padding: 3rem;
      color: #6c757d;
      grid-column: 1 / -1;
    }

    @media (max-width: 768px) {
      .project-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
      
      .filter-content {
        flex-direction: column;
      }
      
      .filter-content .col-md-3,
      .filter-content .col-md-4,
      .filter-content .col-md-2 {
        margin-bottom: 1rem;
      }

      .pagination-info {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
      }

      .quick-filters {
        justify-content: center;
      }
    }
  </style>
</head>

<body>
  <div class="page page-center">

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

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

    <form class="container py-4" id="join-project-form">
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
        </div>
      </div>

      @if($availableProjects->count() > 0)
        <!-- Project Selection Card -->
        <div class="card card-md" id="project-selection-card">
          <div class="card-header">
            <h3 class="card-title">Pilih Project</h3>
            <div class="card-actions">
              <span class="badge bg-primary-lt" id="project-count">{{ $availableProjects->total() }} tersedia</span>
            </div>
          </div>
          <div class="card-body">
            
            <!-- Search & Filter Section -->
            <div class="filter-section">
              <div class="d-flex align-items-center justify-content-between">
                <button type="button" class="filter-toggle d-flex align-items-center" id="filter-toggle">
                  <i class="ti ti-filter me-2"></i>
                  <span>Filter & Pencarian</span>
                  <i class="ti ti-chevron-down ms-2" id="filter-chevron"></i>
                </button>
                <div class="d-flex gap-2">
                  <span class="badge bg-primary-lt" id="active-filters-count" style="display: none;">0 Filter Aktif</span>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="reset-filters">
                    <i class="ti ti-refresh me-1"></i>
                    Reset
                  </button>
                </div>
              </div>

              <!-- Active Filters Display -->
              <div id="active-filters" class="mt-2" style="display: none;">
                <div class="d-flex flex-wrap gap-1"></div>
              </div>

              <div class="filter-content" id="filter-content">
                <div class="row g-3">
                  <!-- Search -->
                  <div class="col-md-4">
                    <label class="form-label">Cari Project</label>
                    <div class="input-icon">
                      <input type="text" value="" class="form-control"  id="search-input" placeholder="Nama project, developer, lokasi...">
                      <span class="input-icon-addon">
                        <i class="ti ti-search icon icon-1"></i>
                      </span>
                    </div>
                  </div>
                  
                  <!-- Location Filter -->
                  <div class="col-md-3">
                    <label class="form-label">Lokasi</label>
                    <select class="form-select" id="location-filter">
                      <option value="">Semua Lokasi</option>
                      @foreach($locations as $location)
                        <option value="{{ $location }}">{{ $location }}</option>
                      @endforeach
                    </select>
                  </div>
                  
                  <!-- Commission Type Filter -->
                  <div class="col-md-3">
                    <label class="form-label">Tipe Komisi</label>
                    <select class="form-select" id="commission-type-filter">
                      <option value="">Semua Tipe</option>
                      @foreach($commissionTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                      @endforeach
                    </select>
                  </div>
                  
                  <!-- Commission Range -->
                  <div class="col-md-2">
                    <label class="form-label">Komisi Min</label>
                    <input type="number" class="form-control" id="min-commission" placeholder="Min" step="0.1">
                  </div>
                </div>
              </div>
            </div>

            <!-- Results Info -->
            <div class="pagination-info" id="pagination-info">
              <span>Menampilkan {{ $availableProjects->firstItem() ?? 0 }} - {{ $availableProjects->lastItem() ?? 0 }} 
              dari {{ $availableProjects->total() }} project</span>
              <div class="d-flex gap-2">
                <select class="form-select form-select-sm" id="per-page-select" style="width: auto;">
                  <option value="12">12 per halaman</option>
                  <option value="24">24 per halaman</option>
                  <option value="48">48 per halaman</option>
                </select>
                <select class="form-select form-select-sm" id="sort-select" style="width: auto;">
                  <option value="name">Urutkan: Nama A-Z</option>
                  <option value="name_desc">Urutkan: Nama Z-A</option>
                  <option value="newest">Terbaru</option>
                  <option value="oldest">Terlama</option>
                </select>
              </div>
            </div>

            <!-- Projects Grid -->
            <div class="project-grid" id="projects-container" style="max-height: 500px; overflow-y: auto;">
              @forelse($availableProjects as $project)
              <div class="project-card card" data-project-id="{{ $project['id'] }}">
                <label class="form-imagecheck mb-0 h-100">
                  <input type="radio" 
                          name="selected_project" 
                          value="{{ $project['id'] }}" 
                          class="form-imagecheck-input" 
                          data-project-id="{{ $project['id'] }}" 
                          @if(!empty($autoSelectProjectSlug) && $project['slug'] === $autoSelectProjectSlug) checked @endif />
                  <span class="form-imagecheck-figure h-100 d-flex flex-column">
                    <!-- Project Image -->
                    <div class="img-responsive img-responsive-16x9" style="background-image: url('{{ $project['logo_url'] }}'); background-size: cover; background-position: center;"></div>
                    
                    <!-- Project Info -->
                    <div class="card-body d-flex flex-column flex-grow-1">
                      <h4 class="card-title mb-2">{{ $project['name'] }}</h4>
                      
                      @if($project['developer_name'])
                        <div class="text-muted small mb-1">
                          <i class="ti ti-building me-1"></i>
                          {{ $project['developer_name'] }}
                        </div>
                      @endif
                      
                      <div class="text-muted small mb-2">
                        <i class="ti ti-map-pin me-1"></i>
                        {{ $project['location'] }}
                      </div>
                      
                      <div class="mt-auto">
                        <div class="d-flex gap-1 flex-wrap mb-2">
                          <div class="badge badge-sm bg-primary-lt">
                            {{ $project['commission_preview'] }}
                          </div>
                          @if($project['require_digital_signature'])
                            <div class="badge badge-sm bg-info-lt">
                              <i class="ti ti-writing"></i>
                              TTD Digital
                            </div>
                          @endif
                          <div class="badge badge-sm bg-secondary-lt">
                            {{ $project['units_count'] }} Unit
                          </div>
                        </div>
                        
                        {{-- @if($project['description'])
                        <p class="text-muted small mb-0" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                          {{ strip_tags($project['description']) }}
                        </p>
                        @endif --}}
                      </div>
                    </div>
                  </span>
                </label>
              </div>
              @empty
                <div class="no-results">
                  <i class="ti ti-search-off icon icon-xl text-muted mb-3"></i>
                  <h4>Tidak Ada Project Ditemukan</h4>
                  <p class="text-muted">Coba ubah kriteria pencarian atau filter Anda</p>
                </div>
              @endforelse
            </div>

            <!-- Pagination -->
            @if($availableProjects->hasPages())
            <div class="d-flex justify-content-center">
              <nav aria-label="Project pagination">
                <ul class="pagination pagination-sm" id="pagination-container">
                  {{-- Previous Page Link --}}
                  @if ($availableProjects->onFirstPage())
                    <li class="page-item disabled">
                      <span class="page-link"><i class="ti ti-chevron-left"></i></span>
                    </li>
                  @else
                    <li class="page-item">
                      <a class="page-link" href="javascript:void(0)" data-page="{{ $availableProjects->currentPage() - 1 }}">
                        <i class="ti ti-chevron-left"></i>
                      </a>
                    </li>
                  @endif

                  {{-- Page Number Links --}}
                  @php
                    $currentPage = $availableProjects->currentPage();
                    $lastPage = $availableProjects->lastPage();
                    $start = max(1, $currentPage - 2);
                    $end = min($lastPage, $currentPage + 2);
                  @endphp

                  @if($start > 1)
                    <li class="page-item">
                      <a class="page-link" href="javascript:void(0)" data-page="1">1</a>
                    </li>
                    @if($start > 2)
                      <li class="page-item disabled">
                        <span class="page-link">...</span>
                      </li>
                    @endif
                  @endif

                  @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $currentPage)
                      <li class="page-item active">
                        <span class="page-link">{{ $page }}</span>
                      </li>
                    @else
                      <li class="page-item">
                        <a class="page-link" href="javascript:void(0)" data-page="{{ $page }}">{{ $page }}</a>
                      </li>
                    @endif
                  @endfor

                  @if($end < $lastPage)
                    @if($end < $lastPage - 1)
                      <li class="page-item disabled">
                        <span class="page-link">...</span>
                      </li>
                    @endif
                    <li class="page-item">
                      <a class="page-link" href="javascript:void(0)" data-page="{{ $lastPage }}">{{ $lastPage }}</a>
                    </li>
                  @endif

                  {{-- Next Page Link --}}
                  @if ($availableProjects->hasMorePages())
                    <li class="page-item">
                      <a class="page-link" href="javascript:void(0)" data-page="{{ $availableProjects->currentPage() + 1 }}">
                        <i class="ti ti-chevron-right"></i>
                      </a>
                    </li>
                  @else
                    <li class="page-item disabled">
                      <span class="page-link"><i class="ti ti-chevron-right"></i></span>
                    </li>
                  @endif
                </ul>
              </nav>
            </div>
            @endif
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
  @include('components.toast')
  @include('components.scripts.global')
  <script src="{{ asset('libs/signature_pad/dist/signature_pad.umd.min.js') }}" defer></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      let currentStep = 1;
      let selectedProjectId = null;
      let projectData = null;
      let signaturePad = null;
      let requireDigitalSignature = false;
      let currentPage = {{ $availableProjects->currentPage() ?? 1 }};
      let totalPages = {{ $availableProjects->lastPage() ?? 1 }};
      let autoSelectProjectSlug = @json($autoSelectProjectSlug ?? null);
      
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
      const loadingOverlay = document.getElementById('loading-overlay');

      // Filter Elements
      const filterToggle = document.getElementById('filter-toggle');
      const filterContent = document.getElementById('filter-content');
      const filterChevron = document.getElementById('filter-chevron');
      const searchInput = document.getElementById('search-input');
      const locationFilter = document.getElementById('location-filter');
      const commissionTypeFilter = document.getElementById('commission-type-filter');
      const minCommissionInput = document.getElementById('min-commission');
      const resetFiltersBtn = document.getElementById('reset-filters');
      const projectsContainer = document.getElementById('projects-container');
      const paginationInfo = document.getElementById('pagination-info');
      const paginationContainer = document.getElementById('pagination-container');
      const projectCount = document.getElementById('project-count');
      const activeFiltersCount = document.getElementById('active-filters-count');
      const activeFiltersContainer = document.getElementById('active-filters');
      const quickFilterBtns = document.querySelectorAll('.quick-filter-btn');
      const perPageSelect = document.getElementById('per-page-select');
      const sortSelect = document.getElementById('sort-select');

      // Filter Toggle
      filterToggle.addEventListener('click', function() {
        const isVisible = filterContent.classList.contains('show');
        filterContent.classList.toggle('show');
        filterChevron.classList.toggle('ti-chevron-down', isVisible);
        filterChevron.classList.toggle('ti-chevron-up', !isVisible);
      });

      // Search & Filter Functions
      function showLoading() {
        loadingOverlay.classList.add('show');
        projectsContainer.style.opacity = '0.5';
        projectsContainer.style.pointerEvents = 'none';
      }

      function hideLoading() {
        loadingOverlay.classList.remove('show');
        projectsContainer.style.opacity = '1';
        projectsContainer.style.pointerEvents = 'auto';
      }

      function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
          const later = () => {
            clearTimeout(timeout);
            func(...args);
          };
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
        };
      }

      function getFilterParams() {
        const params = {
          search: searchInput.value.trim(),
          location: locationFilter.value,
          commission_type: commissionTypeFilter.value,
          min_commission: minCommissionInput.value,
          per_page: perPageSelect?.value || 12,
          sort: sortSelect?.value || 'name',
          page: currentPage
        };

        // ← BARU: Add auto_select_project if it exists
        if (autoSelectProjectSlug) {
          params.auto_select_project = autoSelectProjectSlug;
        }

        return params;
      }

      function updateActiveFilters() {
        const filters = [];
        let activeCount = 0;

        if (searchInput.value.trim()) {
          filters.push({
            label: `Pencarian: "${searchInput.value.trim()}"`,
            key: 'search',
            value: searchInput.value.trim()
          });
          activeCount++;
        }

        if (locationFilter.value) {
          filters.push({
            label: `Lokasi: ${locationFilter.value}`,
            key: 'location',
            value: locationFilter.value
          });
          activeCount++;
        }

        if (commissionTypeFilter.value) {
          const typeLabel = commissionTypeFilter.selectedOptions[0].text;
          filters.push({
            label: `Tipe: ${typeLabel}`,
            key: 'commission_type',
            value: commissionTypeFilter.value
          });
          activeCount++;
        }

        if (minCommissionInput.value) {
          filters.push({
            label: `Min Komisi: ${minCommissionInput.value}`,
            key: 'min_commission',
            value: minCommissionInput.value
          });
          activeCount++;
        }

        // Update active filters display
        if (activeCount > 0) {
          activeFiltersCount.textContent = `${activeCount} Filter Aktif`;
          activeFiltersCount.style.display = 'inline-block';
          activeFiltersContainer.style.display = 'block';

          const filtersHtml = filters.map(filter => `
            <span class="filter-badge">
              ${filter.label}
              <button type="button" class="btn-close" data-filter-key="${filter.key}" aria-label="Remove filter"></button>
            </span>
          `).join('');

          activeFiltersContainer.querySelector('.d-flex').innerHTML = filtersHtml;

          // Add event listeners to remove buttons
          activeFiltersContainer.querySelectorAll('.btn-close').forEach(btn => {
            btn.addEventListener('click', function() {
              const filterKey = this.getAttribute('data-filter-key');
              removeFilter(filterKey);
            });
          });
        } else {
          activeFiltersCount.style.display = 'none';
          activeFiltersContainer.style.display = 'none';
        }
      }

      function removeFilter(filterKey) {
        switch(filterKey) {
          case 'search':
            searchInput.value = '';
            break;
          case 'location':
            locationFilter.value = '';
            break;
          case 'commission_type':
            commissionTypeFilter.value = '';
            break;
          case 'min_commission':
            minCommissionInput.value = '';
            break;
        }
        currentPage = 1;
        updateActiveFilters();
        loadProjects(1);
      }

      function loadProjects(page = 1) {
        showLoading();
        currentPage = page;
        
        const params = new URLSearchParams(getFilterParams());
        
        fetch(`/ajax/projects/available?${params.toString()}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          updateProjectsDisplay(data);
          updatePagination(data.pagination);
          hideLoading();
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Gagal memuat data project', 'danger');
          hideLoading();
        });
      }

      function updateProjectsDisplay(data) {
        if (data.data && data.data.length > 0) {
          let html = '';
          data.data.forEach(project => {

            const isAutoSelected = autoSelectProjectSlug && project.slug === autoSelectProjectSlug;
            const autoSelectedClass = isAutoSelected ? ' auto-selected' : '';

            html += `
              <div class="project-card card" data-project-id="${project.id}">
                <label class="form-imagecheck mb-0 h-100">
                  <input type="radio" 
                          name="selected_project" 
                          value="${project.id}" 
                          class="form-imagecheck-input" 
                          data-project-id="${project.id}" />
                  <span class="form-imagecheck-figure h-100 d-flex flex-column">
                    <div class="img-responsive img-responsive-16x9" style="background-image: url('${project.logo_url}'); background-size: cover; background-position: center;"></div>
                    <div class="card-body d-flex flex-column flex-grow-1">
                      <h4 class="card-title mb-2">${project.name}</h4>
                      ${project.developer_name ? `
                        <div class="text-muted small mb-1">
                          <i class="ti ti-building me-1"></i>
                          ${project.developer_name}
                        </div>
                      ` : ''}
                      <div class="text-muted small mb-2">
                        <i class="ti ti-map-pin me-1"></i>
                        ${project.location}
                      </div>
                      <div class="mt-auto">
                        <div class="d-flex gap-1 flex-wrap mb-2">
                          <div class="badge badge-sm bg-primary-lt">
                            ${project.commission_preview}
                          </div>
                          ${project.require_digital_signature ? `
                            <div class="badge badge-sm bg-info-lt">
                              <i class="ti ti-writing"></i>
                              TTD Digital
                            </div>
                          ` : ''}
                          <div class="badge badge-sm bg-secondary-lt">
                            ${project.units_count} Unit
                          </div>
                        </div>
                      </div>
                    </div>
                  </span>
                </label>
              </div>
            `;

            if (isAutoSelected) {
              selectedProjectId = project.id;
              nextButton.disabled = false;
            }
          });
          projectsContainer.innerHTML = html;
          
          // Re-attach event listeners
          attachProjectEventListeners();
          
          // Update count
          projectCount.textContent = `${data.pagination.total} tersedia`;
          
          // Update pagination info
          const infoText = data.pagination.from && data.pagination.to 
            ? `Menampilkan ${data.pagination.from} - ${data.pagination.to} dari ${data.pagination.total} project`
            : 'Tidak ada project yang ditemukan';
          
          paginationInfo.querySelector('span').textContent = infoText;
          
        } else {
          projectsContainer.innerHTML = `
            <div class="no-results">
              <i class="ti ti-search-off icon icon-xl text-muted mb-3"></i>
              <h4>Tidak Ada Project Ditemukan</h4>
              <p class="text-muted">Coba ubah kriteria pencarian atau filter Anda</p>
            </div>
          `;
          projectCount.textContent = '0 tersedia';
          paginationInfo.querySelector('span').textContent = 'Tidak ada project yang ditemukan';
        }
      }

      function initializePagination() {
        // Handle pagination clicks for initial server-rendered pagination
        const initialPaginationLinks = document.querySelectorAll('#pagination-container a[data-page]');
        
        initialPaginationLinks.forEach(link => {
          link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = parseInt(this.getAttribute('data-page'));
            loadProjects(page);
          });
        });
      }

      function updatePagination(pagination) {
        if (!pagination || pagination.last_page <= 1) {
          paginationContainer.style.display = 'none';
          return;
        }

        paginationContainer.style.display = 'flex';
        totalPages = pagination.last_page;
        currentPage = pagination.current_page;

        let html = '';
        
        // Previous button
        if (currentPage > 1) {
          html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" data-page="${currentPage - 1}"><i class="ti ti-chevron-left"></i></a></li>`;
        } else {
          html += `<li class="page-item disabled"><span class="page-link"><i class="ti ti-chevron-left"></i></span></li>`;
        }

        // Page numbers
        const start = Math.max(1, currentPage - 2);
        const end = Math.min(totalPages, currentPage + 2);

        if (start > 1) {
          html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" data-page="1">1</a></li>`;
          if (start > 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
          }
        }

        for (let i = start; i <= end; i++) {
          if (i === currentPage) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
          } else {
            html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" data-page="${i}">${i}</a></li>`;
          }
        }

        if (end < totalPages) {
          if (end < totalPages - 1) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
          }
          html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" data-page="${totalPages}">${totalPages}</a></li>`;
        }

        // Next button
        if (currentPage < totalPages) {
          html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" data-page="${currentPage + 1}"><i class="ti ti-chevron-right"></i></a></li>`;
        } else {
          html += `<li class="page-item disabled"><span class="page-link"><i class="ti ti-chevron-right"></i></span></li>`;
        }

        paginationContainer.innerHTML = html;
        
        // Attach pagination event listeners
        paginationContainer.querySelectorAll('a[data-page]').forEach(link => {
          link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = parseInt(this.getAttribute('data-page'));
            loadProjects(page);
          });
        });
      }

      function attachProjectEventListeners() {
        const newRadioButtons = document.querySelectorAll('input[name="selected_project"]');
        newRadioButtons.forEach(radio => {
          radio.addEventListener('change', function () {
            if (this.checked) {
              selectedProjectId = this.value;
              nextButton.disabled = false;
              
              // Update visual selection
              document.querySelectorAll('.project-card').forEach(card => {
                card.classList.remove('selected');
              });
              this.closest('.project-card').classList.add('selected');
            }
          });
        });
      }

      // Search functionality
      const debouncedSearch = debounce(() => {
        currentPage = 1;
        updateActiveFilters();
        loadProjects(1);
      }, 500);

      searchInput.addEventListener('input', debouncedSearch);

      // Filter change handlers
      [locationFilter, commissionTypeFilter, minCommissionInput].forEach(element => {
        element.addEventListener('change', () => {
          currentPage = 1;
          updateActiveFilters();
          loadProjects(1);
        });
      });

      // Per page and sort handlers
      if (perPageSelect) {
        perPageSelect.addEventListener('change', () => {
          currentPage = 1;
          loadProjects(1);
        });
      }

      if (sortSelect) {
        sortSelect.addEventListener('change', () => {
          currentPage = 1;
          loadProjects(1);
        });
      }

      // Enter key in search
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          currentPage = 1;
          updateActiveFilters();
          loadProjects(1);
        }
      });

      // Reset filters
      resetFiltersBtn.addEventListener('click', function() {
        searchInput.value = '';
        locationFilter.value = '';
        commissionTypeFilter.value = '';
        minCommissionInput.value = '';
        if (perPageSelect) perPageSelect.value = '12';
        if (sortSelect) sortSelect.value = 'name';
        
        quickFilterBtns.forEach(btn => btn.classList.remove('active'));
        
        currentPage = 1;
        updateActiveFilters();
        loadProjects(1);
        
        // ← BARU: Show toast if auto-select project will be prioritized again
        if (autoSelectProjectSlug) {
          setTimeout(() => {
            showToast('Project rekomendasi akan ditampilkan di urutan pertama', 'info');
          }, 500);
        }
      });

      // Initial setup
      function getStepsConfig() {
        if (requireDigitalSignature) {
          return [
            { progress: 14, showBack: false },
            { progress: 28, showBack: true },
            { progress: 42, showBack: true },
            { progress: 56, showBack: true },
            { progress: 70, showBack: true },
            { progress: 85, showBack: true },
            { progress: 100, showBack: false }
          ];
        } else {
          return [
            { progress: 16, showBack: false },
            { progress: 33, showBack: true },
            { progress: 50, showBack: true },
            { progress: 66, showBack: true },
            { progress: 83, showBack: true },
            { progress: 100, showBack: false }
          ];
        }
      }

      function updateProgress(step) {
        const steps = getStepsConfig();
        const stepData = steps[step - 1];
        progressBar.style.width = stepData.progress + '%';
        progressBar.setAttribute('aria-valuenow', stepData.progress);
        
        backButton.style.display = stepData.showBack ? 'inline-block' : 'none';
        welcomeCard.style.display = step === 1 ? 'block' : 'none';
      }

      // Auto-select project if specified
      @if($autoSelectProjectSlug ?? false)
        const targetProjectSlug = '{{ $autoSelectProjectSlug }}';
        
        setTimeout(() => {
          const targetRadio = document.querySelector(`input[name="selected_project"]:checked`);
          if (targetRadio) {
            selectedProjectId = targetRadio.value;
            nextButton.disabled = false;
            targetRadio.closest('.project-card').classList.add('selected');
            targetRadio.scrollIntoView({ behavior: 'smooth', block: 'center' });
            showToast('Project berdasarkan pilihan Anda telah dipilih otomatis', 'info');
          }
        }, 500);
      @endif

      // Initial project selection handlers
      attachProjectEventListeners();

      // Initialize active filters
      updateActiveFilters();

      // Initialize pagination event listeners for server-rendered pagination
      initializePagination();

      // Back button handler
      backButton.addEventListener('click', function() {
        console.log('Current step:', currentStep, 'requireDigitalSignature:', requireDigitalSignature);
        
        if (currentStep === 2) {
          projectSelectionCard.style.display = 'block';
          projectDetailsCard.style.display = 'none';
          document.querySelectorAll('input[name="selected_project"]').forEach(radio => {
            radio.checked = false;
          });
          document.querySelectorAll('.project-card').forEach(card => {
            card.classList.remove('selected');
          });
          selectedProjectId = null;
          currentStep = 1;
          updateProgress(currentStep);
          nextButton.disabled = true;
          
        } else if (currentStep === 3) {
          projectDetailsCard.style.display = 'block';
          termsCard.style.display = 'none';
          currentStep = 2;
          updateProgress(currentStep);
          nextButton.disabled = false;
          
        } else if (currentStep === 4) {
          termsCard.style.display = 'block';
          ktpCard.style.display = 'none';
          currentStep = 3;
          updateProgress(currentStep);
          nextButton.disabled = !termsCheckbox.checked;
          document.getElementById('join-project-form').classList.remove('container-tight');
          
        } else if (currentStep === 5 && requireDigitalSignature) {
          ktpCard.style.display = 'block';
          signatureCard.style.display = 'none';
          currentStep = 4;
          updateProgress(currentStep);
          nextButton.style.display = 'inline-block';
          joinButton.style.display = 'none';
          validateKtpStep();
          
        } else if (currentStep === 5 && !requireDigitalSignature) {
          hideCompletionMessage();
          ktpCard.style.display = 'block';
          currentStep = 4;
          updateProgress(currentStep);
          nextButton.style.display = 'inline-block';
          joinButton.style.display = 'none';
          validateKtpStep();
          
        } else if (currentStep === 6 && requireDigitalSignature) {
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
          if (requireDigitalSignature) {
            showSignature();
          } else {
            showCompletionStep();
          }
        } else if (currentStep === 5 && requireDigitalSignature && validateSignatureStep()) {
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
          if (file.size > 2 * 1024 * 1024) {
            showToast('File terlalu besar. Maksimal 2MB.', 'danger');
            this.value = '';
            ktpPreview.innerHTML = '';
            validateKtpStep();
            return;
          }

          if (!file.type.startsWith('image/')) {
            showToast('Pilih file gambar yang valid.', 'danger');
            this.value = '';
            ktpPreview.innerHTML = '';
            validateKtpStep();
            return;
          }

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
            showToast(data.error, 'danger');
            return;
          }
          
          projectData = data;
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
          showToast('Gagal memuat detail project', 'danger');
        });
      }

      // Display project details
      function displayProjectDetails(data) {
        document.getElementById('project-details-title').textContent = `Detail Project - ${data.name}`;
        
        document.getElementById('commission-range').textContent = data.commission_range || 'Komisi Bervariasi';
        document.getElementById('commission-description').textContent = data.commission_description || 'Komisi disesuaikan berdasarkan unit yang dipilih';

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

        nextButton.style.display = 'inline-block';
        joinButton.style.display = 'none';

        ktpCard.style.display = 'none';
        signatureCard.style.display = 'block';
        signatureCard.scrollIntoView({ behavior: 'smooth' });
        
        initializeSignaturePad();
        validateSignatureStep();
      }

      // Show completion step
      function showCompletionStep() {
        currentStep = requireDigitalSignature ? 6 : 5;
        updateProgress(currentStep);

        nextButton.style.display = 'none';
        joinButton.style.display = 'inline-block';
        joinButton.disabled = false;

        if (requireDigitalSignature) {
          signatureCard.style.display = 'none';
        } else {
          ktpCard.style.display = 'none';
        }
        
        showCompletionMessage();
      }

      function showCompletionMessage() {
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
        
        hideCompletionMessage();
        
        const form = document.getElementById('join-project-form');
        const navigation = form.querySelector('.row.align-items-center.mt-3');
        navigation.insertAdjacentHTML('beforebegin', completionHtml);
        
        setTimeout(() => {
          const completionCard = document.getElementById('completion-card');
          if (completionCard) {
            completionCard.scrollIntoView({ behavior: 'smooth' });
          }
        }, 100);
      }

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
          showToast('Pilih project terlebih dahulu', 'warning');
          return;
        }

        if (!termsCheckbox.checked) {
          showToast('Anda harus menyetujui syarat & ketentuan', 'warning');
          return;
        }

        if (!validateKtpStep()) {
          showToast('Lengkapi data KTP', 'warning');
          return;
        }

        if (requireDigitalSignature && (!signaturePad || signaturePad.isEmpty())) {
          showToast('Berikan tanda tangan digital', 'warning');
          return;
        }

        joinButton.disabled = true;
        joinButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

        const formData = new FormData();
        formData.append('project_id', selectedProjectId);
        formData.append('ktp_number', ktpNumber.value);
        formData.append('ktp_photo', ktpPhoto.files[0]);
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
            showToast(data.message, 'success');
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
            showToast(data.message || 'Terjadi kesalahan', 'danger');
            joinButton.disabled = false;
            joinButton.innerHTML = '<i class="ti ti-user-plus me-1"></i>Bergabung Project';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Terjadi kesalahan saat bergabung project', 'danger');
          joinButton.disabled = false;
          joinButton.innerHTML = '<i class="ti ti-user-plus me-1"></i>Bergabung Project';
        });
      });

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