@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page crm-task-detail-page">

    {{-- Header Section --}}
    <div class="page-header crm-header">
        <div>
            <h1>
                Task Details
            </h1>
            <p>
                View complete task information and progress
            </p>
        </div>

        <div class="module-form-actions task-detail-actions">
            <a href="{{ route('tasks.index') }}" 
               class="module-secondary-btn secondary-btn">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to tasks
            </a>

            @if(auth()->user()->hasPermission('tasks.update'))
                <a href="{{ route('tasks.edit', $task) }}" 
                   class="primary-btn">
                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                    Edit Task
                </a>
            @endif
        </div>
    </div>

    {{-- Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 task-detail-grid">

        {{-- Main Details Card --}}
        <div class="lg:col-span-2 module-card crm-card task-detail-card task-detail-main">
            <h2 class="task-detail-heading">
                {{ $task->title }}
            </h2>

            <div class="mb-6">
                <span class="task-detail-label">
                    Description
                </span>
                <p class="task-detail-copy">
                    {{ $task->description ?? 'No description available.' }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 task-detail-meta-grid">
                <div>
                    <span class="task-detail-label">
                        Project
                    </span>
                    <p class="task-detail-value">
                        {{ $task->project?->name ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <span class="task-detail-label">
                        Assigned To
                    </span>
                    <p class="task-detail-value">
                        {{ $task->assignedUser?->name ?? 'Unassigned' }}
                    </p>
                </div>

                <div>
                    <span class="task-detail-label">
                        Created By
                    </span>
                    <p class="task-detail-value">
                        {{ $task->creator?->name ?? 'N/A' }}
                    </p>
                </div>

                <div>
                    <span class="task-detail-label">
                        Due Date
                    </span>
                    <p class="task-detail-value">
                        {{ $task->due_date ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Status Panel Card --}}
        <div class="module-card crm-card task-detail-card task-detail-status">
            <h2 class="task-detail-heading">
                Task Status
            </h2>

            <div class="mb-6">
                <span class="task-detail-label">
                    Priority
                </span>
                @if($task->priority == 'High')
                    <span class="task-detail-badge task-priority-high">High</span>
                @elseif($task->priority == 'Medium')
                    <span class="task-detail-badge task-priority-medium">Medium</span>
                @else
                    <span class="task-detail-badge task-priority-low">Low</span>
                @endif
            </div>

            <div class="mb-6">
                <span class="task-detail-label">
                    Status
                </span>
                @if($task->status == 'Completed')
                    <span class="task-detail-badge task-status-completed">Completed</span>
                @elseif($task->status == 'In Progress')
                    <span class="task-detail-badge task-status-progress">In Progress</span>
                @else
                    <span class="task-detail-badge task-status-pending">Pending</span>
                @endif
            </div>

            @if(auth()->user()->hasPermission('tasks.delete'))
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="task-detail-delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="module-danger-btn task-detail-delete"
                            onclick="return confirm('Delete this task?')">
                        <i class="bi bi-trash3" aria-hidden="true"></i>
                        Delete Task
                    </button>
                </form>
            @endif
        </div>

    </div>

</div>
@endsection
