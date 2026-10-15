@extends('layouts.app')

@section('content')

<div class="crm-dashboard">

    <!-- Analytics Hero -->

    <div class="hero-card dashboard-hero">

        <div>

            <p class="dashboard-eyebrow">
                PERFORMANCE ANALYTICS
            </p>

            <h1>
                Analytics Command Center
            </h1>

            <p class="sub-text">
                Track pipeline growth, customer conversion, and team productivity from one place.
            </p>

        </div>

    </div>


    <!-- Analytics KPI -->

    <div class="kpi-grid">


        <div class="kpi-card blue dashboard-kpi">

            <div>
                <span>
                    Total Leads
                </span>

                <h2>
                    {{ $totalLeads }}
                </h2>

                <small>
                    Business opportunities
                </small>
            </div>

        </div>



        <div class="kpi-card green dashboard-kpi">

            <div>
                <span>
                    Total Customers
                </span>

                <h2>
                    {{ $totalCustomers }}
                </h2>

                <small>
                    Converted relationships
                </small>
            </div>

        </div>



        <div class="kpi-card purple dashboard-kpi">

            <div>
                <span>
                    Conversion Rate
                </span>

                <h2>
                    {{ $conversionRate }}%
                </h2>

                <small>
                    Lead success ratio
                </small>
            </div>

        </div>



        <div class="kpi-card orange dashboard-kpi">

            <div>
                <span>
                    Pending Follow Ups
                </span>

                <h2>
                    {{ $pendingFollowUps }}
                </h2>

                <small>
                    Upcoming actions
                </small>
            </div>

        </div>



        <div class="kpi-card cyan dashboard-kpi">

            <div>
                <span>
                    Completed Activities
                </span>

                <h2>
                    {{ $totalActivities }}
                </h2>

                <small>
                    Team productivity
                </small>
            </div>

        </div>


    </div>


    <!-- Charts -->

    <div class="analytics-grid">

        Lead Growth Chart

        Lead Status Chart

    </div>


    <!-- Team Performance -->

    <div class="hero-card">

        Team Performance Table

    </div>


</div>

@endsection