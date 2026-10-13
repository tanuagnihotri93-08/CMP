@extends('layouts.app')

@section('content')

<div class="crm-dashboard">

    <!-- HERO SECTION -->
    <div class="hero-card performance-hero">
        <div>
            <p class="dashboard-eyebrow">PERFORMANCE ANALYTICS</p>
            <h1>Performance Dashboard</h1>
            <p class="sub-text">Monitor CRM growth, sales activity and team productivity.</p>
        </div>
        <div class="performance-icon">
            <i class="bi bi-speedometer2"></i>
        </div>
    </div>

    <!-- STATS GRID -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="bi bi-person-lines-fill"></i>
            </div>
            <div>
                <p>Total Leads</p>
                <h2>{{ $totalLeads }}</h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <p>Total Customers</p>
                <h2>{{ $totalCustomers }}</h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <p>Conversion Rate</p>
                <h2>{{ $conversionRate }}%</h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <p>Activities</p>
                <h2>{{ $totalActivities }}</h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="bi bi-calendar-event"></i>
            </div>
            <div>
                <p>Pending Follow Ups</p>
                <h2>{{ $totalPendingFollowUps }}</h2>
            </div>
        </div>
    </div>

    <!-- LEAD GROWTH ANALYTICS -->
    <div class="analytics-full">
        <div class="analytics-card">
            <div class="card-header">
                <h3>Lead Growth Analytics</h3>
                <span>Monthly Performance</span>
            </div>
            <div class="chart-box">
                <canvas id="leadChart"></canvas>
            </div>
        </div>
    </div>

    <!-- PERFORMANCE GRID -->
    <div class="performance-grid">

        <!-- Lead Pipeline -->
        <div class="analytics-card">
            <div class="card-header">
                <h3>Lead Pipeline</h3>
                <span>Current Status</span>
            </div>
            <div class="chart-box small">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="pipeline-stats">
                <div>
                    <p>New</p>
                    <h2>{{ $leadStatus['New'] ?? 0 }}</h2>
                </div>
                <div>
                    <p>Proposal</p>
                    <h2>{{ $leadStatus['Proposal'] ?? 0 }}</h2>
                </div>
                <div>
                    <p>Won</p>
                    <h2>{{ $leadStatus['Won'] ?? 0 }}</h2>
                </div>
                <div>
                    <p>Lost</p>
                    <h2>{{ $leadStatus['Lost'] ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <!-- Team Efficiency Cards -->
        @foreach($performance as $item)
        <div class="analytics-card">
            <div class="efficiency-user">
                <div class="user-avatar">
                    {{ strtoupper(substr($item['user']->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-gray-300">{{ $item['user']->name }}</h3>
                    <p class="text-gray-300">{{ $item['user']->role->display_name ?? 'Team Member' }}</p>
                </div>
            </div>

            <div class="efficiency-row">
                <span>Total Leads</span>
                <strong>{{ $item['leads'] }}</strong>
            </div>
            <div class="efficiency-row">
                <span>Customers</span>
                <strong>{{ $item['customers'] }}</strong>
            </div>
            <div class="efficiency-row">
                <span>Activities</span>
                <strong>{{ $item['activities'] }}</strong>
            </div>

            <div class="score-box">
                <p>Performance Score</p>
                <h1>{{ $item['score'] }}%</h1>
                <div class="progress-bar">
                    <div style="width: {{ $item['score'] }}%"></div>
                </div>
            </div>
        </div>
        @endforeach

        <!-- User Performance Table -->
        <div class="analytics-card performance-table-card">
            <div class="card-header">
                <h3 class="text-gray-300">User Performance</h3>
                <span>CRM Operations</span>
            </div>
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
                    @foreach($performance as $data)
                    <tr>
                        <td>{{ $data['user']->name }}</td>
                        <td>{{ $data['leads'] }}</td>
                        <td>{{ $data['customers'] }}</td>
                        <td>{{ $data['activities'] }}</td>
                        <td>{{ $data['score'] }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- CHARTS SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {

    /* ==========================
       LEAD GROWTH CHART
    ========================== */
    const leadCanvas = document.getElementById('leadChart');
    if (leadCanvas) {
        new Chart(leadCanvas, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [{
                    label: 'Leads',
                    data: [
                        {{ $monthlyLeads[1] ?? 0 }},
                        {{ $monthlyLeads[2] ?? 0 }},
                        {{ $monthlyLeads[3] ?? 0 }},
                        {{ $monthlyLeads[4] ?? 0 }},
                        {{ $monthlyLeads[5] ?? 0 }},
                        {{ $monthlyLeads[6] ?? 0 }},
                        {{ $monthlyLeads[7] ?? 0 }},
                        {{ $monthlyLeads[8] ?? 0 }},
                        {{ $monthlyLeads[9] ?? 0 }},
                        {{ $monthlyLeads[10] ?? 0 }}
                    ],
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
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: '#cbd5e1' }
                    }
                },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                }
            }
        });
    }

    /* ==========================
       LEAD PIPELINE DONUT
    ========================== */
    const statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: ['New', 'Proposal', 'Won', 'Lost'],
                datasets: [{
                    data: [
                        {{ $leadStatus['New'] ?? 0 }},
                        {{ $leadStatus['Proposal'] ?? 0 }},
                        {{ $leadStatus['Won'] ?? 0 }},
                        {{ $leadStatus['Lost'] ?? 0 }}
                    ],
                    backgroundColor: ['#3b82f6', '#a855f7', '#22c55e', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#cbd5e1', padding: 20, font: { size: 13 } }
                    }
                },
                animation: {
                    animateScale: true,
                    duration: 1200
                }
            }
        });
    }

});
</script>

