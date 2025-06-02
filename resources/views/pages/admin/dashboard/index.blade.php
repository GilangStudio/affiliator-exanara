@extends('layouts.main')

@section('title', 'Dashboard Admin')

@section('content')
<!-- Welcome Header -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h1 class="mb-1">Selamat datang, {{ Auth::user()->name }}!</h1>
                        <div class="text-secondary">Dashboard Admin - Kelola affiliator dan project Anda</div>
                    </div>
                    <div class="col-auto">
                        <div class="btn-list">
                            <a href="{{ route('admin.affiliators.index') }}" class="btn btn-primary">
                                <i class="ti ti-users me-1"></i>
                                Kelola Affiliator
                            </a>
                            @if(isset($stats['pending_withdrawals']) && $stats['pending_withdrawals'] > 0)
                            <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}" class="btn btn-warning">
                                <i class="ti ti-credit-card me-1"></i>
                                {{ $stats['pending_withdrawals'] }} Penarikan Pending
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($stats) && !empty($stats))
<!-- Stats Cards -->
<div class="row mb-3">
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Affiliator</div>
                </div>
                <div class="h1 mb-0">{{ number_format($stats['total_affiliators'] ?? 0) }}</div>
                <div class="d-flex mb-2">
                    <div class="text-secondary">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            {{ number_format($stats['active_affiliators'] ?? 0) }} aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Lead</div>
                </div>
                <div class="h1 mb-0">{{ number_format($stats['total_leads'] ?? 0) }}</div>
                <div class="d-flex mb-2">
                    <div class="text-secondary">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            {{ number_format($stats['verified_leads'] ?? 0) }} terverifikasi
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Komisi</div>
                </div>
                <div class="h1 mb-0">Rp {{ number_format($stats['total_commission'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Penarikan Pending</div>
                </div>
                <div class="h1 mb-0 text-warning">{{ number_format($stats['pending_withdrawals'] ?? 0) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Conversion Rate</div>
                </div>
                <div class="h1 mb-0 text-purple">
                    @php
                        $totalLeads = $stats['total_leads'] ?? 0;
                        $verifiedLeads = $stats['verified_leads'] ?? 0;
                        $conversionRate = $totalLeads > 0 ? ($verifiedLeads / $totalLeads) * 100 : 0;
                    @endphp
                    {{ number_format($conversionRate, 1) }}%
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Project Dikelola</div>
                </div>
                <div class="h1 mb-0 text-blue">{{ isset($projects) ? $projects->count() : 0 }}</div>
            </div>
        </div>
    </div>
</div>
@else
<!-- No Projects Warning -->
<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-warning">
            <div class="d-flex">
                <div>
                    <i class="ti ti-alert-triangle me-2"></i>
                </div>
                <div>
                    <h4 class="alert-title">Tidak Ada Project</h4>
                    Anda belum ditugaskan untuk mengelola project apapun. Silakan hubungi Super Admin untuk mendapatkan akses ke project.
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($monthly_stats) && !empty($monthly_stats))
<!-- Charts Row -->
<div class="row mb-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistik Lead Bulanan</h3>
            </div>
            <div class="card-body">
                <div id="chart-leads" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Komisi Bulanan</h3>
            </div>
            <div class="card-body">
                <div id="chart-commission" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Data Tables Row -->
<div class="row">
    <!-- Recent Leads -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Lead Terbaru</h3>
                <a href="{{ route('admin.leads.index') }}" class="btn btn-sm btn-outline-primary">
                    Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                @if(isset($recent_leads) && $recent_leads->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Affiliator</th>
                                    <th>Status</th>
                                    <th>Komisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_leads as $lead)
                                <tr>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $lead->customer_name }}</div>
                                            <div class="text-secondary small">{{ $lead->customer_phone }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-xs me-1">
                                                {{ $lead->affiliatorProject->user->initials }}
                                            </span>
                                            <span class="text-truncate" style="max-width: 100px;">
                                                {{ $lead->affiliatorProject->user->name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusColor = match($lead->verification_status) {
                                                'verified' => 'success',
                                                'rejected' => 'danger',
                                                default => 'warning'
                                            };
                                            $statusLabel = match($lead->verification_status) {
                                                'verified' => 'Terverifikasi',
                                                'rejected' => 'Ditolak',
                                                default => 'Pending'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}-lt">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($lead->commission_earned > 0)
                                            <div class="fw-bold text-success">Rp {{ number_format($lead->commission_earned, 0, ',', '.') }}</div>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ti ti-file-off icon icon-xl mb-2 text-secondary"></i>
                        <div class="text-secondary">Belum ada lead terbaru</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Withdrawals -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Penarikan Terbaru</h3>
                @if(isset($stats['pending_withdrawals']) && $stats['pending_withdrawals'] > 0)
                    <span class="badge bg-warning-lt">{{ $stats['pending_withdrawals'] }} pending</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if(isset($recent_withdrawals) && $recent_withdrawals->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Affiliator</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_withdrawals as $withdrawal)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-xs me-1">
                                                {{ $withdrawal->user->initials }}
                                            </span>
                                            <span class="text-truncate" style="max-width: 100px;">
                                                {{ $withdrawal->user->name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $statusColor = match($withdrawal->status) {
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'processed' => 'blue',
                                                default => 'secondary'
                                            };
                                            $statusLabel = match($withdrawal->status) {
                                                'pending' => 'Pending',
                                                'approved' => 'Disetujui',
                                                'rejected' => 'Ditolak',
                                                'processed' => 'Diproses',
                                                default => 'Unknown'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}-lt">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($withdrawal->status == 'pending')
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-success"
                                                        onclick="updateWithdrawalStatus({{ $withdrawal->id }}, 'approved')">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="updateWithdrawalStatus({{ $withdrawal->id }}, 'rejected')">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-secondary small">
                                                {{ $withdrawal->updated_at->format('d/m') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ti ti-credit-card icon icon-xl mb-2 text-secondary"></i>
                        <div class="text-secondary">Belum ada penarikan</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Projects Overview -->
@if(isset($projects) && $projects->count() > 0)
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Project yang Dikelola</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Affiliator</th>
                                <th>Lead</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($project->logo)
                                            <img src="{{ $project->logo_url }}" alt="{{ $project->name }}" 
                                                 class="avatar avatar-sm me-2">
                                        @else
                                            <div class="avatar avatar-sm bg-primary-lt me-2">
                                                {{ substr($project->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $project->name }}</div>
                                            <div class="text-secondary small">{{ $project->slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-lt">{{ $project->affiliator_projects_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-lt">{{ $project->leads_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $project->is_active ? 'success' : 'secondary' }}-lt">
                                        {{ $project->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Recent Activities -->
@if(isset($recent_activities) && $recent_activities->count() > 0)
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Aktivitas Terbaru</h3>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($recent_activities as $activity)
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="avatar avatar-sm">
                                    {{ $activity->user->initials ?? 'U' }}
                                </span>
                            </div>
                            <div class="col text-truncate">
                                <div class="text-reset d-block">{{ $activity->description }}</div>
                                <div class="d-block text-secondary text-truncate mt-n1">
                                    {{ $activity->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="text-secondary">{{ $activity->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
@include('components.alert')
@include('components.toast')
<script>
document.addEventListener("DOMContentLoaded", function () {
    @if(isset($monthly_stats) && !empty($monthly_stats))
    // Prepare monthly stats data
    const monthlyStats = @json($monthly_stats);
    const months = Object.keys(monthlyStats);
    const leadData = months.map(month => monthlyStats[month].leads || 0);
    const verifiedLeadData = months.map(month => monthlyStats[month].verified_leads || 0);
    const commissionData = months.map(month => monthlyStats[month].commission || 0);
    const monthLabels = months.map(month => {
        const date = new Date(month + '-01');
        return date.toLocaleDateString('id-ID', { month: 'short', year: '2-digit' });
    });

    // Leads Chart
    if (window.ApexCharts && document.getElementById("chart-leads")) {
        new ApexCharts(document.getElementById("chart-leads"), {
            chart: {
                type: "line",
                fontFamily: "inherit",
                height: 300,
                toolbar: { show: false },
                animations: { enabled: false }
            },
            stroke: {
                width: [2, 2],
                lineCap: "round",
                curve: "smooth"
            },
            series: [{
                name: "Total Lead",
                data: leadData
            }, {
                name: "Lead Terverifikasi",
                data: verifiedLeadData
            }],
            colors: ["var(--tblr-primary)", "var(--tblr-success)"],
            xaxis: {
                categories: monthLabels,
                labels: { padding: 0 }
            },
            yaxis: {
                labels: { padding: 4 }
            },
            grid: {
                strokeDashArray: 4
            },
            tooltip: {
                theme: "dark"
            },
            legend: {
                show: true,
                position: 'top'
            }
        }).render();
    }

    // Commission Chart
    if (window.ApexCharts && document.getElementById("chart-commission")) {
        new ApexCharts(document.getElementById("chart-commission"), {
            chart: {
                type: "bar",
                fontFamily: "inherit",
                height: 300,
                toolbar: { show: false },
                animations: { enabled: false }
            },
            plotOptions: {
                bar: {
                    columnWidth: "50%"
                }
            },
            series: [{
                name: "Komisi",
                data: commissionData
            }],
            colors: ["var(--tblr-green)"],
            xaxis: {
                categories: monthLabels,
                labels: { padding: 0 }
            },
            yaxis: {
                labels: { 
                    padding: 4,
                    formatter: function (val) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                    }
                }
            },
            grid: {
                strokeDashArray: 4
            },
            tooltip: {
                theme: "dark",
                y: {
                    formatter: function (val) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                    }
                }
            }
        }).render();
    }
    @endif
});

// Update withdrawal status function
function updateWithdrawalStatus(withdrawalId, status) {
    const statusText = status === 'approved' ? 'menyetujui' : 'menolak';
    
    if (confirm(`Apakah Anda yakin ingin ${statusText} penarikan ini?`)) {
        fetch(`/${withdrawalId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: status,
                admin_notes: status === 'rejected' ? 'Ditolak dari dashboard' : null
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Gagal memperbarui status penarikan', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan', 'error');
        });
    }
}
</script>
@endpush 