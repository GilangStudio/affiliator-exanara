@extends('layouts.main')

@section('title', 'Edit Unit - ' . $unit->name)

@section('content')
<!-- Project Header -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                @if($project->logo)
                    <img src="{{ $project->logo_url }}" alt="{{ $project->name }}" 
                            class="avatar avatar-lg">
                @else
                    <div class="avatar avatar-lg bg-primary-lt">
                        {{ substr($project->name, 0, 2) }}
                    </div>
                @endif
            </div>
            <div class="col">
                <h1 class="mb-1">{{ $project->name }}</h1>
                <div class="text-secondary">Edit Unit - {{ $unit->name }}</div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.projects.index') }}">Projects</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.projects.units.index', $project) }}">Unit</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('admin.projects.units.update', [$project, $unit]) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row g-3">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-building me-2"></i>Informasi Unit</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Unit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name', $unit->name) }}" required
                               placeholder="Contoh: Tower A Unit 12">
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="3"
                                  placeholder="Deskripsi unit...">{{ old('description', $unit->description) }}</textarea>
                        @error('description')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Harga <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                           name="price" value="{{ old('price', $unit->price) }}" required min="0"
                                           placeholder="500000000">
                                </div>
                                @error('price')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tipe Unit</label>
                                <select class="form-select @error('unit_type') is-invalid @enderror" name="unit_type">
                                    <option value="">Pilih Tipe Unit</option>
                                    @foreach(App\Models\Unit::getUnitTypes() as $key => $label)
                                        <option value="{{ $key }}" {{ old('unit_type', $unit->unit_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('unit_type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tipe Komisi <span class="text-danger">*</span></label>
                                <select class="form-select @error('commission_type') is-invalid @enderror" 
                                        name="commission_type" required id="commission-type">
                                    @foreach(App\Models\Unit::getCommissionTypes() as $key => $label)
                                        <option value="{{ $key }}" {{ old('commission_type', $unit->commission_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('commission_type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nilai Komisi <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text" id="commission-prefix">Rp</span>
                                    <input type="number" class="form-control @error('commission_value') is-invalid @enderror" 
                                           name="commission_value" value="{{ old('commission_value', $unit->commission_value) }}" required min="0" step="0.1"
                                           placeholder="0">
                                </div>
                                @error('commission_value')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spesifikasi Unit -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-home me-2"></i>Spesifikasi Unit</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Luas Bangunan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('building_area') is-invalid @enderror" 
                                           name="building_area" value="{{ old('building_area', $unit->building_area) }}" 
                                           placeholder="120">
                                    <span class="input-group-text">m²</span>
                                </div>
                                @error('building_area')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Luas Tanah</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('land_area') is-invalid @enderror" 
                                           name="land_area" value="{{ old('land_area', $unit->land_area) }}" 
                                           placeholder="150">
                                    <span class="input-group-text">m²</span>
                                </div>
                                @error('land_area')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Kamar Tidur</label>
                                <input type="number" class="form-control @error('bedrooms') is-invalid @enderror" 
                                       name="bedrooms" value="{{ old('bedrooms', $unit->bedrooms) }}" min="0" placeholder="3">
                                @error('bedrooms')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Kamar Mandi</label>
                                <input type="number" class="form-control @error('bathrooms') is-invalid @enderror" 
                                       name="bathrooms" value="{{ old('bathrooms', $unit->bathrooms) }}" min="0" placeholder="2">
                                @error('bathrooms')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Carport</label>
                                <input type="number" class="form-control @error('carport') is-invalid @enderror" 
                                       name="carport" value="{{ old('carport', $unit->carport) }}" min="0" placeholder="1">
                                @error('carport')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Lantai</label>
                                <input type="number" class="form-control @error('floor') is-invalid @enderror" 
                                       name="floor" value="{{ old('floor', $unit->floor) }}" min="0" placeholder="1">
                                @error('floor')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Gambar Unit -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-photo me-2"></i>Gambar Unit</h3>
                </div>
                <div class="card-body">
                    @if($unit->image)
                        <div class="mb-3 text-center">
                            <img src="{{ asset('storage/' . $unit->image) }}" alt="Current Image" 
                                 class="avatar avatar-xl mb-2">
                            <div class="text-secondary small">Gambar saat ini</div>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label class="form-label">Upload Gambar Baru</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" 
                               name="image" accept="image/*" id="image-input">
                        @error('image')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <small class="form-hint">
                            <i class="ti ti-info-circle me-1"></i>
                            Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah.
                        </small>
                        <div class="mt-2" id="image-preview"></div>
                    </div>
                </div>
            </div>

            <!-- Pengaturan Unit -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-settings me-2"></i>Pengaturan Unit</h3>
                </div>
                <div class="card-body">
                    <div class="">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" 
                                   value="1" {{ old('is_active', $unit->is_active) ? 'checked' : '' }}>
                            <span class="form-check-label">Unit Aktif</span>
                        </label>
                        <small class="form-hint">Centang untuk mengaktifkan unit</small>
                    </div>
                </div>
            </div>

            <!-- Info Unit -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-info-circle me-2"></i>Info Unit</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <div class="text-secondary small">Dibuat</div>
                            <div>{{ $unit->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-12 mb-2">
                            <div class="text-secondary small">Terakhir Diperbarui</div>
                            <div>{{ $unit->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-secondary small">Komisi Unit Ini</div>
                            <div class="fw-bold">{{ $unit->commission_amount_formatted }}</div>
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
                        <a href="{{ route('admin.projects.units.index', $project) }}" class="btn btn-link">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary ms-auto">
                            <i class="ti ti-device-floppy me-1"></i>
                            Perbarui Unit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
@include('components.alert')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Commission type handler
    const commissionTypeSelect = document.getElementById('commission-type');
    const commissionPrefix = document.getElementById('commission-prefix');
    
    function updateCommissionPrefix() {
        const type = commissionTypeSelect.value;
        if (type === 'percentage') {
            commissionPrefix.textContent = '%';
        } else if (type === 'fixed') {
            commissionPrefix.textContent = 'Rp';
        } else {
            commissionPrefix.textContent = 'Rp';
        }
    }
    
    commissionTypeSelect.addEventListener('change', updateCommissionPrefix);
    updateCommissionPrefix();
    
    // Image preview functionality
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');
    
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            const maxSize = 2 * 1024 * 1024; // 2MB
            if (file.size > maxSize) {
                showAlert(imageInput, 'danger', 'File terlalu besar. Maksimal 2MB.');
                imageInput.value = '';
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                showAlert(imageInput, 'danger', 'Pilih file gambar yang valid.');
                imageInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.innerHTML = `
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
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearImagePreview()">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <small class="text-warning">
                                    <i class="ti ti-alert-triangle me-1"></i>
                                    Akan mengganti gambar yang ada
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.innerHTML = '';
        }
    });

    // Clear image preview function
    window.clearImagePreview = function() {
        imageInput.value = '';
        imagePreview.innerHTML = '';
    };
});
</script>
@endpush