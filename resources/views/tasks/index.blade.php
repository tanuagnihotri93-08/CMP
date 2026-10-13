@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page tasks-index-page">

    <!-- Header -->
    <div class="page-header crm-header">
        <div>
            <h1>
                Task Management
            </h1>
            <p>
                Manage projects tasks, assignments and progress
            </p>
        </div>

        @if(auth()->user()->hasPermission('tasks.create'))
            <a href="{{ route('tasks.create') }}" class="primary-btn">
                + Create Task
            </a>
        @endif
    </div>

    <!-- Stats -->
    <div class="stats-grid">

        <div class="stat-card crm-stat-card">
            <div class="stat-icon">
                <i class="bi bi-list-task"></i>
            </div>
            <div>
                <span>Total Tasks</span>
                <h2>
                    {{ $tasks->count() }}
                </h2>
            </div>
        </div>

        <div class="stat-card crm-stat-card">
            <div class="stat-icon">
                <i class="bi bi-clock"></i>
            </div>
            <div>
                <span>Pending</span>
                <h2>
                    {{ $tasks->where('status','Pending')->count() }}
                </h2>
            </div>
        </div>

        <div class="stat-card crm-stat-card">
            <div class="stat-icon">
                <i class="bi bi-play-circle"></i>
            </div>
            <div>
                <span>In Progress</span>
                <h2>
                    {{ $tasks->where('status','In Progress')->count() }}
                </h2>
            </div>
        </div>

        <div class="stat-card crm-stat-card">
            <div class="stat-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <span>Completed</span>
                <h2>
                    {{ $tasks->where('status','Completed')->count() }}
                </h2>
            </div>
        </div>

    </div>

    @if(session('success'))
    <div class="alert-success">
        {{ session('success') }}
    </div>
    @endif

    <!-- Table Card -->
    <div class="table-card">

        <div class="table-top">
            <div>
                <h2>
                    Task Directory
                </h2>
                <small>
                    Manage tasks and assignments overview
                </small>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Task Information</th>
                        <th>Project</th>
                        <th>Assigned To</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($tasks as $task)
                    <tr>
                        <td>
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            <div class="lead-profile">
                                <div class="avatar">
                                    {{ strtoupper(substr($task->title, 0, 1)) }}
                                </div>
                                <div>
                                    <h4>
                                        {{ $task->title }}
                                    </h4>
                                    @if($task->description)
                                    <p>
                                        {{ Str::limit($task->description, 40) }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            {{ $task->project?->name ?? '-' }}
                        </td>

                        <td>
                            {{ $task->assignedUser?->name ?? 'Unassigned' }}
                        </td>

                        <td>
                            <span class="source">
                                {{ $task->priority }}
                            </span>
                        </td>

                        <td>
                            <span class="status crm-badge {{ Str::slug($task->status) }}">
                                {{ ucfirst($task->status) }}
                            </span>
                        </td>

                        <td>
                            {{ $task->due_date ?? '-' }}
                        </td>

                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('tasks.show', $task->id) }}" class="action-btn view-btn crm-action-btn crm-action-view" aria-label="View {{ $task->title }}">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </a>

                                @if(auth()->user()->hasPermission('tasks.update'))
                                <a href="{{ route('tasks.edit', $task->id) }}" class="action-btn edit-btn crm-action-btn crm-action-edit" aria-label="Edit {{ $task->title }}">
                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                </a>
                                @endif

                                @if(auth()->user()->hasPermission('tasks.delete'))
                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST"
                                    onsubmit="return confirm('Delete this task?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete-btn crm-action-btn crm-action-delete" aria-label="Delete {{ $task->title }}">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td class="crm-empty-cell text-center" colspan="8" style="padding: 40px; color: #64748b;">
                            No Tasks Found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

<style>
.page-wrapper {
    width: 100%;
    padding: 20px;
    background: linear-gradient(135deg, #f8fafc, #eef2ff);
    min-height: 100vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
}

.page-header h1 {
    font-size: 32px;
    font-weight: 800;
    letter-spacing: -.5px;
    color: #0f172a;
}

.page-header p {
    margin-top: 8px;
    font-size: 15px;
    color: #64748b;
}

.primary-btn {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    padding: 13px 26px;
    border-radius: 14px;
    color: white;
    font-weight: 700;
    transition: .3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 15px 35px rgba(37,99,235,.25);
}

.primary-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 45px rgba(37,99,235,.35);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 22px;
    margin-bottom: 35px;
}

.stat-card {
    background: rgba(255,255,255,.75);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255,255,255,.8);
    border-radius: 24px;
    padding: 28px;
    display: flex;
    align-items: center;
    gap: 18px;
    transition: .35s;
    position: relative;
    overflow: hidden;
    box-shadow: 0 12px 30px rgb(15 23 42 / 5%);
}

.stat-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, #2563eb, #7c3aed);
    transform: scaleX(0);
    transition: .35s;
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 40px rgba(15,23,42,.12);
}

.stat-card span {
    color: #64748b;
    font-size: 14px;
}

.stat-card h2 {
    font-size: 36px;
    margin-top: 12px;
    color: #0f172a;
}

.stat-icon {
    width: 55px;
    height: 55px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-card:nth-child(1) .stat-icon {
    background: linear-gradient(135deg,#dbeafe,#bfdbfe);
    color:#2563eb;
}

.stat-card:nth-child(2) .stat-icon {
    background: linear-gradient(135deg,#ede9fe,#ddd6fe);
    color:#7c3aed;
}

.stat-card:nth-child(3) .stat-icon {
    background: linear-gradient(135deg,#fef3c7,#fde68a);
    color:#d97706;
}

.stat-card:nth-child(4) .stat-icon {
    background: linear-gradient(135deg,#dcfce7,#bbf7d0);
    color:#16a34a;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 20px;
}

.table-card {
    background: rgba(255,255,255,.85);
    backdrop-filter: blur(18px);
    border-radius: 28px;
    padding: 30px;
    border: 1px solid rgba(226,232,240,.8);
    box-shadow: 0 25px 70px rgba(15,23,42,.08);
}

.table-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.table-top h2 {
    color: #0f172a;
    font-size: 20px;
}

.table-top small {
    color: #64748b;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: linear-gradient(135deg, #0f172a, #1e293b);
}

thead th {
    padding: 18px;
    color: white;
    font-size: 12px;
    letter-spacing: 1px;
    text-transform: uppercase;
    text-align: left;
}

td {
    padding: 20px 18px;
    border-bottom: 1px solid #eef2f7;
    color: #334155;
    font-size: 14px;
}

tbody tr {
    transition: .3s;
}

tbody tr:hover {
    background: #f8fafc;
}

.lead-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar {
    width: 45px;
    height: 45px;
    border-radius: 14px;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    box-shadow: 0 8px 20px rgba(37,99,235,.3);
}

.status {
    padding: 7px 14px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .3px;
}

.status.pending {
    background: #f1f5f9;
    color: #475569;
}

.status.in-progress {
    background: #dbeafe;
    color: #1d4ed8;
}

.status.completed {
    background: #dcfce7;
    color: #166534;
}

.source {
    background: #f1f5f9;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
}

.action-btn {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: .3s;
    cursor: pointer;
}

.action-btn:hover {
    transform: translateY(-3px) scale(1.05);
}

.view-btn {
    background: #dbeafe;
    color: #2563eb;
}

.edit-btn {
    background: #ede9fe;
    color: #7c3aed;
}

.delete-btn {
    background: #fee2e2;
    color: #dc2626;
}

@media(max-width:900px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .page-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}
</style>
@endsection