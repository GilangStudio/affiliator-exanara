@extends('layouts.main')

@section('title', 'Submit Ulang Verifikasi - ' . $project->name)

@section('content')

@push('styles')
<style>
.signature-canvas {
    width: 100%;
    height: 300px;
    border: 2px dashed #e9ecef;
    border-radius: 0.375rem;
    cursor: crosshair;
    transition: border-color 0.15s ease-in-out;
}
</style>
@endpush

@include('components.alert')

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-refresh me-2"></i>
                    Submit Ulang Data Verifikasi
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
                                Verifikasi Ditolak
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Rejection Info -->
                @if($userProject->verification_notes)
                    <div class="alert alert-danger d-block">
                        <h4 class="alert-title">
                            <i class="ti ti-alert-triangle icon me-2"></i>
                            Alasan Penolakan
                        </h4>
                        <div class="mb-2">{{ $userProject->verification_notes }}</div>
                        @if($userProject->verified_at)
                            <small class="text-muted">
                                Ditolak pada {{ $userProject->verified_at->format('d F Y, H:i') }}
                                @if($userProject->verifiedBy)
                                    oleh {{ $userProject->verifiedBy->name }}
                                @endif
                            </small>
                        @endif
                    </div>
                @endif

                <form action="{{ route('affiliator.project.resubmit', $project) }}" method="POST" 
                      enctype="multipart/form-data" id="resubmit-form">
                    @csrf

                    <!-- Current Data Status -->
                    <div class="card bg-light mb-4">
                        <div class="card-header">
                            <h4 class="card-title">Status Data Saat Ini</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Data KTP</label>
                                        <div class="d-flex align-items-center">
                                            @if($userProject->ktp_number && $userProject->ktp_photo)
                                                <i class="ti ti-check text-success me-2"></i>
                                                <span>Sudah dilengkapi</span>
                                            @else
                                                <i class="ti ti-x text-danger me-2"></i>
                                                <span class="text-danger">Belum dilengkapi</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Syarat & Ketentuan</label>
                                        <div class="d-flex align-items-center">
                                            @if($userProject->terms_accepted)
                                                <i class="ti ti-check text-success me-2"></i>
                                                <span>Sudah disetujui</span>
                                                @if($userProject->terms_accepted_at)
                                                    <div class="text-secondary small ms-2">
                                                        ({{ $userProject->terms_accepted_at->format('d/m/Y H:i') }})
                                                    </div>
                                                @endif
                                            @else
                                                <i class="ti ti-x text-danger me-2"></i>
                                                <span class="text-danger">Belum disetujui</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    @if($project->require_digital_signature)
                                    <div class="mb-3">
                                        <label class="form-label">Tanda Tangan Digital</label>
                                        <div class="d-flex align-items-center">
                                            @if($userProject->digital_signature)
                                                <i class="ti ti-check text-success me-2"></i>
                                                <span>Sudah dibuat</span>
                                                @if($userProject->digital_signature_at)
                                                    <div class="text-secondary small ms-2">
                                                        ({{ $userProject->digital_signature_at->format('d/m/Y H:i') }})
                                                    </div>
                                                @endif
                                            @else
                                                <i class="ti ti-x text-danger me-2"></i>
                                                <span class="text-danger">Belum dibuat</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update KTP Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">Update Data KTP</h4>
                        </div>
                        <div class="card-body">
                            @if($userProject->ktp_number && $userProject->ktp_photo)
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle icon me-2"></i>
                                    Data KTP Anda saat ini sudah ada. Anda dapat menggantinya dengan yang baru atau biarkan kosong untuk tetap menggunakan yang lama.
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nomor KTP Saat Ini</label>
                                        <div class="fw-bold">{{ $userProject->ktp_number }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Foto KTP Saat Ini</label>
                                        <div>
                                            <img src="{{ $userProject->ktp_photo_url }}" alt="KTP" 
                                                 class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nomor KTP {{ !$userProject->ktp_number ? '(Wajib)' : '(Opsional - untuk update)' }}</label>
                                        <input type="text" class="form-control @error('ktp_number') is-invalid @enderror" 
                                               name="ktp_number" value="{{ old('ktp_number') }}" 
                                               placeholder="Masukkan nomor KTP"
                                               {{ !$userProject->ktp_number ? 'required' : '' }}>
                                        @error('ktp_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Foto KTP {{ !$userProject->ktp_photo ? '(Wajib)' : '(Opsional - untuk update)' }}</label>
                                        <input type="file" class="form-control @error('ktp_photo') is-invalid @enderror" 
                                               name="ktp_photo" accept="image/*"
                                               {{ !$userProject->ktp_photo ? 'required' : '' }}>
                                        @error('ktp_photo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Format: JPG, PNG. Maksimal 2MB.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    @if(!$userProject->terms_accepted)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">Syarat & Ketentuan</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-control" style="height: 200px; overflow-y: auto; background: #f8f9fa;">
                                {!! $project->terms_and_conditions !!}
                            </div>
                            
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="accept_terms" name="accept_terms" 
                                       value="1" required>
                                <label class="form-check-label" for="accept_terms">
                                    <strong>Saya telah membaca dan menyetujui syarat & ketentuan di atas</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Digital Signature -->
                    @if($project->require_digital_signature && !empty($userProject->digital_signature))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">Tanda Tangan Digital</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle icon icon me-2"></i>
                                Project ini memerlukan tanda tangan digital. Silakan buat tanda tangan Anda di bawah ini.
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Buat Tanda Tangan Anda</label>
                                <div class="position-relative" style="width: fit-content">
                                    <div class="position-absolute top-0 end-0 p-2 z-index-1">
                                        <div class="btn btn-icon btn-sm" id="signature-clear" title="Hapus tanda tangan" data-bs-toggle="tooltip" onclick="clearSignature()">
                                            <i class="ti ti-trash icon"></i>
                                        </div>
                                    </div>
                                    <canvas id="signature-pad" style="border: 2px dashed #e9ecef; background: white; cursor: crosshair; border-radius: 0.375rem"></canvas>
                                    {{-- <div class="mt-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSignature()">
                                                <i class="ti ti-eraser me-1"></i>
                                                Hapus
                                            </button>
                                        </div>
                                    </div> --}}
                                </div>
                                <input type="hidden" name="digital_signature" id="signature-data" required>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Additional Notes -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="card-title">Catatan Tambahan (Opsional)</h4>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" name="notes" rows="3" 
                                      placeholder="Jelaskan perbaikan yang telah Anda lakukan atau informasi tambahan lainnya...">{{ old('notes') }}</textarea>
                            <small class="form-hint">
                                Catatan ini akan dikirim kepada admin sebagai penjelasan atas perbaikan yang Anda lakukan.
                            </small>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('affiliator.project.show', $project) }}" class="btn btn-link">
                            <i class="ti ti-arrow-left me-1"></i>
                            Kembali
                        </a>
                        <div>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="ti ti-send me-1"></i>
                                Submit Ulang untuk Verifikasi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal modal-blur fade" id="submit-confirmation-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Submit Ulang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle icon me-2"></i>
                    <strong>Pastikan data sudah benar!</strong>
                </div>
                <p>Apakah Anda yakin ingin submit ulang data verifikasi untuk project <strong>{{ $project->name }}</strong>?</p>
                <div class="alert alert-warning d-block">
                    <h4 class="alert-title">Yang akan terjadi:</h4>
                    <ul class="mb-0">
                        <li>Data verifikasi akan dikirim untuk review admin</li>
                        <li>Status akan berubah menjadi "Menunggu Verifikasi"</li>
                        <li>Anda akan mendapat notifikasi hasil review</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirm-submit-btn">
                    <i class="ti ti-send me-1"></i>
                    Ya, Submit Ulang
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('libs/signature_pad/dist/signature_pad.umd.min.js') }}" defer></script>
<script>
let signaturePad = null;

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resubmit-form');
    const submitBtn = document.getElementById('submit-btn');
    const confirmSubmitBtn = document.getElementById('confirm-submit-btn');
    
    // Initialize signature pad if exists
    const canvas = document.getElementById('signature-pad');
    if (canvas) {
        signaturePad = new SignaturePad(canvas, {
            penColor: 'rgb(0, 0, 0)', // Warna pena hitam
            velocityFilterWeight: 0.7,
            minWidth: 0.5,
            maxWidth: 2.5,
            throttle: 16,
            minDistance: 5,
        });
        
        // Save signature data when drawing dengan ukuran yang di-crop
        signaturePad.addEventListener('endStroke', function() {
            const croppedSVG = getCroppedSignatureSVG();
            document.getElementById('signature-data').value = croppedSVG;
        });
    }
    
    // Form submission handling
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (canvas && signaturePad.isEmpty()) {
            alert('Silakan buat tanda tangan digital terlebih dahulu.');
            return;
        }
        
        // Update dengan cropped SVG sebelum submit
        if (canvas && !signaturePad.isEmpty()) {
            const croppedSVG = getCroppedSignatureSVG();
            document.getElementById('signature-data').value = croppedSVG;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('submit-confirmation-modal'));
        modal.show();
    });
    
    // Handle modal confirmation
    confirmSubmitBtn.addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('submit-confirmation-modal')).hide();
        
        // Final check dengan cropped SVG
        if (canvas && !signaturePad.isEmpty()) {
            const finalSVG = getCroppedSignatureSVG();
            document.getElementById('signature-data').value = finalSVG;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        
        form.submit();
    });
});

