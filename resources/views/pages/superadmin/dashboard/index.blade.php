@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="row mb-3">
    <div class="col-md-6 col-xl-3">
        <div class="card w-100 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total User</div>
                </div>
                <div class="h1 mb-0">{{ number_format($data['stats']['total_users']) }}</div>
                <div class="d-flex mb-2">
                    <div class="text-secondary">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            {{ number_format($data['stats']['active_affiliators']) }} aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card w-100 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Project</div>
                </div>
                <div class="h1 mb-0">{{ number_format($data['stats']['total_projects']) }}</div>
                <div class="d-flex mb-2">
                    <div class="text-secondary">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            {{ number_format($data['stats']['active_projects']) }} aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card w-100 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Lead</div>
                </div>
                <div class="h1 mb-0">{{ number_format($data['stats']['total_leads']) }}</div>
                <div class="d-flex mb-2">
                    <div class="text-secondary">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            {{ number_format($data['stats']['verified_leads']) }} terverifikasi
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card w-100 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Komisi</div>
                </div>
                <div class="h1 mb-0">Rp {{ number_format($data['stats']['total_commission_earned'], 0, ',', '.') }}</div>
                <div class="d-flex mb-2">
                    <div class="text-secondary">
                        <span class="text-red d-inline-flex align-items-center lh-1">
                            Rp {{ number_format($data['stats']['total_commission_withdrawn'], 0, ',', '.') }} ditarik
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistik Bulanan</h3>
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

<div class="row mb-3">
    <!-- Top Projects -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Project Terbaik</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Lead</th>
                                <th>Komisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['top_projects'] as $project)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($project->logo)
                                            <img src="{{ $project->logo_url }}" alt="{{ $project->name }}" class="avatar avatar-sm me-2">
                                        @else
                                            <div class="avatar avatar-sm bg-primary-lt me-2">{{ substr($project->name, 0, 1) }}</div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $project->name }}</div>
                                            <div class="text-secondary">{{ $project->slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-green-lt">{{ $project->verified_leads_count }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold">Rp {{ number_format($project->total_commission ?? 0, 0, ',', '.') }}</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-secondary">Belum ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Affiliators -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Affiliator Terbaik</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>Affiliator</th>
                                <th>Lead</th>
                                <th>Komisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['top_affiliators'] as $affiliator)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $affiliator->profile_photo_url }}" alt="{{ $affiliator->name }}" class="avatar avatar-sm me-2">
                                        <div>
                                            <div class="fw-bold">{{ $affiliator->name }}</div>
                                            <div class="text-secondary">{{ $affiliator->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-green-lt">{{ $affiliator->total_leads }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold">Rp {{ number_format($affiliator->total_commission ?? 0, 0, ',', '.') }}</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-secondary">Belum ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <!-- Recent Activities -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Aktivitas Terbaru</h3>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @forelse($data['recent_activities'] as $activity)
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="avatar avatar-sm">{{ $activity->user->initials ?? 'U' }}</span>
                            </div>
                            <div class="col text-truncate">
                                <div class="text-reset d-block">{{ $activity->description }}</div>
                                <div class="d-block text-secondary text-truncate mt-n1">
                                    oleh {{ $activity->user->name ?? 'Unknown' }}
                                    @if($activity->project)
                                        di {{ $activity->project->name }}
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="text-secondary">{{ $activity->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-center text-secondary">
                        Belum ada aktivitas terbaru
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Withdrawals -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Penarikan Terbaru</h3>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @forelse($data['latest_withdrawals'] as $withdrawal)
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col text-truncate">
                                <div class="text-reset d-block">{{ $withdrawal->user->name }}</div>
                                <div class="d-block text-secondary text-truncate mt-n1">
                                    Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-{{ $withdrawal->status_color }}-lt">{{ $withdrawal->status_label }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-center text-secondary">
                        Belum ada penarikan
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Chart Leads
    const leadsData = @json($data['monthly_stats']['leads']);
    const commissionsData = @json($data['monthly_stats']['commissions']);

    // Prepare data for charts
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const leadsSeries = [];
    const commissionsSeries = [];
    
    for (let i = 1; i <= 12; i++) {
        leadsSeries.push(leadsData[i] || 0);
        commissionsSeries.push(commissionsData[i] || 0);
    }

    // Leads Chart
    if (window.ApexCharts) {
        new ApexCharts(document.getElementById("chart-leads"), {
            chart: {
                type: "line",
                fontFamily: "inherit",
                height: 300,
                toolbar: { show: false },
                animations: { enabled: false }
            },
            stroke: {
                width: 2,
                lineCap: "round",
                curve: "smooth"
            },
            series: [{
                name: "Lead",
                data: leadsSeries
            }],
            colors: ["var(--tblr-primary)"],
            xaxis: {
                categories: months,
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
            }
        }).render();
    }

    // Commission Chart
    if (window.ApexCharts) {
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
                data: commissionsSeries
            }],
            colors: ["var(--tblr-green)"],
            xaxis: {
                categories: months,
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
});
</script>
@endpush