@extends('layouts.app')

@section('content')

<div class="crm-dashboard">


    <!-- HERO HEADER -->

    <div class="hero-card dashboard-hero">

        <div>

            <p class="dashboard-eyebrow">CRM OVERVIEW</p>

            <p class="welcome-text">
                Welcome back, {{ Auth::user()->name }}
            </p>

            <h1>
                CRM Command Center
            </h1>

            <p class="sub-text">
                A clear view of your pipeline, customer relationships, and the work ahead.
            </p>

        </div>


        <div class="hero-actions">

            @if(auth()->user()->hasPermission('leads.create'))
                <a href="{{ route('leads.create') }}" class="primary-btn">
                    + Create Lead
                </a>
            @endif

            @if(auth()->user()->hasPermission('customers.create'))
                <a href="{{ route('customers.create') }}" class="secondary-btn">
                    Add Customer
                </a>
            @endif

        </div>

    </div>




    <!-- KPI SECTION -->

    <div class="kpi-grid">


        <div class="kpi-card blue dashboard-kpi kpi-leads">

            <div class="kpi-icon">
                <i class="bi bi-person-lines-fill"></i>
            </div>


            <div>

                <span>
                    Total Leads
                </span>

                <h2>
                    {{ $totalLeads }}
                </h2>

                <small>
                    Active business opportunities
                </small>

            </div>


        </div>


        <div class="kpi-card purple dashboard-kpi kpi-won">

            <div class="kpi-icon">
                <i class="bi bi-trophy"></i>
            </div>


            <div>

                <span>
                    Won Leads / Converted Customers
                </span>

                <h2>
                    {{ $wonLeads }}
                </h2>

                <small>
                    Leads won in the CRM pipeline
                </small>

            </div>


        </div>



        <div class="kpi-card green dashboard-kpi kpi-customers">

            <div class="kpi-icon">
                <i class="bi bi-people-fill"></i>
            </div>


            <div>

                <span>
                    Total Customers
                </span>

                <h2>
                    {{ $totalCustomers }}
                </h2>

                <small>
                    Registered clients
                </small>

            </div>


        </div>





        <div class="kpi-card purple dashboard-kpi kpi-followups">

            <div class="kpi-icon">
                <i class="bi bi-calendar-check"></i>
            </div>


            <div>

                <span>
                    Pending Follow Ups
                </span>

                <h2>
                    {{ $pendingFollowUps }}
                </h2>

                <small>
                    Upcoming interactions
                </small>

            </div>


        </div>





        <div class="kpi-card orange dashboard-kpi kpi-activities">

            <div class="kpi-icon">
                <i class="bi bi-lightning-charge"></i>
            </div>


            <div>

                <span>
                    Completed Activities
                </span>

                <h2>
                    {{ $completedActivities }}
                </h2>

                <small>
                    CRM operations
                </small>

            </div>


        </div>



    </div>







    <!-- MAIN CONTENT -->


    <div class="dashboard-grid">



        <!-- PIPELINE -->


        <div class="glass-panel dashboard-pipeline-panel">


            <div class="panel-title">

                <h3>
                    Lead Pipeline
                </h3>

                <span>
                    Overview
                </span>

            </div>



            <div class="pipeline">


                <div class="pipeline-stage pipeline-stage-new">

                    <span>
                        New
                    </span>

                    <strong>
                        {{ $newLeads }}
                    </strong>

                </div>



                <div class="pipeline-stage pipeline-stage-proposal">

                    <span>
                        Proposal
                    </span>

                    <strong>
                        {{ $proposalLeads }}
                    </strong>

                </div>

                <div class="pipeline-stage pipeline-stage-won">

                    <span>
                        Won
                    </span>

                    <strong>
                        {{ $wonLeads }}
                    </strong>

                </div>

                <div class="pipeline-stage pipeline-stage-customers">

                    <span>
                        Customers
                    </span>

                    <strong>
                        {{ $totalCustomers }}
                    </strong>

                </div>



                <div class="pipeline-stage pipeline-stage-followups">

                    <span>
                        Follow Ups
                    </span>

                    <strong>
                        {{ $totalFollowUps }}
                    </strong>

                </div>


            </div>

            <div class="dashboard-analytics">
                <div>
                    <span>Conversion rate</span>
                    <strong>{{ $conversionRate }}%</strong>
                </div>
                <div>
                    <span>Follow-up completion</span>
                    <strong>{{ $followUpCompletionRate }}%</strong>
                </div>
                <div>
                    <span>New leads this month</span>
                    <strong>{{ $leadsAddedThisMonth }}</strong>
                </div>
                <div>
                    <span>Recent business activity | 30 days</span>
                    <strong>{{ $recentBusinessActivity }}</strong>
                </div>
            </div>

            <div class="dashboard-growth-summary">
                <span>Monthly lead growth</span>
                @foreach($monthlyLeadGrowth as $month => $count)
                    <span>{{ $month }} <strong>{{ $count }}</strong></span>
                @endforeach
            </div>


        </div>





        <!-- QUICK ACTION -->


        <div class="glass-panel dashboard-quick-panel">


            <div class="panel-title">
                <div>
                    <h3>Quick Actions</h3>
                    <p>Move your work forward</p>
                </div>

            </div>


            <div class="quick-links">


                @if(auth()->user()->hasPermission('leads.create'))
                    <a class="dashboard-action-card" href="{{ route('leads.create') }}">
                        <i class="bi bi-plus-circle"></i>
                        New Lead
                    </a>
                @endif


                @if(auth()->user()->hasPermission('customers.create'))
                    <a class="dashboard-action-card" href="{{ route('customers.create') }}">
                        <i class="bi bi-person-plus"></i>
                        New Customer
                    </a>
                @endif



                @if(auth()->user()->hasPermission('follow_ups.create'))
                    <a class="dashboard-action-card" href="{{ route('followups.create') }}">
                        <i class="bi bi-clock"></i>
                        Schedule Follow Up
                    </a>
                @endif



            </div>


        </div>



    </div>






    <!-- RECENT LEADS -->


    <div class="glass-panel table-panel dashboard-leads-panel">


        <div class="panel-title">

            <h3>
                Recent Leads
            </h3>


            <a href="{{ route('leads.index') }}">
                View All
            </a>


        </div>



        <div class="dashboard-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLeads as $lead)
                        <tr class="dashboard-lead-row">
                            <td>{{ $lead->name }}</td>
                            <td>{{ $lead->company_name }}</td>
                            <td>
                                <span class="status {{ strtolower($lead->status) }}">
                                    {{ $lead->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


    </div>


    <div class="dashboard-grid dashboard-records-grid">
        <div class="glass-panel table-panel dashboard-activity-panel">
            <div class="panel-title">
                <div>
                    <h3>Recent Activities</h3>
                    <p>Latest interactions recorded in your CRM</p>
                </div>
                <a href="{{ route('activities.index') }}">View All</a>
            </div>
            <div class="dashboard-table-wrap">
                <table>
                    <thead>
                        <tr><th>Activity</th><th>Type</th><th>Date</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentActivities as $activity)
                            <tr class="dashboard-activity-row">
                                <td><a class="dashboard-record-link" href="{{ route('activities.show', $activity) }}">{{ $activity->subject }}</a></td>
                                <td>{{ $activity->type }}</td>
                                <td>{{ $activity->activity_date?->format('M j, Y') ?? '—' }}</td>
                                <td><span class="dashboard-status {{ $activity->status }}">{{ ucfirst($activity->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="dashboard-empty">No activities recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass-panel table-panel dashboard-followup-panel">
            <div class="panel-title">
                <div>
                    <h3>Upcoming Follow Ups</h3>
                    <p>Next scheduled lead reminders</p>
                </div>
                <a href="{{ route('followups.index') }}">View All</a>
            </div>
            <div class="dashboard-upcoming-list">
                @forelse($upcomingFollowUps as $followUp)
                    <a class="dashboard-followup-item" href="{{ route('followups.show', $followUp) }}">
                        <span class="dashboard-followup-date">{{ $followUp->date?->format('M j') ?? '—' }}</span>
                        <span class="dashboard-followup-copy">
                            <strong>{{ $followUp->type }}</strong>
                            <small>{{ $followUp->lead?->name ?? 'Lead unavailable' }}</small>
                        </span>
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </a>
                @empty
                    <p class="dashboard-empty">No upcoming follow ups are scheduled.</p>
                @endforelse
            </div>
        </div>
    </div>


</div>



<style>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&display=swap');
/* =====================================================
   CRM DASHBOARD - COMPLETE STYLE (replace the whole <style>)
===================================================== */

body .crm-dashboard.crm-dashboard{
    --ease:cubic-bezier(.22,1,.36,1);
    position:relative;
    z-index:0;
    max-width:1480px;
    margin:0 auto;
    padding:4px 2px 28px;
    color:#e8eefc;
    font-family:Inter,"Segoe UI",sans-serif;
}

/* ---------- HERO ---------- */
body .crm-dashboard.crm-dashboard .hero-card{
    position:relative;
    overflow:hidden;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:28px;
    min-height:176px;
    padding:30px 38px;
    border:1px solid rgba(56,140,255,.55);
    border-radius:22px;
    color:#fff;
    background:
        radial-gradient(ellipse 45% 110% at 100% 100%, rgba(168,85,247,.35), transparent 60%),
        radial-gradient(ellipse 50% 90% at 70% 100%, rgba(37,99,235,.35), transparent 60%),
        linear-gradient(135deg,#061330 0%,#08183f 55%,#071436 100%);
    box-shadow:
        0 0 0 1px rgba(59,130,246,.15),
        0 0 38px rgba(37,99,235,.35),
        0 22px 50px rgba(2,8,23,.5),
        inset 0 0 40px rgba(37,99,235,.10);
    transition:box-shadow .6s var(--ease), border-color .4s ease;
}
body .crm-dashboard.crm-dashboard .hero-card:hover{
    box-shadow:
        0 0 0 1px rgba(59,130,246,.25),
        0 0 52px rgba(37,99,235,.5),
        0 28px 60px rgba(2,8,23,.55),
        inset 0 0 40px rgba(37,99,235,.12);
}
body .crm-dashboard.crm-dashboard .hero-card::before{
    content:"";
    position:absolute;
    right:0;
    bottom:0;
    width:62%;
    height:100%;
    pointer-events:none;
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 200' preserveAspectRatio='none'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' x2='1'%3E%3Cstop offset='0' stop-color='%2338bdf8' stop-opacity='0'/%3E%3Cstop offset='.35' stop-color='%2338bdf8' stop-opacity='.8'/%3E%3Cstop offset='.65' stop-color='%236366f1'/%3E%3Cstop offset='.85' stop-color='%23c084fc'/%3E%3Cstop offset='1' stop-color='%23fbbf24'/%3E%3C/linearGradient%3E%3Cfilter id='b' x='-5%25' y='-50%25' width='110%25' height='200%25'%3E%3CfeGaussianBlur stdDeviation='5'/%3E%3C/filter%3E%3C/defs%3E%3Cg fill='none' stroke='url(%23g)'%3E%3Cpath d='M0 175 C150 150 260 90 420 130 S650 190 800 110' stroke-width='6' filter='url(%23b)' opacity='.8'/%3E%3Cpath d='M0 175 C150 150 260 90 420 130 S650 190 800 110' stroke-width='2'/%3E%3Cpath d='M0 190 C170 165 280 120 440 150 S670 200 800 135' stroke-width='1.4' opacity='.7'/%3E%3Cpath d='M0 160 C140 135 250 70 410 112 S640 175 800 90' stroke-width='1' opacity='.5'/%3E%3C/g%3E%3C/svg%3E") right bottom / 100% 100% no-repeat;
}
body .crm-dashboard.crm-dashboard .hero-card > *{
    position:relative;
    z-index:1;
}

body .crm-dashboard.crm-dashboard .dashboard-eyebrow{
    display:inline-flex;
    margin:0 0 12px;
    padding:5px 12px;
    border:1px solid rgba(56,189,248,.55);
    border-radius:999px;
    background:rgba(37,99,235,.25);
    color:#bae6fd;
    font-size:11px;
    font-weight:700;
    letter-spacing:.16em;
    box-shadow:0 0 14px rgba(56,189,248,.25);
}
body .crm-dashboard.crm-dashboard .welcome-text{
    margin:0;
    color:rgba(226,232,240,.88);
    font-size:15px;
    font-weight:500;
}
body .crm-dashboard.crm-dashboard .hero-card h1{
    margin:8px 0 12px;
    font-size:clamp(30px,3.2vw,42px);
    font-weight:800;
    letter-spacing:-.04em;
    line-height:1.1;
    background:linear-gradient(90deg,#ffffff 0 21%,#38bdf8 25%,#3b82f6 68%,#60a5fa 100%);
    -webkit-background-clip:text;
    background-clip:text;
    color:transparent;
}
body .crm-dashboard.crm-dashboard .sub-text{
    max-width:560px;
    margin:0;
    color:#a9b8d0;
    font-size:14px;
    line-height:1.65;
}

body .crm-dashboard.crm-dashboard .hero-actions{
    display:flex;
    flex-wrap:wrap;
    justify-content:flex-end;
    gap:14px;
}
body .crm-dashboard.crm-dashboard .primary-btn,
body .crm-dashboard.crm-dashboard .secondary-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    min-height:42px;
    padding:0 20px;
    border-radius:12px;
    font-size:13px;
    font-weight:700;
    text-decoration:none;
    transition:transform .35s var(--ease), box-shadow .35s var(--ease), background-color .3s ease, border-color .3s ease;
}
body .crm-dashboard.crm-dashboard .primary-btn{
    background:linear-gradient(135deg,#0ea5e9,#2563eb);
    border:1px solid rgba(125,211,252,.75);
    color:#fff;
    box-shadow:0 0 22px rgba(56,189,248,.5), inset 0 1px 0 rgba(255,255,255,.3);
}
body .crm-dashboard.crm-dashboard .secondary-btn{
    background:rgba(8,20,50,.65);
    border:1px solid rgba(125,211,252,.38);
    color:#e0f2fe;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.08);
}
body .crm-dashboard.crm-dashboard .secondary-btn::before{
    content:"";
    width:18px;
    height:18px;
    flex:0 0 18px;
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23e0f2fe' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='9' cy='8' r='3.5'/%3E%3Cpath d='M2.5 20c.6-3.6 3.2-5.5 6.5-5.5s5.9 1.9 6.5 5.5'/%3E%3Cpath d='M19 8v6M16 11h6'/%3E%3C/svg%3E") center / contain no-repeat;
}
body .crm-dashboard.crm-dashboard .primary-btn:hover{
    transform:translateY(-3px);
    box-shadow:0 0 30px rgba(56,189,248,.65), inset 0 1px 0 rgba(255,255,255,.3);
}
body .crm-dashboard.crm-dashboard .secondary-btn:hover{
    transform:translateY(-3px);
    background:rgba(255,255,255,.10);
    border-color:rgba(125,211,252,.6);
}
body .crm-dashboard.crm-dashboard .primary-btn:active,
body .crm-dashboard.crm-dashboard .secondary-btn:active{
    transform:translateY(-1px) scale(.98);
}

/* ---------- KPI CARDS ---------- */
body .crm-dashboard.crm-dashboard .kpi-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:20px;
    margin:22px 0 26px;
}

body .crm-dashboard.crm-dashboard .kpi-card{
    position:relative;
    overflow:hidden;
    display:flex;
    align-items:center;
    gap:14px;
    min-height:142px;
    padding:20px 22px;
    border-radius:16px;
    color:#fff;
    cursor:default;
    will-change:transform;
    box-shadow:
        0 0 0 1px rgba(255,255,255,.04),
        0 0 26px -4px var(--glow, rgba(59,130,246,.5)),
        0 16px 36px rgba(2,8,23,.4),
        inset 0 1px 0 rgba(255,255,255,.28);
    transition:transform .5s var(--ease), box-shadow .5s var(--ease), border-color .35s ease;
}
body .crm-dashboard.crm-dashboard .kpi-card:hover{
    transform:translateY(-6px);
    box-shadow:
        0 28px 55px rgba(2,8,23,.55),
        0 0 40px -6px var(--glow, rgba(96,165,250,.6)),
        inset 0 1px 0 rgba(255,255,255,.3);
}

body .crm-dashboard.crm-dashboard .kpi-card > div:last-of-type{
    position:relative;
    z-index:1;
    min-width:0;
    flex:1;
}

/* colors */
body .crm-dashboard.crm-dashboard .kpi-leads{
    --glow:rgba(59,130,246,.65);
    --arrow:#2563eb;
    border:1px solid rgba(96,165,250,.85);
    background:linear-gradient(160deg,#2563eb 0%,#1d4ed8 35%,#0b2a8a 100%);
}
body .crm-dashboard.crm-dashboard .kpi-won{
    --glow:rgba(139,92,246,.65);
    --arrow:#7c3aed;
    border:1px solid rgba(167,139,250,.85);
    background:linear-gradient(160deg,#7c3aed 0%,#5b21b6 38%,#2e1065 100%);
}
body .crm-dashboard.crm-dashboard .kpi-customers{
    --glow:rgba(20,184,166,.60);
    --arrow:#0d9488;
    border:1px solid rgba(45,212,191,.85);
    background:linear-gradient(160deg,#14b8a6 0%,#0f766e 38%,#064e3b 100%);
}
body .crm-dashboard.crm-dashboard .kpi-followups{
    --glow:rgba(168,85,247,.65);
    --arrow:#9333ea;
    border:1px solid rgba(192,132,252,.85);
    background:linear-gradient(160deg,#9333ea 0%,#6b21a8 38%,#3b0764 100%);
}
body .crm-dashboard.crm-dashboard .kpi-activities{
    --glow:rgba(245,158,11,.60);
    --arrow:#d97706;
    border:1px solid rgba(251,191,36,.85);
    background:linear-gradient(160deg,#f59e0b 0%,#b45309 40%,#78350f 100%);
}

/* bottom neon wave */
body .crm-dashboard.crm-dashboard .kpi-card::before{
    content:"";
    position:absolute;
    left:0;
    right:0;
    bottom:0;
    height:64px;
    pointer-events:none;
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 70' preserveAspectRatio='none'%3E%3Cpath d='M0 48 C50 20 100 68 160 44 S250 16 300 36 V70 H0 Z' fill='white' fill-opacity='.10'/%3E%3Cpath d='M0 48 C50 20 100 68 160 44 S250 16 300 36' fill='none' stroke='white' stroke-opacity='.95' stroke-width='2'/%3E%3Cpath d='M0 56 C60 34 110 70 170 52 S260 30 300 46' fill='none' stroke='white' stroke-opacity='.45' stroke-width='1.2'/%3E%3C/svg%3E") 0% bottom / 140% 100% no-repeat;
    filter:drop-shadow(0 0 6px var(--glow, rgba(96,165,250,.8)));
    transition:background-position .9s var(--ease);
}
body .crm-dashboard.crm-dashboard .kpi-card:hover::before{
    background-position:100% bottom;
}

/* arrow circle */
body .crm-dashboard.crm-dashboard .kpi-card::after{
    content:"";
    position:relative;
    z-index:1;
    flex:0 0 34px;
    width:34px;
    height:34px;
    margin-left:auto;
    margin-top:12px;
    border:1px solid rgba(255,255,255,.4);
    border-radius:50%;
    background:var(--arrow, rgba(255,255,255,.18)) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12h14'/%3E%3Cpath d='M13 6l6 6-6 6'/%3E%3C/svg%3E") center / 12px no-repeat;
    box-shadow:0 0 14px -2px var(--glow, rgba(96,165,250,.6));
    transition:transform .45s var(--ease);
}
body .crm-dashboard.crm-dashboard .kpi-card:hover::after{
    transform:translateX(4px);
}

/* icon */
body .crm-dashboard.crm-dashboard .kpi-icon{
    position:relative;
    z-index:1;
    display:grid;
    flex:0 0 48px;
    width:48px;
    height:48px;
    place-items:center;
    font-size:38px;
    background:none;
    box-shadow:none;
    filter:drop-shadow(0 0 10px var(--glow, rgba(96,165,250,.7)));
    transition:transform .5s var(--ease);
}
body .crm-dashboard.crm-dashboard .kpi-card:hover .kpi-icon{
    transform:translateY(-3px) scale(1.12);
}
body .crm-dashboard.crm-dashboard .kpi-leads .kpi-icon{ color:#93c5fd; }
body .crm-dashboard.crm-dashboard .kpi-won .kpi-icon{ color:#ddd6fe; }
body .crm-dashboard.crm-dashboard .kpi-customers .kpi-icon{ color:#99f6e4; }
body .crm-dashboard.crm-dashboard .kpi-followups .kpi-icon{ color:#e9d5ff; }
body .crm-dashboard.crm-dashboard .kpi-activities .kpi-icon{ color:#fde68a; }

body .crm-dashboard.crm-dashboard .kpi-card span{
    display:block;
    color:#fff;
    font-size:13px;
    font-weight:600;
    line-height:1.3;
}
body .crm-dashboard.crm-dashboard .kpi-card h2{
    margin:6px 0 8px;
    color:#fff;
    font-size:36px;
    font-weight:800;
    letter-spacing:-.04em;
    line-height:1;
}
body .crm-dashboard.crm-dashboard .kpi-card small{
    color:rgba(255,255,255,.85);
    font-size:11.5px;
}

/* ---------- PANELS ---------- */
body .crm-dashboard.crm-dashboard .dashboard-grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:22px;
}
body .crm-dashboard.crm-dashboard .glass-panel{
    margin-bottom:22px;
    padding:24px;
    border:1px solid rgba(148,163,184,.16);
    border-radius:22px;
    background:linear-gradient(180deg,rgba(15,27,53,.92),rgba(8,16,36,.94));
    box-shadow:0 18px 40px rgba(2,8,23,.28), inset 0 1px 0 rgba(255,255,255,.05);
    transition:transform .5s var(--ease), box-shadow .5s var(--ease), border-color .35s ease;
}
body .crm-dashboard.crm-dashboard .glass-panel:hover{
    transform:translateY(-4px);
    border-color:rgba(96,165,250,.28);
    box-shadow:0 26px 55px rgba(2,8,23,.5), 0 0 30px -10px rgba(96,165,250,.35);
}

body .crm-dashboard.crm-dashboard .panel-title{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    margin-bottom:18px;
}
body .crm-dashboard.crm-dashboard .panel-title h3{
    margin:0;
    color:#f8fafc;
    font-size:18px;
    font-weight:750;
}
body .crm-dashboard.crm-dashboard .panel-title p,
body .crm-dashboard.crm-dashboard .panel-title > span{
    margin:4px 0 0;
    color:#8ea0bb;
    font-size:13px;
}
body .crm-dashboard.crm-dashboard .panel-title a{
    color:#93c5fd;
    font-size:13px;
    font-weight:650;
    text-decoration:none;
    transition:color .25s ease;
}
body .crm-dashboard.crm-dashboard .panel-title a:hover{ color:#fff; }

/* pipeline */
body .crm-dashboard.crm-dashboard .pipeline{
    display:flex;
    justify-content:space-between;
    gap:10px;
    text-align:center;
}
body .crm-dashboard.crm-dashboard .pipeline-stage{
    flex:1;
    min-width:0;
    padding:14px 8px;
    border:1px solid rgba(148,163,184,.14);
    border-radius:16px;
    background:rgba(255,255,255,.04);
    transition:transform .4s var(--ease), background-color .3s ease;
}
body .crm-dashboard.crm-dashboard .pipeline-stage:hover{
    transform:translateY(-4px);
    background:rgba(255,255,255,.08);
}
body .crm-dashboard.crm-dashboard .pipeline-stage span{
    color:#94a3b8;
    font-size:12px;
}
body .crm-dashboard.crm-dashboard .pipeline-stage strong{
    display:block;
    margin-top:8px;
    color:#fff;
    font-size:28px;
}
body .crm-dashboard.crm-dashboard .pipeline-stage-new{ border-color:rgba(96,165,250,.30); }
body .crm-dashboard.crm-dashboard .pipeline-stage-proposal{ border-color:rgba(167,139,250,.30); }
body .crm-dashboard.crm-dashboard .pipeline-stage-won{ border-color:rgba(192,132,252,.32); }
body .crm-dashboard.crm-dashboard .pipeline-stage-customers{ border-color:rgba(45,212,191,.30); }
body .crm-dashboard.crm-dashboard .pipeline-stage-followups{ border-color:rgba(251,191,36,.30); }

body .crm-dashboard.crm-dashboard .dashboard-analytics{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:12px;
    margin-top:18px;
}
body .crm-dashboard.crm-dashboard .dashboard-analytics > div,
body .crm-dashboard.crm-dashboard .dashboard-growth-summary{
    padding:14px 16px;
    border:1px solid rgba(148,163,184,.12);
    border-radius:16px;
    background:rgba(2,8,23,.30);
}
body .crm-dashboard.crm-dashboard .dashboard-analytics > div{
    transition:transform .4s var(--ease), border-color .3s ease;
}
body .crm-dashboard.crm-dashboard .dashboard-analytics > div:hover{
    transform:translateY(-3px);
    border-color:rgba(96,165,250,.3);
}
body .crm-dashboard.crm-dashboard .dashboard-analytics span,
body .crm-dashboard.crm-dashboard .dashboard-growth-summary > span:first-child{
    display:block;
    color:#8ea0bb;
    font-size:12px;
}
body .crm-dashboard.crm-dashboard .dashboard-analytics strong{
    display:block;
    margin-top:6px;
    color:#fff;
    font-size:22px;
}
body .crm-dashboard.crm-dashboard .dashboard-growth-summary{
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:10px 16px;
    margin-top:12px;
}
body .crm-dashboard.crm-dashboard .dashboard-growth-summary span{
    color:#c7d2e5;
    font-size:13px;
}

/* quick actions */
body .crm-dashboard.crm-dashboard .quick-links a{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:12px;
    padding:15px 16px;
    border:1px solid rgba(148,163,184,.14);
    border-radius:16px;
    background:rgba(255,255,255,.04);
    color:#e2e8f0;
    text-decoration:none;
    transition:transform .4s var(--ease), background-color .3s ease, border-color .3s ease;
}
body .crm-dashboard.crm-dashboard .quick-links a:hover{
    transform:translateX(6px);
    background:rgba(96,165,250,.10);
    border-color:rgba(96,165,250,.35);
}
body .crm-dashboard.crm-dashboard .quick-links a i{
    color:#93c5fd;
    font-size:18px;
    transition:transform .4s var(--ease);
}
body .crm-dashboard.crm-dashboard .quick-links a:hover i{
    transform:scale(1.2) rotate(-6deg);
}

/* tables */
body .crm-dashboard.crm-dashboard .dashboard-table-wrap{ overflow-x:auto; }
body .crm-dashboard.crm-dashboard table{
    width:100%;
    border-collapse:collapse;
}
body .crm-dashboard.crm-dashboard th,
body .crm-dashboard.crm-dashboard td{
    padding:14px 12px;
    border-bottom:1px solid rgba(148,163,184,.12);
    text-align:left;
    color:#dbe4f3;
}
body .crm-dashboard.crm-dashboard th{
    color:#93a4bd;
    font-size:12px;
    font-weight:700;
    letter-spacing:.04em;
    text-transform:uppercase;
}
body .crm-dashboard.crm-dashboard tbody tr{
    background:transparent;
    transition:background-color .3s ease;
}
body .crm-dashboard.crm-dashboard tbody tr:hover{
    background:rgba(96,165,250,.08);
}

body .crm-dashboard.crm-dashboard .status,
body .crm-dashboard.crm-dashboard .dashboard-status{
    display:inline-flex;
    padding:5px 12px;
    border-radius:999px;
    background:rgba(37,99,235,.18);
    color:#93c5fd;
    font-size:12px;
    font-weight:650;
}
body .crm-dashboard.crm-dashboard .status.won,
body .crm-dashboard.crm-dashboard .dashboard-status.completed{
    background:rgba(16,185,129,.16);
    color:#6ee7b7;
}
body .crm-dashboard.crm-dashboard .status.new,
body .crm-dashboard.crm-dashboard .dashboard-status.pending{
    background:rgba(59,130,246,.16);
    color:#93c5fd;
}
body .crm-dashboard.crm-dashboard .status.proposal{
    background:rgba(168,85,247,.16);
    color:#d8b4fe;
}
body .crm-dashboard.crm-dashboard .dashboard-record-link{
    color:#bfdbfe;
    text-decoration:none;
}
body .crm-dashboard.crm-dashboard .dashboard-empty{ color:#8ea0bb; }

/* upcoming follow ups */
body .crm-dashboard.crm-dashboard .dashboard-upcoming-list{
    display:flex;
    flex-direction:column;
    gap:10px;
}
body .crm-dashboard.crm-dashboard .dashboard-followup-item{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 14px;
    border:1px solid rgba(148,163,184,.12);
    border-radius:16px;
    background:rgba(255,255,255,.04);
    color:#e2e8f0;
    text-decoration:none;
    transition:transform .4s var(--ease), background-color .3s ease, border-color .3s ease;
}
body .crm-dashboard.crm-dashboard .dashboard-followup-item:hover{
    transform:translateX(6px);
    background:rgba(96,165,250,.10);
    border-color:rgba(96,165,250,.35);
}
body .crm-dashboard.crm-dashboard .dashboard-followup-date{
    min-width:48px;
    color:#93c5fd;
    font-weight:750;
}
body .crm-dashboard.crm-dashboard .dashboard-followup-copy{
    display:flex;
    flex:1;
    min-width:0;
    flex-direction:column;
}
body .crm-dashboard.crm-dashboard .dashboard-followup-copy small{ color:#8ea0bb; }

/* ---------- PAGE LOAD ---------- */
@keyframes crmRise{
    from{ opacity:0; transform:translateY(20px); }
    to{ opacity:1; transform:translateY(0); }
}
body .crm-dashboard.crm-dashboard .hero-card,
body .crm-dashboard.crm-dashboard .kpi-card,
body .crm-dashboard.crm-dashboard .glass-panel{
    animation:crmRise .7s var(--ease) backwards;
}
body .crm-dashboard.crm-dashboard .kpi-card:nth-child(1){ animation-delay:.08s; }
body .crm-dashboard.crm-dashboard .kpi-card:nth-child(2){ animation-delay:.14s; }
body .crm-dashboard.crm-dashboard .kpi-card:nth-child(3){ animation-delay:.20s; }
body .crm-dashboard.crm-dashboard .kpi-card:nth-child(4){ animation-delay:.26s; }
body .crm-dashboard.crm-dashboard .kpi-card:nth-child(5){ animation-delay:.32s; }

/* ---------- RESPONSIVE ---------- */
@media (max-width:1100px){
    body .crm-dashboard.crm-dashboard .kpi-grid,
    body .crm-dashboard.crm-dashboard .dashboard-analytics{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
    body .crm-dashboard.crm-dashboard .hero-card{
        flex-direction:column;
        align-items:flex-start;
    }
}
@media (max-width:900px){
    body .crm-dashboard.crm-dashboard .dashboard-grid,
    body .crm-dashboard.crm-dashboard .dashboard-records-grid{
        grid-template-columns:minmax(0,1fr);
    }
    body .crm-dashboard.crm-dashboard .pipeline{ flex-wrap:wrap; }
}
@media (max-width:640px){
    body .crm-dashboard.crm-dashboard .kpi-grid,
    body .crm-dashboard.crm-dashboard .dashboard-analytics{
        grid-template-columns:minmax(0,1fr);
    }
}

@media (prefers-reduced-motion:reduce){
    body .crm-dashboard.crm-dashboard *,
    body .crm-dashboard.crm-dashboard *::before,
    body .crm-dashboard.crm-dashboard *::after{
        animation:none !important;
        transition-duration:.01ms !important;
    }
}

/* =====================================================
   V2 REFINEMENTS (top section + lighter panels)
===================================================== */

/* ---------- HERO ---------- */
body .crm-dashboard.crm-dashboard .hero-card{
    min-height:192px;
    padding:34px 42px;
}
body .crm-dashboard.crm-dashboard .hero-card::after{
    content:"";
    position:absolute;
    inset:0;
    pointer-events:none;
    background-image:radial-gradient(rgba(147,197,253,.18) 1px, transparent 1.3px);
    background-size:22px 22px;
    -webkit-mask-image:linear-gradient(90deg,#000 0%,transparent 55%);
    mask-image:linear-gradient(90deg,#000 0%,transparent 55%);
}
body .crm-dashboard.crm-dashboard .dashboard-eyebrow{
    align-items:center;
}
body .crm-dashboard.crm-dashboard .dashboard-eyebrow::before{
    content:"";
    width:7px;
    height:7px;
    margin-right:8px;
    border-radius:50%;
    background:#38bdf8;
    box-shadow:0 0 10px #38bdf8;
}
body .crm-dashboard.crm-dashboard .hero-card h1{
    font-size:clamp(32px,3.6vw,48px);
}
body .crm-dashboard.crm-dashboard .sub-text{
    color:#bccbe3;
}

/* ---------- KPI ---------- */
body .crm-dashboard.crm-dashboard .kpi-grid{
    margin:26px 0 28px;
}
body .crm-dashboard.crm-dashboard .kpi-card{
    min-height:150px;
    padding:22px 24px;
    border-radius:18px;
}
body .crm-dashboard.crm-dashboard .kpi-card h2{
    font-size:40px;
    text-shadow:0 2px 14px rgba(0,0,0,.25);
}
body .crm-dashboard.crm-dashboard .kpi-card span{
    font-size:13.5px;
}

/* ---------- LIGHTER PANELS ---------- */
body .crm-dashboard.crm-dashboard .glass-panel{
    background:
        radial-gradient(ellipse 60% 80% at 100% 0%, rgba(99,102,241,.22), transparent 60%),
        linear-gradient(160deg, rgba(40,70,134,.80), rgba(18,38,88,.88));
    border:1px solid rgba(147,197,253,.28);
    backdrop-filter:blur(16px);
    -webkit-backdrop-filter:blur(16px);
    box-shadow:0 18px 40px rgba(2,8,23,.35), inset 0 1px 0 rgba(255,255,255,.14);
}
body .crm-dashboard.crm-dashboard .glass-panel:hover{
    border-color:rgba(147,197,253,.50);
}

body .crm-dashboard.crm-dashboard .pipeline-stage,
body .crm-dashboard.crm-dashboard .dashboard-analytics > div,
body .crm-dashboard.crm-dashboard .dashboard-growth-summary,
body .crm-dashboard.crm-dashboard .quick-links a,
body .crm-dashboard.crm-dashboard .dashboard-followup-item{
    background:rgba(255,255,255,.08);
    border-color:rgba(191,219,254,.20);
}
body .crm-dashboard.crm-dashboard .pipeline-stage-new{ border-color:rgba(96,165,250,.55); }
body .crm-dashboard.crm-dashboard .pipeline-stage-proposal{ border-color:rgba(167,139,250,.55); }
body .crm-dashboard.crm-dashboard .pipeline-stage-won{ border-color:rgba(192,132,252,.55); }
body .crm-dashboard.crm-dashboard .pipeline-stage-customers{ border-color:rgba(45,212,191,.55); }
body .crm-dashboard.crm-dashboard .pipeline-stage-followups{ border-color:rgba(251,191,36,.55); }

body .crm-dashboard.crm-dashboard .pipeline,
body .crm-dashboard.crm-dashboard .dashboard-analytics{
    border:0;
}

/* text contrast */
body .crm-dashboard.crm-dashboard .panel-title h3{ color:#fff; }
body .crm-dashboard.crm-dashboard .panel-title p{ color:#b7c7e6; }
body .crm-dashboard.crm-dashboard .panel-title a{ color:#bfdbfe; }
body .crm-dashboard.crm-dashboard .panel-title > span{
    display:inline-flex;
    margin:0;
    padding:4px 12px;
    border-radius:999px;
    background:rgba(96,165,250,.20);
    border:1px solid rgba(147,197,253,.38);
    color:#dbeafe;
    font-size:12px;
    font-weight:650;
}
body .crm-dashboard.crm-dashboard .pipeline-stage span,
body .crm-dashboard.crm-dashboard .dashboard-analytics span,
body .crm-dashboard.crm-dashboard .dashboard-growth-summary > span:first-child{
    color:#b7c7e6;
}
body .crm-dashboard.crm-dashboard .pipeline-stage strong,
body .crm-dashboard.crm-dashboard .dashboard-analytics strong,
body .crm-dashboard.crm-dashboard .dashboard-growth-summary strong{
    color:#fff;
}
body .crm-dashboard.crm-dashboard .dashboard-growth-summary span{ color:#dbe6fb; }

body .crm-dashboard.crm-dashboard .quick-links a{ color:#f1f5ff; }
body .crm-dashboard.crm-dashboard .quick-links a i{ color:#bfdbfe; }
body .crm-dashboard.crm-dashboard .dashboard-followup-date{ color:#bfdbfe; }
body .crm-dashboard.crm-dashboard .dashboard-followup-copy small,
body .crm-dashboard.crm-dashboard .dashboard-empty{ color:#b7c7e6; }

/* tables */
body .crm-dashboard.crm-dashboard thead,
body .crm-dashboard.crm-dashboard thead tr{
    background:transparent;
}
body .crm-dashboard.crm-dashboard th{
    color:#c3d1ec;
    background:transparent;
    border-bottom:1px solid rgba(191,219,254,.28);
}
body .crm-dashboard.crm-dashboard td{
    color:#eaf0fc;
    border-bottom:1px solid rgba(191,219,254,.14);
}
body .crm-dashboard.crm-dashboard .dashboard-record-link{ color:#dbeafe; }

/* status badges */
body .crm-dashboard.crm-dashboard .status.lost{
    background:rgba(239,68,68,.20);
    color:#fecaca;
}
body .crm-dashboard.crm-dashboard .status.qualified{
    background:rgba(250,204,21,.20);
    color:#fde68a;
}
body .crm-dashboard.crm-dashboard .status.contacted{
    background:rgba(34,211,238,.18);
    color:#a5f3fc;
}

/* =====================================================
   V3 ULTRA PREMIUM
===================================================== */

body .crm-dashboard.crm-dashboard{
    -webkit-font-smoothing:antialiased;
    font-feature-settings:"tnum" 1;
}
body .crm-dashboard.crm-dashboard ::selection{
    background:rgba(96,165,250,.45);
    color:#fff;
}

/* soft aurora behind the top of the page */
body .crm-dashboard.crm-dashboard::before{
    content:"";
    position:absolute;
    top:-40px;
    left:0;
    right:0;
    height:560px;
    z-index:-1;
    pointer-events:none;
    background:
        radial-gradient(ellipse 40% 60% at 14% 12%, rgba(37,99,235,.24), transparent 70%),
        radial-gradient(ellipse 36% 55% at 86% 4%, rgba(124,58,237,.22), transparent 70%);
    filter:blur(30px);
}

/* ---------- HERO ---------- */
body .crm-dashboard.crm-dashboard .hero-card{
    border:1px solid rgba(96,165,250,.45);
    border-radius:26px;
    background:
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 1 0 0 0 0 1 0 0 0 0 1 .5 0 0 0 -.18'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"),
        radial-gradient(ellipse 45% 110% at 100% 100%, rgba(168,85,247,.38), transparent 60%),
        radial-gradient(ellipse 50% 90% at 70% 100%, rgba(37,99,235,.36), transparent 60%),
        radial-gradient(ellipse 40% 80% at 0% 0%, rgba(56,189,248,.10), transparent 60%),
        linear-gradient(135deg,#061330 0%,#08183f 55%,#071436 100%);
    box-shadow:
        0 0 0 1px rgba(59,130,246,.12),
        0 40px 80px -24px rgba(2,8,23,.85),
        0 0 70px -12px rgba(37,99,235,.45),
        inset 0 1px 0 rgba(255,255,255,.18),
        inset 0 -30px 60px -30px rgba(124,58,237,.28);
}
body .crm-dashboard.crm-dashboard .hero-card::before{
    transform-origin:left center;
    animation:crmDrift 9s ease-in-out infinite alternate;
}
@keyframes crmDrift{
    from{ transform:scale(1); }
    to{ transform:scale(1.05); }
}

/* one shine sweep on the title at load */
body .crm-dashboard.crm-dashboard .hero-card h1{
    background:linear-gradient(100deg,
        #ffffff 0 9%,
        #7dd3fc 11%,
        #3b82f6 28%,
        #a78bfa 45%,
        #7dd3fc 60%,
        #ffffff 75%,
        #7dd3fc 100%);
    background-size:220% 100%;
    background-position:0% 0;
    -webkit-background-clip:text;
    background-clip:text;
    color:transparent;
    animation:crmShine 2.4s var(--ease) .5s 1 both;
}
@keyframes crmShine{
    from{ background-position:100% 0; }
    to{ background-position:0% 0; }
}

/* primary button sheen */
body .crm-dashboard.crm-dashboard .primary-btn{
    position:relative;
    overflow:hidden;
}
body .crm-dashboard.crm-dashboard .primary-btn::before{
    content:"";
    position:absolute;
    inset:0;
    background:linear-gradient(110deg, transparent 30%, rgba(255,255,255,.38) 50%, transparent 70%);
    transform:translateX(-120%);
    transition:transform .8s var(--ease);
}
body .crm-dashboard.crm-dashboard .primary-btn:hover::before{
    transform:translateX(120%);
}

/* ---------- KPI CARDS ---------- */
body .crm-dashboard.crm-dashboard .kpi-card{
    box-shadow:
        0 0 0 1px rgba(255,255,255,.05),
        0 30px 50px -22px rgba(2,8,23,.9),
        0 0 34px -6px var(--glow, rgba(59,130,246,.5)),
        inset 0 1px 0 rgba(255,255,255,.35),
        inset 0 -24px 40px -24px rgba(0,0,0,.35);
}
body .crm-dashboard.crm-dashboard .kpi-card h2{
    font-variant-numeric:tabular-nums;
}

/* ---------- PANELS: gradient hairline border ---------- */
body .crm-dashboard.crm-dashboard .glass-panel{
    border:1px solid transparent;
    background:
        radial-gradient(ellipse 55% 70% at 100% 0%, rgba(99,102,241,.22), transparent 62%) padding-box,
        linear-gradient(160deg,#223e78,#122858) padding-box,
        linear-gradient(140deg, rgba(191,219,254,.55), rgba(147,197,253,.10) 38%, rgba(167,139,250,.14) 62%, rgba(147,197,253,.45)) border-box;
    box-shadow:
        0 30px 60px -28px rgba(2,8,23,.85),
        0 0 0 1px rgba(2,8,23,.35),
        inset 0 1px 0 rgba(255,255,255,.12);
}

/* pipeline stages: colored top accent */
body .crm-dashboard.crm-dashboard .pipeline-stage{
    --c:96,165,250;
    box-shadow:
        inset 0 2px 0 rgba(var(--c),.9),
        0 12px 26px -14px rgba(var(--c),.55);
}
body .crm-dashboard.crm-dashboard .pipeline-stage-new{ --c:96,165,250; }
body .crm-dashboard.crm-dashboard .pipeline-stage-proposal{ --c:167,139,250; }
body .crm-dashboard.crm-dashboard .pipeline-stage-won{ --c:192,132,252; }
body .crm-dashboard.crm-dashboard .pipeline-stage-customers{ --c:45,212,191; }
body .crm-dashboard.crm-dashboard .pipeline-stage-followups{ --c:251,191,36; }

body .crm-dashboard.crm-dashboard .pipeline-stage strong,
body .crm-dashboard.crm-dashboard .dashboard-analytics strong{
    background:linear-gradient(180deg,#ffffff 25%,#bfdbfe 100%);
    -webkit-background-clip:text;
    background-clip:text;
    color:transparent;
    font-variant-numeric:tabular-nums;
}

/* quick actions: icon chip + chevron */
body .crm-dashboard.crm-dashboard .quick-links a{
    padding:13px 14px;
}
body .crm-dashboard.crm-dashboard .quick-links a i{
    display:grid;
    place-items:center;
    flex:0 0 38px;
    width:38px;
    height:38px;
    border-radius:12px;
    background:rgba(96,165,250,.16);
    border:1px solid rgba(147,197,253,.30);
    font-size:17px;
    color:#dbeafe;
    transition:background .35s ease, box-shadow .35s ease, border-color .35s ease, transform .4s var(--ease);
}
body .crm-dashboard.crm-dashboard .quick-links a::after{
    content:"";
    flex:0 0 16px;
    width:16px;
    height:16px;
    margin-left:auto;
    opacity:.6;
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23dbeafe' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M9 6l6 6-6 6'/%3E%3C/svg%3E") center / contain no-repeat;
    transition:transform .4s var(--ease), opacity .3s ease;
}
body .crm-dashboard.crm-dashboard .quick-links a:hover::after{
    transform:translateX(4px);
    opacity:1;
}
body .crm-dashboard.crm-dashboard .quick-links a:hover i{
    background:linear-gradient(135deg,#2563eb,#7c3aed);
    border-color:transparent;
    box-shadow:0 8px 20px rgba(37,99,235,.4);
    transform:scale(1.06);
}

/* tables */
body .crm-dashboard.crm-dashboard th{
    font-size:11.5px;
    letter-spacing:.08em;
}
body .crm-dashboard.crm-dashboard td{
    padding:16px 14px;
}
body .crm-dashboard.crm-dashboard tbody tr:hover td:first-child{
    box-shadow:inset 3px 0 0 #60a5fa;
}
body .crm-dashboard.crm-dashboard .dashboard-table-wrap{
    scrollbar-width:thin;
    scrollbar-color:rgba(147,197,253,.35) transparent;
}

/* keyboard focus */
body .crm-dashboard.crm-dashboard a:focus-visible,
body .crm-dashboard.crm-dashboard button:focus-visible{
    outline:2px solid #7dd3fc;
    outline-offset:3px;
    border-radius:12px;
}

/* =====================================================
   V4 ELITE
===================================================== */

@property --crm-angle{
    syntax:"<angle>";
    inherits:false;
    initial-value:0deg;
}
@keyframes crmOrbit{
    to{ --crm-angle:360deg; }
}

/* ---------- display typography ---------- */
body .crm-dashboard.crm-dashboard .hero-card h1,
body .crm-dashboard.crm-dashboard .kpi-card h2,
body .crm-dashboard.crm-dashboard .panel-title h3,
body .crm-dashboard.crm-dashboard .pipeline-stage strong,
body .crm-dashboard.crm-dashboard .dashboard-analytics strong{
    font-family:"Manrope",Inter,"Segoe UI",sans-serif;
}
body .crm-dashboard.crm-dashboard .kpi-card span{
    letter-spacing:.01em;
}

/* ---------- HERO: light travelling along the border ---------- */
body .crm-dashboard.crm-dashboard .hero-card{
    border:1px solid transparent;
    background:
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 1 0 0 0 0 1 0 0 0 0 1 .5 0 0 0 -.18'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E") padding-box,
        radial-gradient(ellipse 45% 110% at 100% 100%, rgba(168,85,247,.38), transparent 60%) padding-box,
        radial-gradient(ellipse 50% 90% at 70% 100%, rgba(37,99,235,.36), transparent 60%) padding-box,
        radial-gradient(ellipse 40% 80% at 0% 0%, rgba(56,189,248,.10), transparent 60%) padding-box,
        linear-gradient(135deg,#061330 0%,#08183f 55%,#071436 100%) padding-box,
        conic-gradient(from var(--crm-angle),
            rgba(96,165,250,.18) 0deg,
            rgba(56,189,248,.95) 35deg,
            rgba(168,85,247,.95) 75deg,
            rgba(96,165,250,.18) 120deg,
            rgba(96,165,250,.18) 360deg) border-box;
    animation:
        crmRise .7s var(--ease) backwards,
        crmOrbit 9s linear infinite;
}

/* ---------- KPI: glass reflection ---------- */
body .crm-dashboard.crm-dashboard .kpi-leads{
    background:
        linear-gradient(115deg, rgba(255,255,255,.20) 0%, rgba(255,255,255,.04) 34%, transparent 52%),
        linear-gradient(160deg,#2563eb 0%,#1d4ed8 35%,#0b2a8a 100%);
}
body .crm-dashboard.crm-dashboard .kpi-won{
    background:
        linear-gradient(115deg, rgba(255,255,255,.20) 0%, rgba(255,255,255,.04) 34%, transparent 52%),
        linear-gradient(160deg,#7c3aed 0%,#5b21b6 38%,#2e1065 100%);
}
body .crm-dashboard.crm-dashboard .kpi-customers{
    background:
        linear-gradient(115deg, rgba(255,255,255,.20) 0%, rgba(255,255,255,.04) 34%, transparent 52%),
        linear-gradient(160deg,#14b8a6 0%,#0f766e 38%,#064e3b 100%);
}
body .crm-dashboard.crm-dashboard .kpi-followups{
    background:
        linear-gradient(115deg, rgba(255,255,255,.20) 0%, rgba(255,255,255,.04) 34%, transparent 52%),
        linear-gradient(160deg,#9333ea 0%,#6b21a8 38%,#3b0764 100%);
}
body .crm-dashboard.crm-dashboard .kpi-activities{
    background:
        linear-gradient(115deg, rgba(255,255,255,.20) 0%, rgba(255,255,255,.04) 34%, transparent 52%),
        linear-gradient(160deg,#f59e0b 0%,#b45309 40%,#78350f 100%);
}

/* ---------- panel headers ---------- */
body .crm-dashboard.crm-dashboard .panel-title{
    padding-bottom:16px;
    border-bottom:1px solid transparent;
    border-image:linear-gradient(90deg, rgba(191,219,254,.38), rgba(191,219,254,0) 85%) 1;
}

/* ---------- pipeline flow connectors ---------- */
body .crm-dashboard.crm-dashboard .pipeline{
    gap:16px;
}
body .crm-dashboard.crm-dashboard .pipeline-stage{
    position:relative;
}
body .crm-dashboard.crm-dashboard .pipeline-stage:not(:last-child)::after{
    content:"";
    position:absolute;
    top:50%;
    right:-14px;
    width:12px;
    height:12px;
    transform:translateY(-50%);
    opacity:.6;
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23bfdbfe' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M9 5l7 7-7 7'/%3E%3C/svg%3E") center / contain no-repeat;
}

/* ---------- status badges with glowing dot ---------- */
body .crm-dashboard.crm-dashboard .status,
body .crm-dashboard.crm-dashboard .dashboard-status{
    align-items:center;
    gap:7px;
}
body .crm-dashboard.crm-dashboard .status::before,
body .crm-dashboard.crm-dashboard .dashboard-status::before{
    content:"";
    width:6px;
    height:6px;
    border-radius:50%;
    background:currentColor;
    box-shadow:0 0 8px currentColor;
}

@media (max-width:900px){
    body .crm-dashboard.crm-dashboard .pipeline-stage:not(:last-child)::after{
        display:none;
    }
}


/* =====================================================
   V5 - HERO + KPI MATCH THE REFERENCE
   (lower sections untouched)
===================================================== */

/* ---------- HERO ---------- */
body .crm-dashboard.crm-dashboard .hero-card::after{
    content:none;
    display:none;
}
body .crm-dashboard.crm-dashboard .hero-card{
    border:1px solid transparent;
    background:
        radial-gradient(ellipse 55% 120% at 92% 112%, rgba(139,92,246,.55), transparent 62%) padding-box,
        radial-gradient(ellipse 60% 100% at 66% 108%, rgba(37,99,235,.55), transparent 62%) padding-box,
        radial-gradient(ellipse 45% 70% at 100% 0%, rgba(59,130,246,.22), transparent 60%) padding-box,
        linear-gradient(120deg,#050f2a 0%,#071a45 50%,#0a1f5c 100%) padding-box,
        conic-gradient(from var(--crm-angle),
            rgba(59,130,246,.55) 0deg,
            rgba(56,189,248,1) 35deg,
            rgba(168,85,247,1) 75deg,
            rgba(59,130,246,.55) 120deg,
            rgba(59,130,246,.55) 360deg) border-box;
    box-shadow:
        0 0 0 1px rgba(59,130,246,.15),
        0 40px 80px -24px rgba(2,8,23,.85),
        0 0 60px -10px rgba(37,99,235,.5),
        inset 0 1px 0 rgba(255,255,255,.14);
}
body .crm-dashboard.crm-dashboard .hero-card::before{
    content:"";
    position:absolute;
    top:0;
    left:auto;
    right:0;
    bottom:0;
    width:72%;
    height:100%;
    opacity:1;
    pointer-events:none;
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 200' preserveAspectRatio='none'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' x2='1'%3E%3Cstop offset='0' stop-color='%2338bdf8' stop-opacity='0'/%3E%3Cstop offset='.28' stop-color='%2338bdf8'/%3E%3Cstop offset='.55' stop-color='%233b82f6'/%3E%3Cstop offset='.8' stop-color='%23a855f7'/%3E%3Cstop offset='1' stop-color='%23fbbf24'/%3E%3C/linearGradient%3E%3ClinearGradient id='a' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0' stop-color='%236366f1' stop-opacity='.28'/%3E%3Cstop offset='1' stop-color='%236366f1' stop-opacity='0'/%3E%3C/linearGradient%3E%3Cfilter id='b' x='-5%25' y='-80%25' width='110%25' height='260%25'%3E%3CfeGaussianBlur stdDeviation='7'/%3E%3C/filter%3E%3C/defs%3E%3Cpath d='M0 150 C140 120 250 40 400 90 S640 190 800 70 V200 H0 Z' fill='url(%23a)'/%3E%3Cg fill='none' stroke='url(%23g)'%3E%3Cpath d='M0 150 C140 120 250 40 400 90 S640 190 800 70' stroke-width='10' filter='url(%23b)' opacity='.85'/%3E%3Cpath d='M0 150 C140 120 250 40 400 90 S640 190 800 70' stroke-width='3'/%3E%3Cpath d='M0 165 C150 138 262 62 410 110 S650 200 800 96' stroke-width='1.6' opacity='.7'/%3E%3Cpath d='M0 138 C130 104 240 24 392 72 S630 176 800 48' stroke-width='1.2' opacity='.5'/%3E%3Cpath d='M0 178 C160 152 270 84 420 128 S660 205 800 118' stroke-width='1' opacity='.4'/%3E%3C/g%3E%3C/svg%3E") right bottom / 100% 100% no-repeat;
}
body .crm-dashboard.crm-dashboard .primary-btn{
    min-height:44px;
    padding:0 26px;
    font-size:14px;
    font-weight:600;
}
body .crm-dashboard.crm-dashboard .secondary-btn{
    min-height:44px;
    font-size:14px;
    font-weight:600;
}

/* ---------- KPI CARDS ---------- */
body .crm-dashboard.crm-dashboard .kpi-card::before,
body .crm-dashboard.crm-dashboard .kpi-card::after{
    content:none !important;
    display:none !important;
}
body .crm-dashboard.crm-dashboard .kpi-card{
    padding:22px 84px 22px 24px;
    background-repeat:no-repeat;
    transition:
        transform .5s var(--ease),
        box-shadow .5s var(--ease),
        border-color .35s ease,
        background-position .9s var(--ease);
}
body .crm-dashboard.crm-dashboard .kpi-card span{
    font-weight:500;
}
body .crm-dashboard.crm-dashboard .kpi-card h2{
    font-weight:700;
}
body .crm-dashboard.crm-dashboard .kpi-leads{
    background:
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='38' height='38' viewBox='0 0 38 38'%3E%3Ccircle cx='19' cy='19' r='17.5' fill='%233b82f6' stroke='white' stroke-opacity='.45' stroke-width='1.2'/%3E%3Cg fill='none' stroke='white' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.5 19h13'/%3E%3Cpath d='M20.5 13.5l5.5 5.5-5.5 5.5'/%3E%3C/g%3E%3C/svg%3E") right 24px center / 38px 38px no-repeat,
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 100' preserveAspectRatio='none'%3E%3Cdefs%3E%3ClinearGradient id='f' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0' stop-color='%2360a5fa' stop-opacity='.55'/%3E%3Cstop offset='1' stop-color='%2360a5fa' stop-opacity='.12'/%3E%3C/linearGradient%3E%3Cfilter id='b' x='-5%25' y='-80%25' width='110%25' height='260%25'%3E%3CfeGaussianBlur stdDeviation='3.5'/%3E%3C/filter%3E%3C/defs%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42 V100 H0 Z' fill='url(%23f)'/%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42' fill='none' stroke='%2360a5fa' stroke-width='5' filter='url(%23b)' opacity='.9'/%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42' fill='none' stroke='white' stroke-opacity='.95' stroke-width='2'/%3E%3Cpath d='M0 72 C80 40 140 96 210 70 S335 36 400 60' fill='none' stroke='white' stroke-opacity='.4' stroke-width='1.2'/%3E%3C/svg%3E") 0% 100% / 150% 78px no-repeat,
        linear-gradient(115deg,rgba(255,255,255,.20) 0%,rgba(255,255,255,.04) 34%,transparent 52%),
        linear-gradient(160deg,#2563eb 0%,#1d4ed8 35%,#0b2a8a 100%);
}
body .crm-dashboard.crm-dashboard .kpi-leads:hover{
    background-position:right 24px center, 100% 100%, 0 0, 0 0;
}
body .crm-dashboard.crm-dashboard .kpi-won{
    background:
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='38' height='38' viewBox='0 0 38 38'%3E%3Ccircle cx='19' cy='19' r='17.5' fill='%238b5cf6' stroke='white' stroke-opacity='.45' stroke-width='1.2'/%3E%3Cg fill='none' stroke='white' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.5 19h13'/%3E%3Cpath d='M20.5 13.5l5.5 5.5-5.5 5.5'/%3E%3C/g%3E%3C/svg%3E") right 24px center / 38px 38px no-repeat,
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 100' preserveAspectRatio='none'%3E%3Cdefs%3E%3ClinearGradient id='f' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0' stop-color='%23b58cff' stop-opacity='.55'/%3E%3Cstop offset='1' stop-color='%23b58cff' stop-opacity='.12'/%3E%3C/linearGradient%3E%3Cfilter id='b' x='-5%25' y='-80%25' width='110%25' height='260%25'%3E%3CfeGaussianBlur stdDeviation='3.5'/%3E%3C/filter%3E%3C/defs%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42 V100 H0 Z' fill='url(%23f)'/%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42' fill='none' stroke='%23b58cff' stroke-width='5' filter='url(%23b)' opacity='.9'/%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42' fill='none' stroke='white' stroke-opacity='.95' stroke-width='2'/%3E%3Cpath d='M0 72 C80 40 140 96 210 70 S335 36 400 60' fill='none' stroke='white' stroke-opacity='.4' stroke-width='1.2'/%3E%3C/svg%3E") 0% 100% / 150% 78px no-repeat,
        linear-gradient(115deg,rgba(255,255,255,.20) 0%,rgba(255,255,255,.04) 34%,transparent 52%),
        linear-gradient(160deg,#7c3aed 0%,#5b21b6 38%,#2e1065 100%);
}
body .crm-dashboard.crm-dashboard .kpi-won:hover{
    background-position:right 24px center, 100% 100%, 0 0, 0 0;
}
body .crm-dashboard.crm-dashboard .kpi-customers{
    background:
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='38' height='38' viewBox='0 0 38 38'%3E%3Ccircle cx='19' cy='19' r='17.5' fill='%2314b8a6' stroke='white' stroke-opacity='.45' stroke-width='1.2'/%3E%3Cg fill='none' stroke='white' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.5 19h13'/%3E%3Cpath d='M20.5 13.5l5.5 5.5-5.5 5.5'/%3E%3C/g%3E%3C/svg%3E") right 24px center / 38px 38px no-repeat,
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 100' preserveAspectRatio='none'%3E%3Cdefs%3E%3ClinearGradient id='f' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0' stop-color='%232dd4bf' stop-opacity='.55'/%3E%3Cstop offset='1' stop-color='%232dd4bf' stop-opacity='.12'/%3E%3C/linearGradient%3E%3Cfilter id='b' x='-5%25' y='-80%25' width='110%25' height='260%25'%3E%3CfeGaussianBlur stdDeviation='3.5'/%3E%3C/filter%3E%3C/defs%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42 V100 H0 Z' fill='url(%23f)'/%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42' fill='none' stroke='%232dd4bf' stroke-width='5' filter='url(%23b)' opacity='.9'/%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42' fill='none' stroke='white' stroke-opacity='.95' stroke-width='2'/%3E%3Cpath d='M0 72 C80 40 140 96 210 70 S335 36 400 60' fill='none' stroke='white' stroke-opacity='.4' stroke-width='1.2'/%3E%3C/svg%3E") 0% 100% / 150% 78px no-repeat,
        linear-gradient(115deg,rgba(255,255,255,.20) 0%,rgba(255,255,255,.04) 34%,transparent 52%),
        linear-gradient(160deg,#14b8a6 0%,#0f766e 38%,#064e3b 100%);
}
body .crm-dashboard.crm-dashboard .kpi-customers:hover{
    background-position:right 24px center, 100% 100%, 0 0, 0 0;
}
body .crm-dashboard.crm-dashboard .kpi-followups{
    background:
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='38' height='38' viewBox='0 0 38 38'%3E%3Ccircle cx='19' cy='19' r='17.5' fill='%23a855f7' stroke='white' stroke-opacity='.45' stroke-width='1.2'/%3E%3Cg fill='none' stroke='white' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.5 19h13'/%3E%3Cpath d='M20.5 13.5l5.5 5.5-5.5 5.5'/%3E%3C/g%3E%3C/svg%3E") right 24px center / 38px 38px no-repeat,
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 100' preserveAspectRatio='none'%3E%3Cdefs%3E%3ClinearGradient id='f' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0' stop-color='%23c084fc' stop-opacity='.55'/%3E%3Cstop offset='1' stop-color='%23c084fc' stop-opacity='.12'/%3E%3C/linearGradient%3E%3Cfilter id='b' x='-5%25' y='-80%25' width='110%25' height='260%25'%3E%3CfeGaussianBlur stdDeviation='3.5'/%3E%3C/filter%3E%3C/defs%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42 V100 H0 Z' fill='url(%23f)'/%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42' fill='none' stroke='%23c084fc' stroke-width='5' filter='url(%23b)' opacity='.9'/%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42' fill='none' stroke='white' stroke-opacity='.95' stroke-width='2'/%3E%3Cpath d='M0 72 C80 40 140 96 210 70 S335 36 400 60' fill='none' stroke='white' stroke-opacity='.4' stroke-width='1.2'/%3E%3C/svg%3E") 0% 100% / 150% 78px no-repeat,
        linear-gradient(115deg,rgba(255,255,255,.20) 0%,rgba(255,255,255,.04) 34%,transparent 52%),
        linear-gradient(160deg,#9333ea 0%,#6b21a8 38%,#3b0764 100%);
}
body .crm-dashboard.crm-dashboard .kpi-followups:hover{
    background-position:right 24px center, 100% 100%, 0 0, 0 0;
}
body .crm-dashboard.crm-dashboard .kpi-activities{
    background:
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='38' height='38' viewBox='0 0 38 38'%3E%3Ccircle cx='19' cy='19' r='17.5' fill='%23f59e0b' stroke='white' stroke-opacity='.45' stroke-width='1.2'/%3E%3Cg fill='none' stroke='white' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12.5 19h13'/%3E%3Cpath d='M20.5 13.5l5.5 5.5-5.5 5.5'/%3E%3C/g%3E%3C/svg%3E") right 24px center / 38px 38px no-repeat,
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 100' preserveAspectRatio='none'%3E%3Cdefs%3E%3ClinearGradient id='f' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0' stop-color='%23fbbf24' stop-opacity='.55'/%3E%3Cstop offset='1' stop-color='%23fbbf24' stop-opacity='.12'/%3E%3C/linearGradient%3E%3Cfilter id='b' x='-5%25' y='-80%25' width='110%25' height='260%25'%3E%3CfeGaussianBlur stdDeviation='3.5'/%3E%3C/filter%3E%3C/defs%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42 V100 H0 Z' fill='url(%23f)'/%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42' fill='none' stroke='%23fbbf24' stroke-width='5' filter='url(%23b)' opacity='.9'/%3E%3Cpath d='M0 58 C70 18 130 88 205 56 S330 14 400 42' fill='none' stroke='white' stroke-opacity='.95' stroke-width='2'/%3E%3Cpath d='M0 72 C80 40 140 96 210 70 S335 36 400 60' fill='none' stroke='white' stroke-opacity='.4' stroke-width='1.2'/%3E%3C/svg%3E") 0% 100% / 150% 78px no-repeat,
        linear-gradient(115deg,rgba(255,255,255,.20) 0%,rgba(255,255,255,.04) 34%,transparent 52%),
        linear-gradient(160deg,#f59e0b 0%,#b45309 40%,#78350f 100%);
}
body .crm-dashboard.crm-dashboard .kpi-activities:hover{
    background-position:right 24px center, 100% 100%, 0 0, 0 0;
}
</style>

@endsection