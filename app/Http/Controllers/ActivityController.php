<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{
    public function index()
    {
        $filters = request()->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'completed'])],
        ]);
        $query = request()->user()->constrainToAssignedRecords(Activity::query());
        $activityCounts = (clone $query)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $search = trim($filters['search'] ?? '');
        $activities = (clone $query)
            ->with(['lead', 'customer'])
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($query) use ($term): void {
                    $query->where('subject', 'like', $term)
                        ->orWhere('type', 'like', $term);

                    if (request()->user()->hasPermission('leads.view')) {
                        $query->orWhereHas('lead', fn ($leadQuery) => $leadQuery->where('name', 'like', $term));
                    }

                    if (request()->user()->hasPermission('customers.view')) {
                        $query->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', $term));
                    }
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('activity_date')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();
        $activities->each(function (Activity $activity): void {
            foreach (['lead', 'customer'] as $relation) {
                $related = $activity->{$relation};
                if ($related && !request()->user()->canAccessRecord($related)) {
                    $activity->setRelation($relation, null);
                }
            }
        });

        $pendingCount = $activities->where('status', 'pending')->count();
        $completedCount = $activities->where('status', 'completed')->count();

        return view('activities.index', compact(
            'activities',
            'filters'
        ) + [
            'totalActivities' => $activityCounts->sum(),
            'pendingCount' => $activityCounts->get('pending', 0),
            'completedCount' => $activityCounts->get('completed', 0),
        ]);
    }


    public function create()
    {
        $activity = null;

        $leads = request()->user()
            ->constrainToAssignedRecords(Lead::query())
            ->select(['id', 'name', 'company_name'])
            ->orderBy('name')
            ->orderBy('id')
            ->lazy(500);
        $customers = request()->user()
            ->constrainToAssignedRecords(Customer::query())
            ->select(['id', 'name', 'company'])
            ->orderBy('name')
            ->orderBy('id')
            ->lazy(500);
        $assignableUsers = $this->assignableUsers();

        $selectedLead = request()->filled('lead_id')
            ? request()->user()->constrainToAssignedRecords(Lead::query())->findOrFail(request('lead_id'))
            : null;

        $selectedCustomer = request()->filled('customer_id')
            ? request()->user()->constrainToAssignedRecords(Customer::query())->findOrFail(request('customer_id'))
            : null;


        return view('activities.create', compact(
            'activity',
            'leads',
            'customers',
            'selectedLead',
            'selectedCustomer',
            'assignableUsers'
        ));
    }


    public function store(Request $request)
    {
        $validated = $this->validateActivity($request);
        $this->authorizeRelatedRecords($request, $validated);
        $this->ensureAssigneeCanAccess($request, $validated);
        $validated['assigned_to_user_id'] ??= $request->user()->id;
        Activity::create($validated);


        return redirect()
            ->route('activities.index')
            ->with('success', 'Activity created successfully.');
    }


    public function show(string $id)
    {
        $activity = Activity::with(['lead', 'customer'])
            ->findOrFail($id);
        abort_unless(request()->user()->canAccessRecord($activity), 404);
        foreach (['lead', 'customer'] as $relation) {
            $related = $activity->{$relation};
            if ($related && !request()->user()->canAccessRecord($related)) {
                $activity->setRelation($relation, null);
            }
        }

        return view('activities.show', compact('activity'));
    }


    public function edit(string $id)
    {
        $activity = Activity::findOrFail($id);

        abort_unless(request()->user()->canAccessRecord($activity), 404);
        $leads = request()->user()
            ->constrainToAssignedRecords(Lead::query())
            ->select(['id', 'name', 'company_name'])
            ->orderBy('name')
            ->orderBy('id')
            ->lazy(500);
        $customers = request()->user()
            ->constrainToAssignedRecords(Customer::query())
            ->select(['id', 'name', 'company'])
            ->orderBy('name')
            ->orderBy('id')
            ->lazy(500);
        $assignableUsers = $this->assignableUsers();


        $selectedLead = null;
        $selectedCustomer = null;


        return view('activities.edit', compact(
            'activity',
            'leads',
            'customers',
            'selectedLead',
            'selectedCustomer',
            'assignableUsers'
        ));
    }


    public function update(Request $request, string $id)
    {
        $activity = Activity::findOrFail($id);
        abort_unless($request->user()->canAccessRecord($activity), 404);
        $validated = $this->validateActivity($request);
        $this->authorizeRelatedRecords($request, $validated);
        $this->ensureAssigneeCanAccess($request, $validated, $activity->assigned_to_user_id);
        $activity->update($validated);


        return redirect()
            ->route('activities.index')
            ->with('success', 'Activity updated successfully.');
    }


    public function destroy(string $id)
    {
        $activity = Activity::findOrFail($id);
        abort_unless(request()->user()->canAccessRecord($activity), 404);
        $activity->delete();

        return redirect()
            ->route('activities.index')
            ->with('success', 'Activity deleted successfully.');
    }



    private function validateActivity(Request $request): array
    {
        return $request->validate([
            'lead_id' => [
                'required_without:customer_id',
                'nullable',
                'integer',
                'exists:leads,id',
            ],
            'customer_id' => [
                'required_without:lead_id',
                'nullable',
                'integer',
                'exists:customers,id',
            ],
            'type' => [
                'required',
                Rule::in([
                    'Call',
                    'Meeting',
                    'Email',
                    'Note',
                    'Task'
                ]),
            ],
            'subject' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'activity_date' => [
                'required',
                'date_format:Y-m-d\\TH:i',
            ],
            'status' => [
                'required',
                Rule::in([
                    'pending',
                    'completed'
                ]),
            ],
            'assigned_to_user_id' => $request->user()->hasPermission('records.assign')
                ? ['nullable', 'integer', 'exists:users,id,is_active,1']
                : ['prohibited'],
        ]);
    }

    private function authorizeRelatedRecords(Request $request, array $validated): void
    {
        if (!empty($validated['lead_id'])) {
            abort_unless(
                $request->user()->canAccessRecord(Lead::findOrFail($validated['lead_id'])),
                404
            );
        }

        if (!empty($validated['customer_id'])) {
            abort_unless(
                $request->user()->canAccessRecord(Customer::findOrFail($validated['customer_id'])),
                404
            );
        }
    }

    private function assignableUsers()
    {
        return request()->user()->hasPermission('records.assign')
            ? User::where('is_active', true)
                ->select(['id', 'name'])
                ->orderBy('name')
                ->orderBy('id')
                ->lazy(500)
            : collect();
    }

    private function ensureAssigneeCanAccess(
        Request $request,
        array $validated,
        ?int $currentAssigneeId = null
    ): void {
        if (!$request->user()->hasPermission('records.assign')) {
            return;
        }

        $assignee = User::findOrFail(
            $validated['assigned_to_user_id'] ?? $currentAssigneeId ?? $request->user()->id
        );

        foreach (['lead_id' => Lead::class, 'customer_id' => Customer::class] as $field => $model) {
            if (!empty($validated[$field])) {
                abort_unless(
                    $assignee->canAccessRecord($model::findOrFail($validated[$field])),
                    422,
                    'Assign related records to this user before assigning the activity.'
                );
            }
        }
    }
}