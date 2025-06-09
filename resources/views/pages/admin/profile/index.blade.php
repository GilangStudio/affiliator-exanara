@extends('layouts.main')

@section('title', 'Profil & Pengaturan')

@section('content')
<div class="row">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-user-circle me-2"></i>
                    Profil & Pengaturan
                </h3>
            </div>
            <div class="card-body p-2">
                <nav class="nav nav-pills nav-vertical settings-nav">
                    <a class="nav-link {{ $activeTab == 'profile' ? 'active' : '' }}" 
                       href="{{ route('admin.profile.index', ['tab' => 'profile']) }}">
                        <i class="ti ti-user me-2"></i>
                        Profil Saya
                    </a>
                    <a class="nav-link {{ $activeTab == 'password' ? 'active' : '' }}" 
                       href="{{ route('admin.profile.index', ['tab' => 'password']) }}">
                        <i class="ti ti-key me-2"></i>
                        Ganti Password
                    </a>
                    <a class="nav-link {{ $activeTab == 'activity' ? 'active' : '' }}" 
                       href="{{ route('admin.profile.index', ['tab' => 'activity']) }}">
                        <i class="ti ti-history me-2"></i>
                        Log Aktivitas
                    </a>
                    <a class="nav-link {{ $activeTab == 'statistics' ? 'active' : '' }}" 
                       href="{{ route('admin.profile.index', ['tab' => 'statistics']) }}">
                        <i class="ti ti-chart-bar me-2"></i>
                        Statistik
                    </a>
                </nav>
            </div>
        </div>

        <!-- Profile Info Sidebar -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-info-circle me-2"></i>
                    Info Akun
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="{{ $admin->profile_photo_url }}" alt="{{ $admin->name }}" 
                         class="avatar avatar-lg mb-2 object-cover">
                    <div class="fw-bold">{{ $admin->name }}</div>
                    <div class="text-secondary small">{{ $admin->email }}</div>
                </div>
                
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="text-center">
                            <div class="h4 mb-0 text-blue">{{ $stats['total_projects'] ?? 0 }}</div>
                            <div class="text-secondary small">Project</div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="text-center">
                            <div class="h4 mb-0 text-green">{{ number_format($stats['total_activities'] ?? 0) }}</div>
                            <div class="text-secondary small">Aktivitas</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 pt-3 border-top">
                    <div class="text-secondary small">Bergabung</div>
                    <div class="fw-bold">{{ $admin->created_at->format('d/m/Y') }}</div>
                    <div class="text-secondary small">{{ $admin->created_at->diffForHumans() }}</div>
                </div>
                
                @if($admin->last_login_at)
                <div class="mt-2">
                    <div class="text-secondary small">Login Terakhir</div>
                    <div class="fw-bold">{{ $admin->last_login_at->format('d/m/Y H:i') }}</div>
                    <div class="text-secondary small">{{ $admin->last_login_at->diffForHumans() }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-3" role="alert">
            <div class="d-flex">
                <div>
                    <i class="ti ti-check me-2"></i>
                </div>
                <div>{{ session('success') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible mb-3" role="alert">
            <div class="d-flex">
                <div>
                    <i class="ti ti-exclamation-circle me-2"></i>
                </div>
                <div>{{ session('error') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
        @endif

        <!-- Profile Tab -->
        @if($activeTab == 'profile')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-user me-2"></i>
                    Informasi Profil
                </h3>
            </div>
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', $admin->name) }}" required>
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email', $admin->email) }}" required>
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">+62</span>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   placeholder="8123456789" value="{{ old('phone', $admin->phone) }}" required>
                        </div>
                        @error('phone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <small class="form-hint">Nomor tanpa kode negara dan tanpa 0 di depan</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto Profil</label>
                        <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" 
                               name="profile_photo" accept="image/*">
                        @error('profile_photo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <small class="form-hint">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>
                        Perbarui Profil
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Password Tab -->
        @if($activeTab == 'password')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-key me-2"></i>
                    Ubah Password
                </h3>
            </div>
            <form action="{{ route('admin.profile.password.change') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                               name="current_password" required>
                        @error('current_password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                               name="new_password" minlength="8" required>
                        @error('new_password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                        <small class="form-hint">Minimal 8 karakter</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('new_password_confirmation') is-invalid @enderror" 
                               name="new_password_confirmation" required>
                        @error('new_password_confirmation')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-warning">
                        <i class="ti ti-key me-1"></i>
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Activity Tab -->
        @if($activeTab == 'activity')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-history me-2"></i>
                    Log Aktivitas
                </h3>
            </div>
            
            <!-- Filters -->
            <div class="card-body border-bottom">
                <form method="GET" class="row g-2">
                    <input type="hidden" name="tab" value="activity">
                    <div class="col-md-3">
                        <select class="form-select" name="action">
                            <option value="">Semua Aksi</option>
                            @foreach($available_actions ?? [] as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ $action }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" placeholder="Dari Tanggal">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" placeholder="Sampai Tanggal">
                    </div>
                    <div class="col-md-3">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="ti ti-search me-1"></i>
                                Filter
                            </button>
                            <a href="{{ route('admin.profile.index', ['tab' => 'activity']) }}" class="btn btn-outline-secondary">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                @if(isset($activities) && $activities->count() > 0)
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Aksi</th>
                                <th>Deskripsi</th>
                                <th>Project</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                            <tr>
                                <td>
                                    <div>{{ $activity->created_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-secondary small">{{ $activity->created_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-lt">{{ $activity->action }}</span>
                                </td>
                                <td>{{ $activity->description }}</td>
                                <td>
                                    @if($activity->project)
                                        {{ $activity->project->name }}
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- @if(method_exists($activities, 'links'))
                <div class="card-footer d-flex align-items-center">
                    {{ $activities->appends(request()->query())->links() }}
                </div>
                @endif --}}
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-secondary">
                        Menampilkan {{ $activities->firstItem() ?? 0 }} hingga {{ $activities->lastItem() ?? 0 }} 
                        dari {{ $activities->total() }} affiliator
                    </p>
                    @include('components.pagination', ['paginator' => $activities->appends(request()->query())])
                </div>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-activity icon icon-xl text-secondary mb-3"></i>
                    <h3 class="text-secondary">Tidak ada aktivitas</h3>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Statistics Tab -->
        @if($activeTab == 'statistics')
        <div class="row">
            <!-- Stats Cards -->
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Aktivitas</div>
                        </div>
                        <div class="h1 mb-0 text-blue">{{ number_format($activityStats['total_activities'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Bulan Ini</div>
                        </div>
                        <div class="h1 mb-0 text-green">{{ number_format($activityStats['this_month'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Minggu Ini</div>
                        </div>
                        <div class="h1 mb-0 text-purple">{{ number_format($activityStats['this_week'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Hari Ini</div>
                        </div>
                        <div class="h1 mb-0 text-orange">{{ number_format($activityStats['today'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Aktivitas Bulanan</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-monthly-activity" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Aktivitas Berdasarkan Aksi</h3>
                    </div>
                    <div class="card-body p-0">
                        @if(isset($activityByAction) && $activityByAction->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                <tbody>
                                    @foreach($activityByAction as $activity)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary-lt">{{ $activity->action }}</span>
                                        </td>
                                        <td class="text-end">
                                            <div class="fw-bold">{{ number_format($activity->count) }}</div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <div class="text-secondary">Belum ada data</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@include('components.alert')
@if($activeTab == 'statistics' && isset($monthlyActivity))
<script>
document.addEventListener("DOMContentLoaded", function () {
    const monthlyActivity = @json($monthlyActivity);
    const months = monthlyActivity.map(item => item.month);
    const counts = monthlyActivity.map(item => item.count);

    if (window.ApexCharts && document.getElementById("chart-monthly-activity")) {
        new ApexCharts(document.getElementById("chart-monthly-activity"), {
            chart: {
                type: "area",
                fontFamily: "inherit",
                height: 300,
                toolbar: { show: false }
            },
            series: [{
                name: "Aktivitas",
                data: counts
            }],
            colors: ["var(--tblr-primary)"],
            xaxis: {
                categories: months
            },
            fill: {
                type: "gradient",
                gradient: {
                    opacityFrom: 0.5,
                    opacityTo: 0
                }
            }
        }).render();
    }
});
</script>
@endif
@endpush