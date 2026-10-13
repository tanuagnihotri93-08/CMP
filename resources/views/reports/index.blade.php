@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div>
            <h1>CRM Reports</h1>
            <p>Analyze CRM performance and export filtered business data</p>
        </div>
    </div>

    <x-crm.validation-alert title="Unable to load or export the selected report." />
    <x-crm.field-error field="export" />

    <div class="module-card enterprise-filter-card crm-card crm-filter-card">
        <div class="enterprise-table-heading"><div><h2>Report filters</h2><small>Focus analysis by timeframe, owner, status, or module</small></div></div>
        <form action="{{ route('reports.index') }}" method="GET" class="module-form-grid" novalidate>
            <div class="module-field">
                <label for="date_from">From</label>
                <input id="date_from" type="date" name="date_from" @class(['crm-invalid-field' => $errors->has('date_from')]) aria-invalid="{{ $errors->has('date_from') ? 'true' : 'false' }}" @if($errors->has('date_from')) aria-describedby="crm-error-date-from" @endif value="{{ $filters['date_from'] ?? '' }}">
                <x-crm.field-error field="date_from" id="crm-error-date-from" />
            </div>
            <div class="module-field">
                <label for="date_to">To</label>
                <input id="date_to" type="date" name="date_to" @class(['crm-invalid-field' => $errors->has('date_to')]) aria-invalid="{{ $errors->has('date_to') ? 'true' : 'false' }}" @if($errors->has('date_to')) aria-describedby="crm-error-date-to" @endif value="{{ $filters['date_to'] ?? '' }}">
                <x-crm.field-error field="date_to" id="crm-error-date-to" />
            </div>
            <div class="module-field">
                <label for="user_id">Assigned user</label>
                <select id="user_id" name="user_id" @class(['crm-invalid-field' => $errors->has('user_id')]) aria-invalid="{{ $errors->has('user_id') ? 'true' : 'false' }}" @if($errors->has('user_id')) aria-describedby="crm-error-user-id" @endif>
                    <option value="">All users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                <x-crm.field-error field="user_id" id="crm-error-user-id" />
            </div>
            <div class="module-field">
                <label for="status">Status</label>
                <select id="status" name="status" @class(['crm-invalid-field' => $errors->has('status')]) aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="crm-error-status" @endif>
                    <option value="">All statuses</option>
                    @foreach(['new', 'qualified', 'proposal', 'won', 'lost', 'active', 'inactive', 'scheduled', 'pending', 'completed'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <x-crm.field-error field="status" />
            </div>
            <div class="module-field">
                <label for="module">Module</label>
                <select id="module" name="module" @class(['crm-invalid-field' => $errors->has('module')]) aria-invalid="{{ $errors->has('module') ? 'true' : 'false' }}" @if($errors->has('module')) aria-describedby="crm-error-module" @endif>
                    <option value="">All modules</option>
                    @foreach($moduleOptions as $module)
                        <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ ucfirst(str_replace('_', ' ', $module)) }}</option>
                    @endforeach
                </select>
                <x-crm.field-error field="module" />
            </div>
            <div class="module-form-actions">
                <button class="primary-btn" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Apply Filters</button>
                <a class="module-secondary-btn" href="{{ route('reports.index') }}">Clear</a>
            </div>
        </form>
    </div>

    @foreach($visibleModules as $module)
        @php($report = match ($module) {
            'leads' => $leadReport,
            'customers' => $customerReport,
            'follow_ups' => $followUpReport,
            'activities' => $activityReport,
        })
        <section class="module-card enterprise-report-card crm-card crm-table-card" aria-labelledby="report-{{ $module }}">
            <div class="enterprise-report-heading">
                <div>
                    <h2 id="report-{{ $module }}">{{ ucfirst(str_replace('_', ' ', $module)) }} Report</h2>
                    <p>Results reflect the selected filters and your record access</p>
                </div>
                <div class="enterprise-report-actions">
                    @if(auth()->user()->hasPermission('reports.export'))
                        <a class="module-secondary-btn" data-export-loading href="{{ route('reports.export', array_merge(['module' => $module, 'format' => 'excel'], request()->query())) }}"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Export Excel</a>
                        <a class="module-secondary-btn" data-export-loading href="{{ route('reports.export', array_merge(['module' => $module, 'format' => 'pdf'], request()->query())) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Export PDF</a>
                    @endif
                </div>
            </div>

            @if($report['total'] === 0)
                @php($createPermission = ['leads' => 'leads.create', 'customers' => 'customers.create', 'follow_ups' => 'follow_ups.create', 'activities' => 'activities.create'][$module])
                @php($createRoute = ['leads' => 'leads.create', 'customers' => 'customers.create', 'follow_ups' => 'followups.create', 'activities' => 'activities.create'][$module])
                @include('partials.crm-empty-state', [
                    'icon' => match ($module) {
                        'leads' => 'bi-person-lines-fill',
                        'customers' => 'bi-people',
                        'follow_ups' => 'bi-calendar2-check',
                        default => 'bi-activity',
                    },
                    'title' => 'No '.str_replace('_', ' ', $module).' data in this report',
                    'description' => request()->query()
                        ? 'No records match the selected report filters. Clear or adjust the filters and try again.'
                        : 'This report will populate as CRM records are added.',
                    'actionUrl' => auth()->user()->hasPermission($createPermission) ? route($createRoute) : null,
                    'actionLabel' => auth()->user()->hasPermission($createPermission)
                        ? match ($module) {
                            'leads' => 'Add lead',
                            'customers' => 'Add customer',
                            'follow_ups' => 'Schedule follow-up',
                            default => 'Add activity',
                        }
                        : null,
                ])
            @elseif($module === 'leads')
                <div class="module-stats crm-stats">
                    <div class="module-stat"><span>Total Leads</span><strong>{{ $report['total'] }}</strong></div>
                    <div class="module-stat"><span>New in Range</span><strong>{{ $report['new_in_range'] }}</strong></div>
                    <div class="module-stat"><span>Won / Converted</span><strong>{{ $report['converted'] }}</strong></div>
                    <div class="module-stat"><span>Lost</span><strong>{{ $report['lost'] }}</strong></div>
                    <div class="module-stat"><span>Conversion Rate</span><strong>{{ $report['conversion_rate'] }}%</strong></div>
                </div>
                <h3>Leads by Status</h3>
                <div class="module-table-wrap"><table class="module-table"><thead><tr><th>Status</th><th>Lead Count</th></tr></thead><tbody>
                    @forelse($report['by_status'] as $status => $count)
                        <tr><td>{{ ucfirst($status) }}</td><td>{{ $count }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="dashboard-empty">No leads match these filters.</td></tr>
                    @endforelse
                </tbody></table></div>
                <h3>Leads by Source</h3>
                <div class="module-table-wrap"><table class="module-table"><thead><tr><th>Source</th><th>Lead Count</th></tr></thead><tbody>
                    @forelse($report['by_source'] as $source => $count)
                        <tr><td>{{ $source }}</td><td>{{ $count }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="dashboard-empty">No source data is available.</td></tr>
                    @endforelse
                </tbody></table></div>
            @elseif($module === 'customers')
                <div class="module-stats crm-stats">
                    <div class="module-stat"><span>Total Customers</span><strong>{{ $report['total'] }}</strong></div>
                    <div class="module-stat"><span>New in Range</span><strong>{{ $report['new_in_range'] }}</strong></div>
                    <div class="module-stat"><span>Active Customers</span><strong>{{ $report['active'] }}</strong></div>
                    <div class="module-stat"><span>Converted from Leads</span><strong>{{ $report['converted_from_lead'] }}</strong></div>
                </div>
                <h3>Customer Growth by Month</h3>
                <div class="module-table-wrap"><table class="module-table"><thead><tr><th>Month</th><th>New Customers</th></tr></thead><tbody>
                    @forelse($report['growth'] as $month => $count)
                        <tr><td>{{ $month }}</td><td>{{ $count }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="dashboard-empty">No customer growth data matches these filters.</td></tr>
                    @endforelse
                </tbody></table></div>
            @elseif($module === 'follow_ups')
                <div class="module-stats crm-stats">
                    <div class="module-stat"><span>Total Follow Ups</span><strong>{{ $report['total'] }}</strong></div>
                    <div class="module-stat"><span>Completed</span><strong>{{ $report['completed'] }}</strong></div>
                    <div class="module-stat"><span>Pending</span><strong>{{ $report['pending'] }}</strong></div>
                    <div class="module-stat"><span>Overdue</span><strong>{{ $report['overdue'] }}</strong></div>
                </div>
                <h3>User-wise Follow Up Performance</h3>
                <div class="module-table-wrap"><table class="module-table"><thead><tr><th>Assigned User</th><th>Total</th><th>Completed</th></tr></thead><tbody>
                    @forelse($report['by_user'] as $name => $performance)
                        <tr><td>{{ $name }}</td><td>{{ $performance['total'] }}</td><td>{{ $performance['completed'] }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="dashboard-empty">No follow ups match these filters.</td></tr>
                    @endforelse
                </tbody></table></div>
            @else
                <div class="module-stats crm-stats">
                    <div class="module-stat"><span>Total Activities</span><strong>{{ $report['total'] }}</strong></div>
                    <div class="module-stat"><span>Completed</span><strong>{{ $report['completed'] }}</strong></div>
                    <div class="module-stat"><span>Pending</span><strong>{{ $report['pending'] }}</strong></div>
                </div>
                <h3>Activities by Type</h3>
                <div class="module-table-wrap"><table class="module-table"><thead><tr><th>Activity Type</th><th>Activity Count</th></tr></thead><tbody>
                    @forelse($report['by_type'] as $type => $count)
                        <tr><td>{{ ucfirst($type) }}</td><td>{{ $count }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="dashboard-empty">No activities match these filters.</td></tr>
                    @endforelse
                </tbody></table></div>
            @endif
        </section>
    @endforeach
</div>
@endsection
