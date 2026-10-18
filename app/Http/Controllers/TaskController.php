<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        abort_unless($user->hasPermission('tasks.view'), 403);

        $query = Task::with([
            'project',
            'assignedUser',
        ]);

        if (
            ! in_array(
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

        $taskCounts = (clone $query)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $tasks = $query
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view(
            'tasks.index',
            compact('tasks') + [
                'totalTasks' => $taskCounts->sum(),
                'pendingTasks' => $taskCounts->get('Pending', 0),
                'inProgressTasks' => $taskCounts->get('In Progress', 0),
                'completedTasks' => $taskCounts->get('Completed', 0),
            ]
        );
    }

    public function create()
    {
        abort_unless(
            Auth::user()->hasPermission('tasks.create'),
            403
        );

        $projects = Project::query()
            ->select(['id', 'name'])
            ->latest()
            ->cursor();

        $users = User::query()
            ->where('is_active', true)
            ->select(['id', 'name'])
            ->lazy(500);

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

            'project_id' => ['required', 'integer', 'exists:projects,id'],

            'assigned_to' => ['nullable', 'integer', 'exists:users,id,is_active,1'],

            'title' => ['required', 'string', 'min:2', 'max:255'],

            'description' => ['nullable', 'string', 'max:5000'],

            'priority' => ['required', Rule::in(['Low', 'Medium', 'High'])],

            'status' => ['required', Rule::in(['Pending', 'In Progress', 'Completed'])],

            'due_date' => ['nullable', 'date_format:Y-m-d'],

        ]);

        $this->authorizeProjectReference($request, (int) $validated['project_id']);

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
        abort_unless(Auth::user()->hasPermission('tasks.view'), 403);

        $this->checkTaskAccess($task);

        $task->load([
            'project',
            'assignedUser',
            'creator',
        ]);

        return view(
            'tasks.show',
            compact('task')
        );
    }

    public function edit(Task $task)
    {
        $this->checkManagePermission('update');

        $projects = Project::query()
            ->select(['id', 'name'])
            ->latest()
            ->cursor();

        $users = User::query()
            ->where('is_active', true)
            ->select(['id', 'name'])
            ->lazy(500);

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

            'project_id' => ['required', 'integer', 'exists:projects,id'],

            'assigned_to' => ['nullable', 'integer', 'exists:users,id,is_active,1'],

            'title' => ['required', 'string', 'min:2', 'max:255'],

            'description' => ['nullable', 'string', 'max:5000'],

            'priority' => ['required', Rule::in(['Low', 'Medium', 'High'])],

            'status' => ['required', Rule::in(['Pending', 'In Progress', 'Completed'])],

            'due_date' => ['nullable', 'date_format:Y-m-d'],

        ]);

        $this->authorizeProjectReference($request, (int) $validated['project_id']);

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

    private function authorizeProjectReference(Request $request, int $projectId): void
    {
        $user = $request->user();
        $project = Project::query()->findOrFail($projectId);

        if (in_array($user->role?->name, ['super_admin', 'manager'], true)) {
            return;
        }

        abort_unless(
            $project->tasks()->where('assigned_to', $user->id)->exists(),
            404
        );
    }
}
