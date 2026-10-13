@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    
    <!-- Header -->
    <div class="page-header crm-header">
        <div>
            <h1>Edit Task</h1>
            <p>Update task details and progress</p>
        </div>
        <a href="{{ route('tasks.index') }}" class="module-secondary-btn">Back to Tasks</a>
    </div>

    <!-- Form Card -->
    <div class="module-card module-form-card lead-crud-form-card crm-card crm-form-card">
        
        <x-crm.validation-alert />

        <form action="{{ route('tasks.update', $task) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            <div class="form-grid module-form-grid">

                <!-- Project -->
                <div class="form-group module-field">
                    <label class="form-label">Project <span>*</span></label>
                    <select id="project_id" name="project_id" @class(['form-select', 'crm-invalid-field' => $errors->has('project_id')]) required>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ (old('project_id', $task->project_id) == $project->id) ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-crm.field-error field="project_id" />
                </div>

                <!-- Assign User -->
                <div class="form-group module-field">
                    <label class="form-label">Assign User</label>
                    <select id="assigned_to" name="assigned_to" @class(['form-select', 'crm-invalid-field' => $errors->has('assigned_to')])>
                        <option value="">Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ (old('assigned_to', $task->assigned_to) == $user->id) ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-crm.field-error field="assigned_to" />
                </div>

                <!-- Task Title (Full Width) -->
                <div class="form-group full-width module-field-wide module-field">
                    <label class="form-label">Task Title <span>*</span></label>
                    <input type="text"
                        id="title"
                        name="title"
                        @class(['form-control', 'crm-invalid-field' => $errors->has('title')])
                        value="{{ old('title', $task->title) }}"
                        placeholder="Enter task title..."
                        required>
                    <x-crm.field-error field="title" />
                </div>

                <!-- Priority -->
                <div class="form-group module-field">
                    <label class="form-label">Priority</label>
                    <select id="priority" name="priority" @class(['form-select', 'crm-invalid-field' => $errors->has('priority')])>
                        <option value="Low" {{ (old('priority', $task->priority) == 'Low') ? 'selected' : '' }}>Low</option>
                        <option value="Medium" {{ (old('priority', $task->priority) == 'Medium') ? 'selected' : '' }}>Medium</option>
                        <option value="High" {{ (old('priority', $task->priority) == 'High') ? 'selected' : '' }}>High</option>
                    </select>
                    <x-crm.field-error field="priority" />
                </div>

                <!-- Status -->
                <div class="form-group module-field">
                    <label class="form-label">Status <span>*</span></label>
                    <select id="status" name="status" @class(['form-select', 'crm-invalid-field' => $errors->has('status')]) required>
                        <option value="Pending" {{ (old('status', $task->status) == 'Pending') ? 'selected' : '' }}>Pending</option>
                        <option value="In Progress" {{ (old('status', $task->status) == 'In Progress') ? 'selected' : '' }}>In Progress</option>
                        <option value="Completed" {{ (old('status', $task->status) == 'Completed') ? 'selected' : '' }}>Completed</option>
                    </select>
                    <x-crm.field-error field="status" />
                </div>

                <!-- Due Date -->
                <div class="form-group module-field">
                    <label class="form-label">Due Date</label>
                    <input type="date"
                        id="due_date"
                        name="due_date"
                        @class(['form-control', 'crm-invalid-field' => $errors->has('due_date')])
                        value="{{ old('due_date', $task->due_date) }}">
                    <x-crm.field-error field="due_date" />
                </div>

                <!-- Description (Full Width) -->
                <div class="form-group full-width module-field-wide module-field">
                    <label class="form-label">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        @class(['form-textarea', 'crm-invalid-field' => $errors->has('description')])
                        rows="4"
                        placeholder="Enter task description...">{{ old('description', $task->description) }}</textarea>
                    <x-crm.field-error field="description" />
                </div>

            </div>

            <!-- Actions -->
            <div class="form-actions module-form-actions">
                <a href="{{ route('tasks.index') }}" class="module-secondary-btn secondary-btn">
                    Cancel
                </a>
                <button type="submit" class="primary-btn">
                    Update Task
                </button>
            </div>

        </form>
    </div>

</div>
@endsection