<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FollowUpController extends Controller
{
    public function index()
    {
        $filters = request()->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['scheduled', 'completed'])],
        ]);
        $query = request()->user()->constrainToAssignedRecords(FollowUp::query());
        $followUpCounts = (clone $query)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $overdueCount = (clone $query)
            ->where('status', 'scheduled')
            ->where('date', '<', now())
            ->count();
        $search = trim($filters['search'] ?? '');
        $followUps = (clone $query)
            ->with('lead')
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($query) use ($term): void {
                    $query->where('type', 'like', $term)
                        ->orWhere('remark', 'like', $term);

                    if (request()->user()->hasPermission('leads.view')) {
                        $query->orWhereHas('lead', fn ($leadQuery) => $leadQuery->where('name', 'like', $term));
                    }
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('date')
            ->orderBy('id')
            ->paginate(25)
            ->withQueryString();
        $followUps->each(function (FollowUp $followUp): void {
            if ($followUp->lead && !request()->user()->canAccessRecord($followUp->lead)) {
                $followUp->setRelation('lead', null);
            }
        });

        return view('followups.index', compact(
            'followUps',
            'filters',
            'overdueCount'
        ) + [
            'scheduledCount' => $followUpCounts->get('scheduled', 0),
            'completedCount' => $followUpCounts->get('completed', 0),
        ]);
    }

    public function create()
    {
        $leads = request()->user()
            ->constrainToAssignedRecords(Lead::query())
            ->select(['id', 'name', 'company_name'])
            ->orderBy('name')
            ->orderBy('id')
            ->lazy(500);
        $lead = request()->filled('lead_id')
            ? request()->user()->constrainToAssignedRecords(Lead::query())->findOrFail(request('lead_id'))
            : null;

        return view('followups.create', [
            'leads' => $leads,
            'lead' => $lead,
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateFollowUp($request);
        $lead = Lead::findOrFail($validated['lead_id']);
        abort_unless($request->user()->canAccessRecord($lead), 404);
        $this->ensureAssigneeCanAccess($request, $validated, $lead);
        $validated['assigned_to_user_id'] ??= $request->user()->id;
        FollowUp::create($validated);

        return redirect()
            ->route('followups.index')
            ->with('success', 'Follow up scheduled successfully.');
    }

    public function show(string $id)
    {
        $followUp = FollowUp::with('lead')->findOrFail($id);
        abort_unless(request()->user()->canAccessRecord($followUp), 404);
        if ($followUp->lead && !request()->user()->canAccessRecord($followUp->lead)) {
            $followUp->setRelation('lead', null);
        }

        return view('followups.show', compact('followUp'));
    }

    public function edit(string $id)
    {
        $followUp = FollowUp::findOrFail($id);
        abort_unless(request()->user()->canAccessRecord($followUp), 404);
        $leads = request()->user()
            ->constrainToAssignedRecords(Lead::query())
            ->select(['id', 'name', 'company_name'])
            ->orderBy('name')
            ->orderBy('id')
            ->lazy(500);
        $lead = null;

        return view('followups.edit', [
            'followUp' => $followUp,
            'leads' => $leads,
            'lead' => $lead,
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $followUp = FollowUp::findOrFail($id);
        abort_unless($request->user()->canAccessRecord($followUp), 404);
        $validated = $this->validateFollowUp($request);
        $lead = Lead::findOrFail($validated['lead_id']);
        abort_unless($request->user()->canAccessRecord($lead), 404);
        $this->ensureAssigneeCanAccess($request, $validated, $lead, $followUp->assigned_to_user_id);
        $followUp->update($validated);

        return redirect()
            ->route('followups.show', $followUp)
            ->with('success', 'Follow up updated successfully.');
    }

    public function destroy(string $id)
    {
        $followUp = FollowUp::findOrFail($id);
        abort_unless(request()->user()->canAccessRecord($followUp), 404);
        $followUp->delete();

        return redirect()
            ->route('followups.index')
            ->with('success', 'Follow up deleted successfully.');
    }

    public function complete(string $followUp)
    {
        $followUp = FollowUp::findOrFail($followUp);
        abort_unless(request()->user()->canAccessRecord($followUp), 404);
        $followUp->update(['status' => 'completed']);

        return redirect()
            ->route('followups.show', $followUp)
            ->with('success', 'Follow up marked as completed.');
    }

    private function validateFollowUp(Request $request): array
    {
        return $request->validate([
            'lead_id' => ['required', 'integer', 'exists:leads,id'],
            'date' => ['required', 'date_format:Y-m-d\\TH:i'],
            'type' => ['required', Rule::in(['Call', 'Meeting', 'Email', 'Demo'])],
            'remark' => 'nullable|string|max:2000',
            'status' => ['required', Rule::in(['scheduled', 'completed'])],
            'assigned_to_user_id' => $request->user()->hasPermission('records.assign')
                ? ['nullable', 'integer', 'exists:users,id,is_active,1']
                : ['prohibited'],
        ]);
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
        Lead $lead,
        ?int $currentAssigneeId = null
    ): void {
        if (!$request->user()->hasPermission('records.assign')) {
            return;
        }

        $assigneeId = $validated['assigned_to_user_id'] ?? $currentAssigneeId ?? $request->user()->id;
        abort_unless(
            User::findOrFail($assigneeId)->canAccessRecord($lead),
            422,
            'Assign the lead to this user before assigning the follow up.'
        );
    }
}
