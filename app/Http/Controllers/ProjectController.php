<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Project::with('creator')
            ->withCount('tasks');

        if ($user->role?->name === 'super_admin' || $user->role?->name === 'manager') {
        } else {
            $query->whereHas('tasks', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });

            $query->withCount([
                'tasks' => function ($q) use ($user) {
                    $q->where('assigned_to', $user->id);
                }
            ]);
        }

        $projects = $query
            ->latest()
            ->paginate(10);

        return view('projects.index', compact('projects'));
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:Planning,Active,Completed,Cancelled']
        ]);

        $validated['created_by'] = Auth::id();

        Project::create($validated);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $this->checkProjectAccess($project);

        $project->load([
            'creator',
            'tasks.assignedUser'
        ]);

        return view('projects.show', compact('project'));
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:Planning,Active,Completed,Cancelled']
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

   private function checkManagePermission(string $action)
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