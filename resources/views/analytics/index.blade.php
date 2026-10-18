@extends('layouts.app')

@section('content')
@php
    $leadStatusLabels = $leadStatus->keys()->map(fn ($status) => ucfirst($status))->values();
    $leadStatusValues = $leadStatus->values()->map(fn ($total) => (int) $total)->values();
    $monthlyLeadLabels = collect(range(1, 12))->map(fn ($month) => date('M', mktime(0, 0, 0, $month, 1)));
    $monthlyLeadValues = collect(range(1, 12))->map(fn ($month) => (int) ($monthlyLeads[$month] ?? 0));
    $hasLeadStatus = $leadStatusValues->sum() > 0;
    $hasMonthlyLeads = $monthlyLeadValues->sum() > 0;
@endphp

<div class="crm-dashboard crm-analytics-page">
    <header class="hero-card dashboard-hero analytics-hero">
        <div>
            <p class="dashboard-eyebrow">PERFORMANCE ANALYTICS</p>
            <h1>Analytics Command Center</h1>
            <p class="sub-text">Track pipeline growth, customer conversion, and team productivity from one place.</p>
        </div>
        <span class="analytics-hero-icon" aria-hidden="true"><i class="bi bi-bar-chart-line"></i></span>
    </header>

    <section class="kpi-grid analytics-kpi-grid" aria-label="CRM overview">
        <article class="kpi-card blue dashboard-kpi">
            <div><span>Total Leads</span><h2>{{ number_format($totalLeads) }}</h2><small>Business opportunities</small></div>
        </article>
        <article class="kpi-card green dashboard-kpi">
            <div><span>Total Customers</span><h2>{{ number_format($totalCustomers) }}</h2><small>Converted relationships</small></div>
        </article>
        <article class="kpi-card purple dashboard-kpi">
            <div><span>Conversion Rate</span><h2>{{ number_format($conversionRate, 2) }}%</h2><small>Lead success ratio</small></div>
        </article>
        <article class="kpi-card orange dashboard-kpi">
            <div><span>Pending Follow Ups</span><h2>{{ number_format($pendingFollowUps) }}</h2><small>Open next actions</small></div>
        </article>
        <article class="kpi-card cyan dashboard-kpi">
            <div><span>Completed Activities</span><h2>{{ number_format($totalActivities) }}</h2><small>Team productivity</small></div>
        </article>
    </section>

    <section class="analytics-grid analytics-chart-grid" aria-label="Lead analytics">
        <article class="analytics-panel">
            <div class="analytics-panel-heading">
                <div><p class="dashboard-eyebrow">PIPELINE ACTIVITY</p><h2>Monthly lead volume</h2></div>
                <span class="analytics-panel-icon"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span>
            </div>
            @if($hasMonthlyLeads)
                <div class="analytics-chart-wrap"><canvas id="analyticsLeadChart" aria-label="Monthly lead volume chart"></canvas></div>
            @else
                <div class="analytics-empty-state"><i class="bi bi-bar-chart" aria-hidden="true"></i><strong>No lead activity yet</strong><span>Lead totals will appear here as records are added.</span></div>
            @endif
        </article>

        <article class="analytics-panel">
            <div class="analytics-panel-heading">
                <div><p class="dashboard-eyebrow">CURRENT PIPELINE</p><h2>Lead status mix</h2></div>
                <span class="analytics-panel-icon"><i class="bi bi-pie-chart" aria-hidden="true"></i></span>
            </div>
            @if($hasLeadStatus)
                <div class="analytics-chart-wrap analytics-donut-wrap"><canvas id="analyticsStatusChart" aria-label="Lead status distribution chart"></canvas></div>
            @else
                <div class="analytics-empty-state"><i class="bi bi-pie-chart" aria-hidden="true"></i><strong>No pipeline data yet</strong><span>Status distribution will appear when leads are available.</span></div>
            @endif
        </article>
    </section>

    <section class="analytics-panel analytics-team-panel" aria-labelledby="analytics-team-heading">
        <div class="analytics-panel-heading">
            <div><p class="dashboard-eyebrow">TEAM OVERVIEW</p><h2 id="analytics-team-heading">Team lead activity</h2></div>
            <span class="analytics-panel-note">{{ $teamPerformance->count() }} team members</span>
        </div>
        @if($teamPerformance->isNotEmpty())
            <div class="analytics-table-wrap">
                <table class="analytics-table">
                    <thead><tr><th scope="col">Team member</th><th scope="col">Assigned leads</th></tr></thead>
                    <tbody>
                        @foreach($teamPerformance->sortByDesc('assigned_leads_count') as $member)
                            <tr>
                                <td><div class="analytics-member"><span class="analytics-member-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($member->name, 0, 1)) }}</span><strong>{{ $member->name }}</strong></div></td>
                                <td><span class="analytics-count-pill">{{ number_format($member->assigned_leads_count) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="analytics-empty-state analytics-table-empty"><i class="bi bi-people" aria-hidden="true"></i><strong>No team data available</strong><span>Team lead activity will appear here when team members are available.</span></div>
        @endif
    </section>
</div>

@if($hasMonthlyLeads || $hasLeadStatus)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const axis = {
                ticks: { color: '#94a3b8' },
                grid: { color: 'rgba(148, 163, 184, .12)' },
            };

            const leadCanvas = document.getElementById('analyticsLeadChart');
            if (leadCanvas) {
                new Chart(leadCanvas, {
                    type: 'bar',
                    data: {
                        labels: @json($monthlyLeadLabels),
                        datasets: [{
                            label: 'Leads',
                            data: @json($monthlyLeadValues),
                            backgroundColor: 'rgba(96, 165, 250, .72)',
                            borderColor: '#60a5fa',
                            borderWidth: 1,
                            borderRadius: 7,
                            maxBarThickness: 34,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { ...axis, grid: { display: false } },
                            y: { ...axis, beginAtZero: true, ticks: { ...axis.ticks, precision: 0 } },
                        },
                    },
                });
            }

            const statusCanvas = document.getElementById('analyticsStatusChart');
            if (statusCanvas) {
                new Chart(statusCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: @json($leadStatusLabels),
                        datasets: [{
                            data: @json($leadStatusValues),
                            backgroundColor: ['#60a5fa', '#a78bfa', '#34d399', '#fb7185', '#fbbf24'],
                            borderColor: '#101a30',
                            borderWidth: 3,
                            hoverOffset: 4,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: { legend: { position: 'bottom', labels: { color: '#cbd5e1', padding: 18, usePointStyle: true } } },
                    },
                });
            }
        });
    </script>
@endif
@endsection
