@extends('layouts.main')

@section('title', 'Unit Project - ' . $project->name)

@section('content')

@include('components.alert')

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
                <div class="text-secondary">Kelola Unit Project</div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.projects.index') }}">Projects</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.projects.show', $project) }}">{{ $project->name }}</a></li>
                        <li class="breadcrumb-item active">Unit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Daftar Unit</h3>
                <a href="{{ route('admin.projects.units.create', $project) }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>
                    Tambah Unit
                </a>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <form method="GET" class="row g-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Cari unit..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="type">
                            <option value="">Semua Tipe</option>
                            @foreach(App\Models\Unit::getUnitTypes() as $key => $label)
                                <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="sort">
                            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nama</option>
                            <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>Harga</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="ti ti-search me-1"></i>
                                Filter
                            </button>
                            <a href="{{ route('admin.projects.units.index', $project) }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                @if($units->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th>Harga</th>
                                    <th>Komisi</th>
                                    <th>Spesifikasi</th>
                                    <th>Status</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($units as $unit)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($unit->image)
                                                <img src="{{ asset('storage/' . $unit->image) }}" alt="{{ $unit->name }}" 
                                                     class="avatar avatar-sm me-2">
                                            @else
                                                <div class="avatar avatar-sm bg-primary-lt me-2">
                                                    {{ substr($unit->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $unit->name }}</div>
                                                @if($unit->unit_type)
                                                    <span class="badge bg-blue-lt">{{ $unit->unit_type_display }}</span>
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
                                        <span class="badge bg-{{ $unit->is_active ? 'success' : 'secondary' }}-lt">
                                            {{ $unit->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-icon bg-light" 
                                                    data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @if(Auth::user()->canEditProject($project->id))
                                                    <a href="{{ route('admin.projects.units.edit', [$project, $unit]) }}" 
                                                       class="dropdown-item">
                                                        <i class="ti ti-edit me-2"></i>
                                                        Edit Unit
                                                    </a>
                                                    <form action="{{ route('admin.projects.units.toggle-status', [$project, $unit]) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="ti ti-{{ $unit->is_active ? 'eye-off' : 'eye' }} me-2"></i>
                                                            {{ $unit->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                        </button>
                                                    </form>
                                                    <div class="dropdown-divider"></div>
                                                    <button type="button" class="dropdown-item text-danger delete-btn"
                                                            data-name="{{ $unit->name }}" 
                                                            data-url="{{ route('admin.projects.units.destroy', [$project, $unit]) }}">
                                                        <i class="ti ti-trash me-2"></i>
                                                        Hapus Unit
                                                    </button>
                                                @else
                                                    <span class="dropdown-item text-muted">
                                                        <i class="ti ti-edit me-2"></i>
                                                        Edit Unit
                                                    </span>
                                                    <span class="dropdown-item text-muted">
                                                        <i class="ti ti-eye-off me-2"></i>
                                                        Toggle Status
                                                    </span>
                                                    <span class="dropdown-item text-muted">
                                                        <i class="ti ti-trash me-2"></i>
                                                        Hapus Unit
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer d-flex align-items-center">
                        <p class="m-0 text-secondary">
                            Menampilkan {{ $units->firstItem() ?? 0 }} hingga {{ $units->lastItem() ?? 0 }} 
                            dari {{ $units->total() }} unit
                        </p>
                        @include('components.pagination', ['paginator' => $units])
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="ti ti-building-off icon icon-xl text-secondary"></i>
                        </div>
                        <h3 class="text-secondary">Belum ada unit</h3>
                        <p class="text-secondary">Project ini belum memiliki unit yang terdaftar.</p>
                        @if(Auth::user()->canEditProject($project->id))
                            <a href="{{ route('admin.projects.units.create', $project) }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>
                                Tambah Unit Pertama
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->canEditProject($project->id))
    @include('components.delete-modal')
@endif

@endsection