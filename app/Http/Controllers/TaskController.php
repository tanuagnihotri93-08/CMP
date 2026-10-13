<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Task::with([
            'project',
            'assignedUser'
        ]);


        if (
            !in_array(
                $user->role?->name,
                ['super_admin', 'manager'],
                true
            )
        ) {

            $query->where(
                'assigned_to',
                $user->id
            );

        }


        $tasks = $query
            ->latest()
            ->get();


        return view(
            'tasks.index',
            compact('tasks')
        );
    }


    public function create()
    {
        abort_unless(
            Auth::user()->hasPermission('tasks.create'),
            403
        );


        $projects = Project::latest()->get();


        $users = User::where(
            'is_active',
            1
        )->get();


        return view(
            'tasks.create',
            compact(
                'projects',
                'users'
            )
        );
    }


    public function store(Request $request)
    {
        abort_unless(
            Auth::user()->hasPermission('tasks.create'),
            403
        );


        $validated = $request->validate([

            'project_id' => 'required|exists:projects,id',

            'assigned_to' => 'nullable|exists:users,id',

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'priority' => 'required|in:Low,Medium,High',

            'status' => 'required|in:Pending,In Progress,Completed',

            'due_date' => 'nullable|date',

        ]);


        $validated['created_by'] = Auth::id();


        Task::create($validated);


        return redirect()
            ->route('tasks.index')
            ->with(
                'success',
                'Task created successfully.'
            );
    }


    public function show(Task $task)
    {
        $this->checkTaskAccess($task);


        $task->load([
            'project',
            'assignedUser',
            'creator'
        ]);


        return view(
            'tasks.show',
            compact('task')
        );
    }


    public function edit(Task $task)
    {
        $this->checkManagePermission('update');


        $projects = Project::latest()->get();


        $users = User::where(
            'is_active',
            1
        )->get();


        return view(
            'tasks.edit',
            compact(
                'task',
                'projects',
                'users'
            )
        );
    }


    public function update(
        Request $request,
        Task $task
    ) {

        $this->checkManagePermission('update');


        $validated = $request->validate([

            'project_id' => 'required|exists:projects,id',

            'assigned_to' => 'nullable|exists:users,id',

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'priority' => 'required|in:Low,Medium,High',

            'status' => 'required|in:Pending,In Progress,Completed',

            'due_date' => 'nullable|date',

        ]);


        $task->update($validated);


        return redirect()
            ->route('tasks.index')
            ->with(
                'success',
                'Task updated successfully.'
            );
    }


    public function destroy(Task $task)
    {
        $this->checkManagePermission('delete');


        $task->delete();


        return redirect()
            ->route('tasks.index')
            ->with(
                'success',
                'Task deleted successfully.'
            );
    }


    private function checkTaskAccess(Task $task)
    {
        $user = Auth::user();


        if (
            in_array(
                $user->role?->name,
                ['super_admin', 'manager'],
                true
            )
        ) {
            return;
        }


        abort_unless(
            $task->assigned_to == $user->id,
            404
        );
    }


    private function checkManagePermission(string $action)
    {
        $user = Auth::user();


        abort_unless(
            $user->hasPermission("tasks.$action"),
            403
        );


        abort_unless(
            in_array(
                $user->role?->name,
                ['super_admin', 'manager'],
                true
            ),
            403
        );
    }
}