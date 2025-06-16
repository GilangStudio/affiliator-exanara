@extends('layouts.main')

@section('title', 'Ajukan Ulang Project - ' . $project->name)

@section('content')

@include('components.alert')

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-refresh me-2"></i>
                    Ajukan Ulang Project untuk Persetujuan
                </h3>
            </div>
            <div class="card-body">
                <!-- Project Info -->
                <div class="row mb-4">
                    <div class="col-auto">
                        @if($project->logo)
                            <img src="{{ $project->logo_url }}" alt="{{ $project->name }}" class="avatar avatar-xl">
                        @else
                            <div class="avatar avatar-xl bg-primary-lt">
                                {{ substr($project->name, 0, 2) }}
                            </div>
                        @endif
                    </div>
                    <div class="col">
                        <h2 class="mb-1">{{ $project->name }}</h2>
                        <div class="text-secondary mb-2">{{ $project->location }}</div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-danger-lt me-2">
                                <i class="ti ti-alert-triangle me-1"></i>
                                {{ $project->registration_status_label }}
                            </span>
                            @if($project->crm_project_id)
                                <span class="badge bg-info-lt">
                                    <i class="ti ti-link me-1"></i>
                                    CRM ID: {{ $project->crm_project_id }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Rejection Info -->
                @if($project->latestRegistration && $project->latestRegistration->review_notes)
                    <div class="alert alert-danger d-block">
                        <h4 class="alert-title">
                            <i class="ti ti-alert-triangle me-2"></i>
                            Alasan Penolakan
                        </h4>
                        <div class="mb-2">{{ $project->latestRegistration->review_notes }}</div>
                        @if($project->latestRegistration->reviewed_at)
                            <small class="text-muted">
                                Ditolak pada {{ $project->latestRegistration->reviewed_at->format('d F Y, H:i') }}
                                @if($project->latestRegistration->reviewedBy)
                                    oleh {{ $project->latestRegistration->reviewedBy->name }}
                                @endif
                            </small>
                        @endif
                    </div>
                @endif

                <!-- What will happen -->
                <div class="alert alert-info d-block">
                    <h4 class="alert-title">
                        <i class="ti ti-info-circle me-2"></i>
                        Yang Akan Terjadi
                    </h4>
                    <ul class="mb-0">
                        <li>Project akan diubah statusnya menjadi "Menunggu Persetujuan"</li>
                        <li>Super Admin akan mendapat notifikasi bahwa project telah diajukan ulang</li>
                        <li>Super Admin akan meninjau ulang project Anda</li>
                        <li>Anda akan mendapat notifikasi hasil review (disetujui/ditolak)</li>
                    </ul>
                </div>

                <!-- Recommendations -->
                <div class="alert alert-warning d-block">
                    <h4 class="alert-title">
                        <i class="ti ti-bulb me-2"></i>
                        Rekomendasi Sebelum Mengajukan Ulang
                    </h4>
                    <ul class="mb-3">
                        <li>Pastikan Anda telah memperbaiki semua hal yang disebutkan dalam alasan penolakan</li>
                        <li>Periksa kembali informasi project, unit, dan dokumen yang dilampirkan</li>
                        <li>Pastikan semua data sudah lengkap dan sesuai ketentuan</li>
                        <li>Jika perlu, edit project terlebih dahulu sebelum mengajukan ulang</li>
                    </ul>
                    <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i>
                        Edit Project Dulu
                    </a>
                </div>

                <!-- Checklist -->
                <div class="mb-4">
                    <h4>Checklist Sebelum Mengajukan Ulang</h4>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="check1" required>
                        <label class="form-check-label" for="check1">
                            Saya telah membaca dan memahami alasan penolakan
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="check2" required>
                        <label class="form-check-label" for="check2">
                            Saya telah memperbaiki hal-hal yang disebutkan dalam penolakan
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="check3" required>
                        <label class="form-check-label" for="check3">
                            Semua informasi project sudah lengkap dan akurat
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="check4" required>
                        <label class="form-check-label" for="check4">
                            Saya siap menunggu proses review dari Super Admin
                        </label>
                    </div>
                </div>

                <!-- Confirmation Form -->
                <form action="{{ route('admin.projects.resubmit', $project) }}" method="POST" id="resubmit-form">
                    @csrf
                    
                    {{-- <div class="mb-3">
                        <label class="form-label">Catatan Tambahan (Opsional)</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Jelaskan perubahan yang telah Anda lakukan untuk memperbaiki project ini..."></textarea>
                        <small class="form-hint">
                            Catatan ini akan dikirim bersama pengajuan ulang kepada Super Admin
                        </small>
                    </div> --}}

                    <div class="alert alert-warning">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <div>
                                <strong>Perhatian:</strong> Setelah mengajukan ulang, Anda tidak dapat mengedit project hingga mendapat keputusan dari Super Admin.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-link">
                            <i class="ti ti-arrow-left me-1"></i>
                            Kembali
                        </a>
                        <div>
                            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-outline-primary me-2">
                                <i class="ti ti-edit me-1"></i>
                                Edit Project Dulu
                            </a>
                            <button type="submit" class="btn btn-warning" id="submit-btn" disabled>
                                <i class="ti ti-refresh me-1"></i>
                                Ajukan Ulang untuk Persetujuan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][required]');
    const submitBtn = document.getElementById('submit-btn');
    const form = document.getElementById('resubmit-form');

    // Check if all checkboxes are checked
    function updateSubmitButton() {
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        submitBtn.disabled = !allChecked;
        
        if (allChecked) {
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-warning');
        } else {
            submitBtn.classList.remove('btn-warning');
            submitBtn.classList.add('btn-secondary');
        }
    }

    // Add event listeners to checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSubmitButton);
    });

    // Form submission confirmation
    form.addEventListener('submit', function(e) {
        if (!confirm('Apakah Anda yakin ingin mengajukan ulang project ini untuk persetujuan?')) {
            e.preventDefault();
        }
    });

    // Initial check
    updateSubmitButton();
});
</script>
@endpush