// Fungsi untuk mendapatkan bounding box dari tanda tangan
function getSignatureBounds() {
    if (!signaturePad || signaturePad.isEmpty()) {
        return null;
    }
    
    const canvas = signaturePad.canvas;
    const ctx = canvas.getContext('2d');
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;
    
    let minX = canvas.width;
    let minY = canvas.height;
    let maxX = 0;
    let maxY = 0;
    
    // Scan semua pixel untuk mencari yang tidak transparan
    for (let y = 0; y < canvas.height; y++) {
        for (let x = 0; x < canvas.width; x++) {
            const index = (y * canvas.width + x) * 4;
            const alpha = data[index + 3]; // Alpha channel
            
            if (alpha > 0) { // Pixel tidak transparan
                minX = Math.min(minX, x);
                minY = Math.min(minY, y);
                maxX = Math.max(maxX, x);
                maxY = Math.max(maxY, y);
            }
        }
    }
    
    // Tambahkan padding kecil
    const padding = 10;
    return {
        x: Math.max(0, minX - padding),
        y: Math.max(0, minY - padding),
        width: Math.min(canvas.width, maxX - minX + (padding * 2)),
        height: Math.min(canvas.height, maxY - minY + (padding * 2))
    };
}

// Fungsi untuk mendapatkan SVG yang sudah di-crop sesuai ukuran tanda tangan
function getCroppedSignatureSVG() {
    if (!signaturePad || signaturePad.isEmpty()) {
        return '';
    }
    
    const bounds = getSignatureBounds();
    if (!bounds) {
        return '';
    }
    
    // Dapatkan SVG original
    const originalSVG = signaturePad.toSVG({
        includeBackgroundColor: false
    });
    
    // Parse SVG untuk mendapatkan elemen-elemen di dalamnya
    const parser = new DOMParser();
    const svgDoc = parser.parseFromString(originalSVG, 'image/svg+xml');
    const svgElement = svgDoc.querySelector('svg');
    
    if (!svgElement) {
        return originalSVG;
    }
    
    // Update viewBox dan ukuran SVG sesuai bounds
    svgElement.setAttribute('viewBox', `${bounds.x} ${bounds.y} ${bounds.width} ${bounds.height}`);
    svgElement.setAttribute('width', bounds.width.toString());
    svgElement.setAttribute('height', bounds.height.toString());
    
    // Serializer kembali ke string
    const serializer = new XMLSerializer();
    return serializer.serializeToString(svgElement);
}

// Fungsi untuk clear signature
function clearSignature() {
    if (signaturePad) {
        signaturePad.clear();
        document.getElementById('signature-data').value = '';
    }
}
</script>
@endpush