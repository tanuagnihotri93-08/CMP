@extends('layouts.app')

@section('content')

<div class="crm-dashboard crm-performance-page">

    <!-- HERO SECTION -->
    <div class="hero-card performance-hero">
        <div>
            <p class="dashboard-eyebrow">PERFORMANCE ANALYTICS</p>
            <h1>Performance Dashboard</h1>
            <p class="sub-text">Monitor CRM growth, sales activity and team productivity.</p>
            <p class="sub-text last-updated">
                <i class="bi bi-clock-history"></i>
                Last updated:
                {{ $lastUpdated ? $lastUpdated->format('d M Y, h:i A') : 'Not calculated yet' }}
            </p>
        </div>
        <div class="performance-icon">
            <i class="bi bi-speedometer2"></i>
        </div>
    </div>

    <!-- STATS GRID -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-person-lines-fill"></i></div>
            <div><p>Total Leads</p><h2>{{ $totalLeads }}</h2></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-people-fill"></i></div>
            <div><p>Total Customers</p><h2>{{ $totalCustomers }}</h2></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></div>
            <div><p>Conversion Rate</p><h2>{{ $conversionRate }}%</h2></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-check-circle"></i></div>
            <div><p>Activities</p><h2>{{ $totalActivities }}</h2></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-calendar-event"></i></div>
            <div><p>Pending Follow Ups</p><h2>{{ $totalPendingFollowUps }}</h2></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-bar-chart-line"></i></div>
            <div><p>Team Avg Score</p><h2>{{ $averageScore }}%</h2></div>
        </div>
    </div>

    <!-- LEAD GROWTH ANALYTICS -->
    <div class="analytics-full">
        <div class="analytics-card">
            <div class="card-header">
                <h3>Lead Growth Analytics</h3>
                <span>Monthly Performance</span>
            </div>
            @if(collect($monthlyLeads)->sum() > 0)
                <div class="chart-box"><canvas id="leadChart" aria-label="Monthly lead volume chart"></canvas></div>
            @else
                <div class="performance-empty-state"><i class="bi bi-bar-chart" aria-hidden="true"></i><strong>No lead activity yet</strong><span>Monthly lead totals will appear here as records are added.</span></div>
            @endif
        </div>
    </div>

    <!-- PERFORMANCE TREND -->
    <div class="analytics-full">
        <div class="analytics-card">
            <div class="card-header">
                <h3>Performance Trend</h3>
                <span>Daily Snapshots</span>
            </div>
            @if(count($performanceTrend) > 0)
                <div class="chart-box"><canvas id="trendChart"></canvas></div>
            @else
                <div class="performance-empty-state"><i class="bi bi-activity" aria-hidden="true"></i><strong>No performance history yet</strong><span>Trend data will appear after the next performance snapshot.</span></div>
            @endif
        </div>
    </div>

    <!-- PERFORMANCE GRID -->
    <div class="performance-grid">

        <!-- Lead Pipeline (full width) -->
        <div class="analytics-card pipeline-card">
            <div class="card-header">
                <h3>Lead Pipeline</h3>
                <span>Current Status</span>
            </div>
            <div class="pipeline-body">
                @if(collect($leadStatus)->sum() > 0)
                    <div class="chart-box small"><canvas id="statusChart" aria-label="Lead pipeline status chart"></canvas></div>
                @else
                    <div class="performance-empty-state performance-empty-state--compact"><i class="bi bi-pie-chart" aria-hidden="true"></i><strong>No pipeline data yet</strong><span>Lead status totals will appear here.</span></div>
                @endif
                <div class="pipeline-stats">
                    <div><p>New</p><h2>{{ $leadStatus['New'] ?? 0 }}</h2></div>
                    <div><p>Proposal</p><h2>{{ $leadStatus['Proposal'] ?? 0 }}</h2></div>
                    <div><p>Won</p><h2>{{ $leadStatus['Won'] ?? 0 }}</h2></div>
                    <div><p>Lost</p><h2>{{ $leadStatus['Lost'] ?? 0 }}</h2></div>
                </div>
            </div>
        </div>

        <!-- Team Efficiency Cards -->
        @forelse($performance as $item)
        <div class="analytics-card">
            <div class="efficiency-user">
                <div class="user-avatar">
                    {{ strtoupper(substr($item->user?->name ?? '?', 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-gray-300">{{ $item->user?->name ?? 'Unknown' }}</h3>
                    <p class="text-gray-300">{{ $item->user?->role?->display_name ?? $item->user?->role?->name ?? 'Team Member' }}</p>
                </div>
            </div>

            <div class="efficiency-row"><span>Total Leads</span><strong>{{ $item->leads_count }}</strong></div>
            <div class="efficiency-row"><span>Customers</span><strong>{{ $item->customers_count }}</strong></div>
            <div class="efficiency-row"><span>Activities</span><strong>{{ $item->activities_count }}</strong></div>
            <div class="efficiency-row"><span>Pending Follow Ups</span><strong>{{ $item->pending_followups_count }}</strong></div>

            <div class="score-box">
                <p>Performance Score</p>
                <h1>{{ $item->total_score }}%</h1>
                <div class="progress-bar">
                    <div style="width: {{ min($item->total_score, 100) }}%"></div>
                </div>

                @php $m = $meter[$item->user_id] ?? null; @endphp
                @if($m)
                    <div class="meter">
                        <p class="meter-diff {{ ($m['diff'] ?? 0) < 0 ? 'down' : 'up' }}">
                            @if($m['diff'] === null)
                                First snapshot
                            @elseif($m['diff'] > 0)
                                ↑ +{{ $m['diff'] }} points
                            @elseif($m['diff'] < 0)
                                ↓ {{ $m['diff'] }} points
                            @else
                                → No change
                            @endif
                        </p>
                        <span class="top-badge">Status: {{ $m['status'] }}</span>
                    </div>
                @endif
            </div>
        </div>
        @empty
        <div class="analytics-card performance-empty-card">
            <div class="performance-empty-state"><i class="bi bi-speedometer2" aria-hidden="true"></i><strong>No performance scores yet</strong><span>Score cards will appear after the next performance snapshot.</span></div>
        </div>
        @endforelse

        <!-- Performance Ranking -->
        <div class="analytics-card performance-table-card performance-ranking">
            <div class="card-header">
                <h3>Performance Ranking</h3>
                <span>Latest Snapshot</span>
            </div>

            @forelse($ranking as $rank)
                @php $isTop = $loop->first && $rank->total_score > 0; @endphp
                <div @class(['rank-row', 'top' => $isTop, 'rank-second' => $loop->iteration === 2, 'rank-third' => $loop->iteration === 3])>
                    <div class="rank-pos" aria-label="Rank {{ $loop->iteration }}">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="user-avatar small">
                        {{ strtoupper(substr($rank->user?->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="rank-info">
                        <strong>{{ $rank->user?->name ?? 'Unknown' }}</strong>
                        <span>{{ $rank->user?->role?->display_name ?? $rank->user?->role?->name ?? 'Team Member' }}</span>
                    </div>
                    @if($isTop)
                        <span class="top-badge"><i class="bi bi-trophy-fill"></i> Top Performer</span>
                    @elseif($loop->iteration === 2)
                        <span class="rank-medal">Runner-up</span>
                    @elseif($loop->iteration === 3)
                        <span class="rank-medal">3rd place</span>
                    @endif
                    <div class="rank-score">{{ $rank->total_score }}%</div>
                </div>
            @empty
                <div class="performance-empty-state performance-empty-state--compact"><i class="bi bi-trophy" aria-hidden="true"></i><strong>No ranking data yet</strong><span>Team rankings will appear when scores are available.</span></div>
            @endforelse
        </div>

        <!-- User Performance Table -->
        <div class="analytics-card performance-table-card">
            <div class="card-header">
                <h3 class="text-gray-300">User Performance</h3>
                <span>CRM Operations</span>
            </div>
            <div class="performance-table-wrap">
            <table class="performance-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Leads</th>
                        <th>Customers</th>
                        <th>Activities</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($performance as $data)
                    <tr>
                        <td>{{ $data->user?->name ?? 'Unknown' }}</td>
                        <td>{{ $data->leads_count }}</td>
                        <td>{{ $data->customers_count }}</td>
                        <td>{{ $data->activities_count }}</td>
                        <td><strong class="performance-table-score">{{ $data->total_score }}%</strong></td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><div class="performance-empty-state performance-empty-state--table"><strong>No performance records yet</strong><span>Scores will appear here after a performance snapshot is calculated.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

    </div>

</div>

@php
    $leadChartData = [];
    for ($mo = 1; $mo <= 12; $mo++) {
        $leadChartData[] = (int) ($monthlyLeads[$mo] ?? 0);
    }

    $statusChartData = [
        (int) ($leadStatus['New'] ?? 0),
        (int) ($leadStatus['Proposal'] ?? 0),
        (int) ($leadStatus['Won'] ?? 0),
        (int) ($leadStatus['Lost'] ?? 0),
    ];

    $trendLabels = collect($performanceTrend)
        ->flatMap(function ($t) { return $t['dates']; })
        ->unique()
        ->values()
        ->all();

    $trendColors = ['#3b82f6', '#22c55e', '#f97316', '#a855f7', '#ef4444', '#eab308'];
    $trendDatasets = [];
    foreach ($performanceTrend as $i => $t) {
        $byDate = collect($t['dates'])->combine($t['scores']);
        $points = [];
        foreach ($trendLabels as $label) {
            $points[] = $byDate->has($label) ? (int) $byDate->get($label) : null;
        }
        $trendDatasets[] = [
            'label' => $t['name'],
            'data' => $points,
            'borderColor' => $trendColors[$i % count($trendColors)],
            'backgroundColor' => $trendColors[$i % count($trendColors)],
            'borderWidth' => 3,
            'tension' => 0.4,
            'spanGaps' => true,
        ];
    }
@endphp

<!-- CHARTS SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const axisOpts = {
        ticks: { color: '#94a3b8' },
        grid: { color: 'rgba(255,255,255,0.05)' }
    };

    /* LEAD GROWTH CHART */
    const leadCanvas = document.getElementById('leadChart');
    if (leadCanvas) {
        new Chart(leadCanvas, {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{
                    label: 'Leads',
                    data: {!! json_encode($leadChartData) !!},
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { color: '#cbd5e1' } } },
                scales: { x: axisOpts, y: Object.assign({ beginAtZero: true }, axisOpts) }
            }
        });
    }

    /* PERFORMANCE TREND CHART */
    const trendCanvas = document.getElementById('trendChart');
    if (trendCanvas) {
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: {!! json_encode($trendLabels) !!},
                datasets: {!! json_encode($trendDatasets) !!}
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { color: '#cbd5e1' } } },
                scales: { x: axisOpts, y: Object.assign({ beginAtZero: true, suggestedMax: 100 }, axisOpts) }
            }
        });
    }

    /* LEAD PIPELINE DONUT */
    const statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: ['New', 'Proposal', 'Won', 'Lost'],
                datasets: [{
                    data: {!! json_encode($statusChartData) !!},
                    backgroundColor: ['#3b82f6', '#a855f7', '#22c55e', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#cbd5e1', padding: 20, font: { size: 13 } } }
                },
                animation: { animateScale: true, duration: 1200 }
            }
        });
    }
});
</script>