<style>
/* =====================================
   PERFORMANCE DASHBOARD PREMIUM UI
===================================== */
.crm-dashboard {
    animation: fadeIn .5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

/* HERO */
.performance-hero {
    position: relative;
    padding: 42px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    border-radius: 30px;
    overflow: hidden;
    background: radial-gradient(circle at top right, rgba(37, 99, 235, .35), transparent 35%),
                linear-gradient(135deg, rgba(37, 99, 235, .15), rgba(124, 58, 237, .18));
    border: 1px solid rgba(255, 255, 255, .12);
    backdrop-filter: blur(25px);
}

.performance-hero::after {
    content: "";
    position: absolute;
    width: 300px;
    height: 300px;
    right: -100px;
    bottom: -120px;
    background: #2563eb;
    filter: blur(120px);
    opacity: .25;
}

.performance-icon {
    height: 95px;
    width: 95px;
    border-radius: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 40px;
    color: white;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    box-shadow: 0 25px 60px rgba(37, 99, 235, .45);
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    50% { transform: translateY(-8px); }
}

/* STAT CARDS */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 22px;
    margin-bottom: 28px;
}

.stat-card {
    position: relative;
    overflow: hidden;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    border-radius: 24px;
    background: linear-gradient(145deg, rgba(255, 255, 255, .12), rgba(255, 255, 255, .04));
    border: 1px solid rgba(255, 255, 255, .12);
    backdrop-filter: blur(20px);
    transition: .35s;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, .35);
}

.stat-card p {
    color: #94a3b8;
    font-size: 14px;
    margin: 0 0 6px;
}

.stat-card h2 {
    font-size: 32px;
    margin: 0;
    color: white;
}

.stat-icon {
    height: 62px;
    width: 62px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 25px;
    color: white;
    box-shadow: 0 12px 25px rgba(0, 0, 0, .25);
}

.blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }
.purple { background: linear-gradient(135deg, #7c3aed, #a855f7); }
.green { background: linear-gradient(135deg, #16a34a, #22c55e); }
.orange { background: linear-gradient(135deg, #ea580c, #f97316); }

/* ANALYTICS CARDS */
.analytics-card {
    background: linear-gradient(145deg, rgba(255, 255, 255, .10), rgba(255, 255, 255, .03));
    border: 1px solid rgba(255, 255, 255, .12);
    border-radius: 28px;
    padding: 30px;
    backdrop-filter: blur(25px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, .15);
    height: max-content;
    margin-bottom: 28px;
}

.analytics-full {
    margin-bottom: 28px;
}

.performance-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 28px;
    margin-bottom: 28px;
    align-items: start;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.card-header h3 {
    margin: 0;
    font-size: 21px;
    color: white;
}

.card-header span {
    color: #94a3b8;
    font-size: 13px;
}

/* CHART AREA */
.chart-box {
    height: 360px;
    position: relative;
}

.chart-box.small {
    height: 300px;
}

/* PIPELINE */
.pipeline-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-top: 25px;
}

.pipeline-stats div {
    padding: 18px 10px;
    border-radius: 18px;
    text-align: center;
    background: rgba(255, 255, 255, .05);
    border: 1px solid rgba(255, 255, 255, .08);
    transition: .3s;
}

.pipeline-stats div:hover {
    background: rgba(37, 99, 235, .18);
    transform: translateY(-5px);
}

.pipeline-stats p {
    color: #94a3b8;
    font-size: 13px;
    margin: 0;
}

.pipeline-stats h2 {
    color: white;
    margin: 8px 0 0;
    font-size: 28px;
}

/* TEAM CARD */
.efficiency-user {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px;
    border-radius: 22px;
    background: rgba(255, 255, 255, .05);
    margin-bottom: 25px;
}

.user-avatar {
    height: 65px;
    width: 65px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 700;
    color: white;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    box-shadow: 0 15px 35px rgba(37, 99, 235, .35);
}

.efficiency-row {
    display: flex;
    justify-content: space-between;
    padding: 17px 0;
    border-bottom: 1px solid rgba(255, 255, 255, .08);
    color: #cbd5e1;
}

.efficiency-row strong {
    color: white;
    font-size: 18px;
}

.score-box {
    margin-top: 25px;
    padding: 25px;
    border-radius: 25px;
    text-align: center;
    background: linear-gradient(135deg, rgba(37, 99, 235, .20), rgba(124, 58, 237, .20));
}

.score-box p {
    color: #94a3b8;
    margin: 0;
}

.score-box h1 {
    font-size: 52px;
    margin: 10px 0;
    color: white;
}

.progress-bar {
    height: 12px;
    border-radius: 20px;
    overflow: hidden;
    background: rgba(255, 255, 255, .12);
}

.progress-bar div {
    height: 100%;
    border-radius: 20px;
    background: linear-gradient(90deg, #2563eb, #22c55e);
}

/* TABLE */
.performance-table-card {
    grid-column: 1 / -1;
}

.performance-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 12px;
}

.performance-table th {
    padding: 14px;
    color: #94a3b8;
    font-size: 13px;
    text-align: left;
}

.performance-table td {
    padding: 18px;
    background: rgba(255, 255, 255, .05);
    color: #e2e8f0;
}

.performance-table tr td:first-child {
    border-radius: 16px 0 0 16px;
}

.performance-table tr td:last-child {
    border-radius: 0 16px 16px 0;
}

.performance-table tbody tr {
    transition: .3s;
}

.performance-table tbody tr:hover td {
    background: rgba(37, 99, 235, .15);
}

@media(max-width:1200px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media(max-width:900px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    .performance-grid {
        grid-template-columns: 1fr;
    }
}

@media(max-width:600px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .performance-hero {
        flex-direction: column;
        gap: 25px;
        align-items: flex-start;
    }
    .pipeline-stats {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

@endsection