@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">

    <!-- Header -->
    <div class="page-header crm-header">
        <div>
            <h1>{{ $project->name }}</h1>
            <p>Project details and workflow overview</p>
        </div>
        <div style="display:flex; gap:12px;">
            @if(auth()->user()->hasPermission('projects.update'))
            <a href="{{ route('projects.edit', $project) }}" class="primary-btn">
                <i class="bi bi-pencil"></i> Edit Project
            </a>
            @endif
            <a href="{{ route('projects.index') }}" class="module-secondary-btn secondary-btn">
                Back to Projects
            </a>
        </div>
    </div>

    <!-- Project Details Card -->
    <div class="module-card module-form-card lead-crud-form-card crm-card crm-form-card mb-4">
        
        <div class="enterprise-table-heading mb-4">
            <div>
                <h2>Project Information</h2>
                <small>Overview of project attributes and timeline</small>
            </div>
        </div>

        <div class="form-grid module-form-grid">

            <!-- Project Name -->
            <div class="form-group module-field">
                <label class="form-label" style="color: #64748b;">Project Name</label>
                <div class="form-control" style="display: flex; align-items: center; background: #f8fafc; color: #0f172a !important; font-weight: 500;">
                    {{ $project->name }}
                </div>
            </div>

            <!-- Client Name -->
            <div class="form-group module-field">
                <label class="form-label" style="color: #64748b;">Client Name</label>
                <div class="form-control" style="display: flex; align-items: center; background: #f8fafc; color: #0f172a !important; font-weight: 500;">
                    {{ $project->client_name ?? '—' }}
                </div>
            </div>

            <!-- Start Date -->
            <div class="form-group module-field">
                <label class="form-label" style="color: #64748b;">Start Date</label>
                <div class="form-control" style="display: flex; align-items: center; background: #f8fafc; color: #0f172a !important; font-weight: 500;">
                    {{ $project->start_date }}
                </div>
            </div>

            <!-- End Date -->
            <div class="form-group module-field">
                <label class="form-label" style="color: #64748b;">End Date</label>
                <div class="form-control" style="display: flex; align-items: center; background: #f8fafc; color: #0f172a !important; font-weight: 500;">
                    {{ $project->end_date }}
                </div>
            </div>

            <!-- Status -->
            <div class="form-group module-field">
                <label class="form-label" style="color: #64748b;">Status</label>
                <div class="form-control" style="display: flex; align-items: center; background: #f8fafc; color: #0f172a !important; font-weight: 500;">
                    {{ ucfirst($project->status) }}
                </div>
            </div>

            <!-- Created By -->
            <div class="form-group module-field">
                <label class="form-label" style="color: #64748b;">Created By</label>
                <div class="form-control" style="display: flex; align-items: center; background: #f8fafc; color: #0f172a !important; font-weight: 500;">
                    {{ $project->creator->name ?? '—' }}
                </div>
            </div>

            <!-- Description (Full Width) -->
            <div class="form-group full-width module-field-wide module-field">
                <label class="form-label" style="color: #64748b;">Description</label>
                <div class="form-textarea" style="background: #f8fafc; color: #0f172a !important; height: auto; min-height: 100px; padding: 12px; font-weight: 500;">
                    {{ $project->description ?? 'No description available' }}
                </div>
            </div>

        </div>

    </div>

    <!-- Project Tasks Card -->
    <div class="module-card crm-card">
        
        <div class="page-header crm-header" style="margin-bottom: 0; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 16px;">
            <div>
                <h2>Project Tasks</h2>
                <p>Tasks linked with this project</p>
            </div>
        </div>

        <div class="module-table-wrap">
            <table class="module-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($tasks as $task)
                    <tr>
                        <td>{{ $task->title }}</td>
                        <td>{{ $task->assignedUser->name ?? 'Unassigned' }}</td>
                        <td>{{ ucfirst($task->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="padding: 24px; text-align: center; color: #64748b;">
                            No tasks found linked to this project
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $tasks->links() }}

    </div>

</div>
@endsection
