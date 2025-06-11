@extends('layouts.main')

@section('title', 'Detail Pendaftaran Project - ' . $registration->project->name)

@section('content')

@include('components.alert')

<!-- Header -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                @if($registration->project->logo)
                    <img src="{{ $registration->project->logo_url }}" alt="{{ $registration->project->name }}" 
                            class="avatar avatar-xl">
                @else
                    <div class="avatar avatar-xl bg-primary-lt">
                        {{ substr($registration->project->name, 0, 2) }}
                    </div>
                @endif
            </div>
            <div class="col">
                <h1 class="mb-1">{{ $registration->project->name }}</h1>
                <div class="text-secondary mb-2">{{ $registration->project->developer_name }}</div>
                <div class="row">
                    <div class="col-auto">
                        <span class="badge bg-{{ $registration->status_color }}-lt me-2">
                            {{ $registration->status_label }}
                        </span>
                        <span class="badge bg-blue-lt">{{ $registration->units_count }} Unit</span>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="btn-list">
                    @if($registration->status === 'pending')
                        <button type="button" class="btn btn-success" 
                                data-bs-toggle="modal" data-bs-target="#approve-modal">
                            <i class="ti ti-check me-1"></i>
                            Setujui
                        </button>
                        <button type="button" class="btn btn-danger" 
                                data-bs-toggle="modal" data-bs-target="#reject-modal">
                            <i class="ti ti-x me-1"></i>
                            Tolak
                        </button>
                    @endif
                    <a href="{{ route('superadmin.projects.index') }}" class="btn btn-outline-primary">
                        <i class="ti ti-arrow-left me-1"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Project Information -->
    <div class="col-lg-8">
        <!-- Basic Info -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Informasi Project</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Project</label>
                            <div class="form-control-plaintext">{{ $registration->project->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Developer</label>
                            <div class="form-control-plaintext">{{ $registration->project->developer_name }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Lokasi</label>
                            <div class="form-control-plaintext">{{ $registration->project->location }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Website</label>
                            <div class="form-control-plaintext">
                                @if($registration->project->website_url)
                                    <a href="{{ $registration->project->website_url }}" target="_blank">
                                        {{ $registration->project->website_url }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <div class="form-control-plaintext">
                        {!! $registration->project->description !!}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Periode Project</label>
                    <div class="form-control-plaintext">{{ $registration->project->project_period }}</div>
                </div>
            </div>
        </div>

        <!-- Files -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">File Project</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Logo</label>
                        @if($registration->project->logo)
                            <div class="mb-2">
                                <img src="{{ $registration->project->logo_url }}" 
                                     alt="Logo" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        @else
                            <div class="text-muted">Tidak ada logo</div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Brosur</label>
                        @if($registration->project->brochure_file)
                            <div>
                                <a href="{{ $registration->project->brochure_file_url }}" 
                                   target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="ti ti-file-text me-1"></i>
                                    Lihat Brosur
                                </a>
                            </div>
                        @else
                            <div class="text-muted">Tidak ada file brosur</div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Price List</label>
                        @if($registration->project->price_list_file)
                            <div>
                                <a href="{{ $registration->project->price_list_file_url }}" 
                                   target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="ti ti-file-text me-1"></i>
                                    Lihat Price List
                                </a>
                            </div>
                        @else
                            <div class="text-muted">Tidak ada price list</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Units -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Data Unit</h3>
            </div>
            <div class="card-body p-0">
                @if($registration->project->units->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th>Harga</th>
                                    <th>Komisi</th>
                                    <th>Spesifikasi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registration->project->units as $unit)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($unit->image)
                                                <img src="{{ $unit->image_url }}" alt="{{ $unit->name }}" 
                                                     class="avatar avatar-sm me-2">
                                            @else
                                                <div class="avatar avatar-sm bg-blue-lt me-2">
                                                    {{ substr($unit->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $unit->name }}</div>
                                                @if($unit->unit_type)
                                                    <div class="text-secondary small">{{ $unit->unit_type_display }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $unit->price_formatted }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $unit->commission_display }}</div>
                                        <div class="text-secondary small">{{ $unit->commission_amount_formatted }}</div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $unit->unit_specs ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $unit->unit_status_color }}-lt">
                                            {{ $unit->unit_status_label }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="text-secondary">Tidak ada unit yang terdaftar</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Commission & PIC -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Komisi & PIC</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Komisi Dibayar Setelah</label>
                            <div class="form-control-plaintext">
                                {{ $registration->commission_payment_trigger_label }}
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="mb-3">Informasi PIC</h5>
                @php $picInfo = $registration->pic_info; @endphp
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Nama PIC</label>
                            <div class="form-control-plaintext">{{ $picInfo['name'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Phone PIC</label>
                            <div class="form-control-plaintext">{{ $picInfo['phone'] }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Email PIC</label>
                            <div class="form-control-plaintext">{{ $picInfo['email'] }}</div>
                        </div>
                    </div>
                </div>

                @if($registration->status === 'approved' && $registration->project->picUser)
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Akun PIC telah dibuat:</strong> 
                        PIC dapat login menggunakan email {{ $registration->project->picUser->email }} 
                        dengan password default yang telah dikirim.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Registration Info -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Info Pendaftaran</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Pendaftar</label>
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-sm me-2">{{ $registration->submittedBy->initials }}</span>
                        <div>
                            <div class="fw-bold">{{ $registration->submittedBy->name }}</div>
                            <div class="text-secondary small">{{ $registration->submittedBy->email }}</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Daftar</label>
                    <div class="form-control-plaintext">
                        {{ $registration->created_at->format('d F Y, H:i') }}
                        <div class="text-secondary small">{{ $registration->created_at->diffForHumans() }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <div class="form-control-plaintext">
                        <span class="badge bg-{{ $registration->status_color }}-lt">
                            {{ $registration->status_label }}
                        </span>
                    </div>
                </div>

                @if($registration->reviewed_at)
                    <div class="mb-3">
                        <label class="form-label">Tanggal Review</label>
                        <div class="form-control-plaintext">
                            {{ $registration->reviewed_at->format('d F Y, H:i') }}
                            <div class="text-secondary small">{{ $registration->reviewed_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @endif

                @if($registration->reviewedBy)
                    <div class="mb-3">
                        <label class="form-label">Direview Oleh</label>
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-sm me-2">{{ $registration->reviewedBy->initials }}</span>
                            <div>
                                <div class="fw-bold">{{ $registration->reviewedBy->name }}</div>
                                <div class="text-secondary small">{{ $registration->reviewedBy->email }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($registration->review_notes)
                    <div class="mb-3">
                        <label class="form-label">
                            {{ $registration->status === 'rejected' ? 'Alasan Penolakan' : 'Catatan Review' }}
                        </label>
                        <div class="form-control-plaintext">
                            <div class="alert alert-{{ $registration->status === 'rejected' ? 'danger' : 'info' }} alert-important">
                                {{ $registration->review_notes }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Project Statistics -->
        @if($registration->status === 'approved')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistik Project</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h2 mb-0">{{ $registration->project->total_affiliators }}</div>
                            <div class="text-secondary">Affiliator</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h2 mb-0">{{ $registration->project->total_leads }}</div>
                            <div class="text-secondary">Lead</div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h3 mb-0 text-success">{{ $registration->project->active_affiliators }}</div>
                            <div class="text-secondary small">Aktif</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h3 mb-0 text-blue">{{ $registration->project->verified_leads }}</div>
                            <div class="text-secondary small">Verified</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Approve Modal -->
@if($registration->status === 'pending')
<div class="modal fade" id="approve-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.projects.approve-registration', $registration) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Setujui Pendaftaran Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Anda akan menyetujui pendaftaran project <strong>{{ $registration->project->name }}</strong> 
                        oleh {{ $registration->submittedBy->name }}.
                    </p>
                    
                    <div class="alert alert-info">
                        <h4 class="alert-title">Yang akan terjadi setelah disetujui:</h4>
                        <ul class="mb-0">
                            <li>Project akan diaktifkan dan dapat dilihat oleh affiliator</li>
                            <li>Semua unit dalam project akan diaktifkan</li>
                            <li>Akun admin akan dibuat untuk PIC ({{ $registration->pic_info['name'] }})</li>
                            <li>PIC akan mendapat notifikasi dan dapat login ke sistem</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan (opsional)</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Tambahkan catatan untuk pendaftar..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check me-1"></i>
                        Setujui Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="reject-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.projects.reject-registration', $registration) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Pendaftaran Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Anda akan menolak pendaftaran project <strong>{{ $registration->project->name }}</strong> 
                        oleh {{ $registration->submittedBy->name }}.
                    </p>
                    
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        Pendaftar akan mendapat notifikasi penolakan beserta alasan yang Anda berikan.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="4" required
                                  placeholder="Jelaskan dengan detail alasan penolakan agar pendaftar dapat memperbaiki dan mendaftar ulang..."></textarea>
                        <small class="form-hint">Berikan alasan yang konstruktif untuk membantu pendaftar</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-x me-1"></i>
                        Tolak Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection