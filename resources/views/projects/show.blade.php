@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page project-show-page">

    <!-- Header -->
    <div class="page-header crm-header">
        <div>
            <h1>{{ $project->name }}</h1>
            <p>Project details and workflow overview</p>
        </div>
        <div class="project-header-actions">
            @if(auth()->user()->hasPermission('projects.update'))
            <a href="{{ route('projects.edit', $project) }}" class="primary-btn">
                <i class="bi bi-pencil" aria-hidden="true"></i> Edit Project
            </a>
            @endif
            <a href="{{ route('projects.index') }}" class="module-secondary-btn secondary-btn">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to Projects
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
                <label class="form-label">Project Name</label>
                <div class="form-control project-detail-value">
                    {{ $project->name }}
                </div>
            </div>

            <!-- Client Name -->
            <div class="form-group module-field">
                <label class="form-label">Client Name</label>
                <div class="form-control project-detail-value">
                    {{ $project->client_name ?? '—' }}
                </div>
            </div>

            <!-- Start Date -->
            <div class="form-group module-field">
                <label class="form-label">Start Date</label>
                <div class="form-control project-detail-value">
                    {{ $project->start_date }}
                </div>
            </div>

            <!-- End Date -->
            <div class="form-group module-field">
                <label class="form-label">End Date</label>
                <div class="form-control project-detail-value">
                    {{ $project->end_date }}
                </div>
            </div>

            <!-- Status -->
            <div class="form-group module-field">
                <label class="form-label">Status</label>
                <div class="form-control project-detail-value">
                    {{ ucfirst($project->status) }}
                </div>
            </div>

            <!-- Created By -->
            <div class="form-group module-field">
                <label class="form-label">Created By</label>
                <div class="form-control project-detail-value">
                    {{ $project->creator->name ?? '—' }}
                </div>
            </div>

            <!-- Description (Full Width) -->
            <div class="form-group full-width module-field-wide module-field">
                <label class="form-label">Description</label>
                <div class="form-textarea project-detail-description">
                    {{ $project->description ?? 'No description available' }}
                </div>
            </div>

        </div>

    </div>

    <!-- Project Tasks Card -->
    <div class="module-card crm-card">
        
        <div class="page-header crm-header project-task-header">
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
                        <td colspan="3" class="project-task-empty">
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