<style>
.crm-dashboard { animation: fadeIn .5s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

.performance-hero {
    position: relative; padding: 42px; display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 28px; border-radius: 30px; overflow: hidden;
    background: radial-gradient(circle at top right, rgba(37,99,235,.35), transparent 35%),
                linear-gradient(135deg, rgba(37,99,235,.15), rgba(124,58,237,.18));
    border: 1px solid rgba(255,255,255,.12); backdrop-filter: blur(25px);
}
.performance-hero::after {
    content: ""; position: absolute; width: 300px; height: 300px; right: -100px; bottom: -120px;
    background: #2563eb; filter: blur(120px); opacity: .25;
}
.last-updated { font-size: 13px; opacity: .8; margin-top: 8px; }
.performance-icon {
    height: 95px; width: 95px; border-radius: 30px; display: flex; justify-content: center;
    align-items: center; font-size: 40px; color: white;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    box-shadow: 0 25px 60px rgba(37,99,235,.45); animation: float 3s ease-in-out infinite;
}
@keyframes float { 50% { transform: translateY(-8px); } }

.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; margin-bottom: 28px; }
.stat-card {
    position: relative; overflow: hidden; padding: 24px; display: flex; align-items: center; gap: 18px;
    border-radius: 24px; background: linear-gradient(145deg, rgba(255,255,255,.12), rgba(255,255,255,.04));
    border: 1px solid rgba(255,255,255,.12); backdrop-filter: blur(20px); transition: .35s;
}
.stat-card:hover { transform: translateY(-8px); box-shadow: 0 25px 50px rgba(0,0,0,.35); }
.stat-card p { color: #94a3b8; font-size: 14px; margin: 0 0 6px; }
.stat-card h2 { font-size: 32px; margin: 0; color: white; }
.stat-icon {
    height: 62px; width: 62px; border-radius: 20px; display: flex; align-items: center;
    justify-content: center; font-size: 25px; color: white; box-shadow: 0 12px 25px rgba(0,0,0,.25);
}
.blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }
.purple { background: linear-gradient(135deg, #7c3aed, #a855f7); }
.green { background: linear-gradient(135deg, #16a34a, #22c55e); }
.orange { background: linear-gradient(135deg, #ea580c, #f97316); }

/* CARDS (gap fix: margin hata, grid gap use hota hai) */
.analytics-card {
    background: linear-gradient(145deg, rgba(255,255,255,.10), rgba(255,255,255,.03));
    border: 1px solid rgba(255,255,255,.12); border-radius: 28px; padding: 30px;
    backdrop-filter: blur(25px); box-shadow: 0 20px 50px rgba(0,0,0,.15);
    margin-bottom: 0;
}
.analytics-full { margin-bottom: 28px; }
.performance-grid {
    display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 28px;
    margin-bottom: 28px; align-items: stretch;
}
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.card-header h3 { margin: 0; font-size: 21px; color: white; }
.card-header span { color: #94a3b8; font-size: 13px; }

.chart-box { height: 360px; position: relative; }
.chart-box.small { height: 300px; }

/* PIPELINE: full width, chart left + stats right */
.pipeline-card { grid-column: 1 / -1; }
.pipeline-body { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; align-items: center; }
.pipeline-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
.pipeline-stats div {
    padding: 22px 10px; border-radius: 18px; text-align: center;
    background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08); transition: .3s;
}
.pipeline-stats div:hover { background: rgba(37,99,235,.18); transform: translateY(-5px); }
.pipeline-stats p { color: #94a3b8; font-size: 13px; margin: 0; }
.pipeline-stats h2 { color: white; margin: 8px 0 0; font-size: 28px; }

/* TEAM CARD */
.efficiency-user {
    display: flex; align-items: center; gap: 16px; padding: 18px; border-radius: 22px;
    background: rgba(255,255,255,.05); margin-bottom: 25px;
}
.user-avatar {
    height: 65px; width: 65px; border-radius: 50%; display: flex; align-items: center;
    justify-content: center; font-size: 26px; font-weight: 700; color: white;
    background: linear-gradient(135deg, #2563eb, #7c3aed); box-shadow: 0 15px 35px rgba(37,99,235,.35);
}
.user-avatar.small { height: 44px; width: 44px; font-size: 18px; }
.efficiency-row {
    display: flex; justify-content: space-between; padding: 17px 0;
    border-bottom: 1px solid rgba(255,255,255,.08); color: #cbd5e1;
}
.efficiency-row strong { color: white; font-size: 18px; }

.score-box {
    margin-top: 25px; padding: 25px; border-radius: 25px; text-align: center;
    background: linear-gradient(135deg, rgba(37,99,235,.20), rgba(124,58,237,.20));
}
.score-box p { color: #94a3b8; margin: 0; }
.score-box h1 { font-size: 52px; margin: 10px 0 16px; color: white; }
.progress-bar { height: 12px; border-radius: 20px; overflow: hidden; background: rgba(255,255,255,.12); }
.progress-bar div { height: 100%; border-radius: 20px; background: linear-gradient(90deg, #2563eb, #22c55e); }

/* METER */
.meter { margin-top: 20px; display: flex; flex-direction: column; align-items: center; gap: 12px; }
.meter-diff { margin: 0; font-weight: 600; font-size: 16px; color: #22c55e; }
.meter-diff.down { color: #ef4444; }
.score-box .meter .meter-diff { color: #22c55e; }
.score-box .meter .meter-diff.down { color: #ef4444; }

/* RANKING */
.rank-row {
    display: flex; align-items: center; gap: 16px; padding: 16px 18px; margin-bottom: 12px;
    border-radius: 18px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08); transition: .3s;
}
.rank-row:hover { background: rgba(37,99,235,.15); }
.rank-row.top {
    background: linear-gradient(135deg, rgba(234,179,8,.18), rgba(249,115,22,.12));
    border-color: rgba(234,179,8,.35);
}
.rank-pos {
    width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center;
    justify-content: center; font-weight: 700; color: white; background: rgba(255,255,255,.1);
}
.rank-row.top .rank-pos { background: linear-gradient(135deg, #eab308, #f97316); }
.rank-info { flex: 1; display: flex; flex-direction: column; }
.rank-info strong { color: white; }
.rank-info span { color: #94a3b8; font-size: 13px; }
.top-badge {
    display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
    color: #fde68a; background: rgba(234,179,8,.18); border: 1px solid rgba(234,179,8,.35);
}
.rank-score { font-size: 22px; font-weight: 700; color: white; }

/* TABLE */
.performance-table-card { grid-column: 1 / -1; }
.performance-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; }
.performance-table th { padding: 14px; color: #94a3b8; font-size: 13px; text-align: left; }
.performance-table td { padding: 18px; background: rgba(255,255,255,.05); color: #e2e8f0; }
.performance-table tr td:first-child { border-radius: 16px 0 0 16px; }
.performance-table tr td:last-child { border-radius: 0 16px 16px 0; }
.performance-table tbody tr { transition: .3s; }
.performance-table tbody tr:hover td { background: rgba(37,99,235,.15); }

@media (max-width: 900px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .performance-grid { grid-template-columns: 1fr; }
    .pipeline-body { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .stats-grid { grid-template-columns: 1fr; }
    .performance-hero { flex-direction: column; gap: 25px; align-items: flex-start; }
    .rank-row { flex-wrap: wrap; }
}
</style>

@endsection
