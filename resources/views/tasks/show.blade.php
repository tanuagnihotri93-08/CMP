@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-4">

    {{-- Header Section --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                Task Details
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                View complete task information and progress
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('tasks.index') }}" 
               class="px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-2xl shadow-sm transition">
                Back
            </a>

            @if(auth()->user()->hasPermission('tasks.update'))
                <a href="{{ route('tasks.edit', $task) }}" 
                   class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-2xl shadow-md transition">
                    Edit Task
                </a>
            @endif
        </div>
    </div>

    {{-- Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main Details Card --}}
        <div class="lg:col-span-2 bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <h3 class="text-2xl font-bold text-gray-900 mb-4">
                {{ $task->title }}
            </h3>

            <div class="mb-6">
                <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">
                    Description
                </span>
                <p class="text-gray-700 text-sm leading-relaxed">
                    {{ $task->description ?? 'No description available.' }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-gray-100">
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">
                        Project
                    </span>
                    <h6 class="text-gray-900 font-semibold text-sm">
                        {{ $task->project?->name ?? 'N/A' }}
                    </h6>
                </div>

                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">
                        Assigned To
                    </span>
                    <h6 class="text-gray-900 font-semibold text-sm">
                        {{ $task->assignedUser?->name ?? 'Unassigned' }}
                    </h6>
                </div>

                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">
                        Created By
                    </span>
                    <h6 class="text-gray-900 font-semibold text-sm">
                        {{ $task->creator?->name ?? 'N/A' }}
                    </h6>
                </div>

                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">
                        Due Date
                    </span>
                    <h6 class="text-gray-900 font-semibold text-sm">
                        {{ $task->due_date ?? '-' }}
                    </h6>
                </div>
            </div>
        </div>

        {{-- Status Panel Card --}}
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 h-fit">
            <h5 class="text-lg font-bold text-gray-900 mb-6">
                Task Status
            </h5>

            <div class="mb-6">
                <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                    Priority
                </span>
                @if($task->priority == 'High')
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700">High</span>
                @elseif($task->priority == 'Medium')
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-700">Medium</span>
                @else
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700">Low</span>
                @endif
            </div>

            <div class="mb-6">
                <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">
                    Status
                </span>
                @if($task->status == 'Completed')
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700">Completed</span>
                @elseif($task->status == 'In Progress')
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-700">In Progress</span>
                @else
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-700">Pending</span>
                @endif
            </div>

            @if(auth()->user()->hasPermission('tasks.delete'))
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="pt-4 border-t border-gray-100">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full py-3 bg-red-50 hover:bg-red-100 text-red-600 font-semibold text-sm rounded-xl transition"
                            onclick="return confirm('Delete this task?')">
                        Delete Task
                    </button>
                </form>
            @endif
        </div>

    </div>

</div>
@endsection