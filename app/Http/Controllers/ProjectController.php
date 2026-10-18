<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        abort_unless($user->hasPermission('projects.view'), 403);

        $isTeamViewer = in_array($user->role?->name, ['super_admin', 'manager'], true);
        $visibleProjects = Project::query();

        if (! $isTeamViewer) {
            $visibleProjects->whereHas('tasks', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        }

        $projectCounts = (clone $visibleProjects)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $totalProjectTasks = Task::query()
            ->whereIn('project_id', (clone $visibleProjects)->select('projects.id'))
            ->when(! $isTeamViewer, fn ($tasks) => $tasks->where('assigned_to', $user->id))
            ->count();

        $query = (clone $visibleProjects)
            ->with('creator')
            ->withCount('tasks');

        if (! $isTeamViewer) {
            $query->withCount([
                'tasks' => function ($q) use ($user) {
                    $q->where('assigned_to', $user->id);
                },
            ]);
        }

        $projects = $query
            ->latest()
            ->paginate(10);

        return view('projects.index', compact('projects') + [
            'totalProjects' => $projectCounts->sum(),
            'activeProjects' => $projectCounts->get('Active', 0),
            'totalProjectTasks' => $totalProjectTasks,
        ]);
    }

    public function create()
    {
        abort_unless(
            Auth::user()->hasPermission('projects.create'),
            403
        );

        return view('projects.create');
    }

    public function store(Request $request)
    {
        abort_unless(
            Auth::user()->hasPermission('projects.create'),
            403
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['Planning', 'Active', 'Completed', 'Cancelled'])],
        ]);

        $validated['created_by'] = Auth::id();

        Project::create($validated);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $user = Auth::user();
        abort_unless($user->hasPermission('projects.view'), 403);

        $this->checkProjectAccess($project);

        $project->load('creator');
        $isTeamViewer = in_array($user->role?->name, ['super_admin', 'manager'], true);
        $tasks = $project->tasks()
            ->when(! $isTeamViewer, fn ($query) => $query->where('assigned_to', $user->id))
            ->with('assignedUser')
            ->latest()
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('projects.show', compact('project', 'tasks'));
    }

    public function edit(Project $project)
    {
        $this->checkManagePermission($project, 'update');

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $this->checkManagePermission($project, 'update');

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['Planning', 'Active', 'Completed', 'Cancelled'])],
        ]);

        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $this->checkManagePermission($project, 'delete');

        if ($project->tasks()->exists()) {
            return redirect()
                ->route('projects.show', $project)
                ->with('error', 'Remove project tasks before deleting project.');
        }

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    private function checkProjectAccess(Project $project)
    {
        $user = Auth::user();

        if ($user->role?->name === 'super_admin' || $user->role?->name === 'manager') {
            return;
        }

        abort_unless(
            $project->tasks()->where('assigned_to', $user->id)->exists(),
            404
        );
    }

    private function checkManagePermission(Project $project, string $action)
    {
        $user = Auth::user();

        abort_unless(
            $user->hasPermission("projects.$action"),
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
