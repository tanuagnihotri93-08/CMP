<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    private const STATUSES = ['new', 'qualified', 'proposal', 'won', 'lost'];

    private const SOURCES = ['Website', 'Referral', 'Social Media', 'Direct'];

    public function index()
    {
        $filters = request()->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
        ]);
        $query = request()->user()->constrainToAssignedRecords(Lead::query());
        $leadCounts = (clone $query)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $search = trim($filters['search'] ?? '');
        $leads = (clone $query)
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($query) use ($term): void {
                    foreach (['name', 'email', 'company_name', 'phone', 'source'] as $column) {
                        $query->orWhere($column, 'like', $term);
                    }
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('leads.index', [
            'leads' => $leads,
            'filters' => $filters,
            'totalLeads' => $leadCounts->sum(),
            'newLeads' => $leadCounts->get('new', 0),
            'qualifiedLeads' => $leadCounts->get('qualified', 0),
            'convertedLeads' => $leadCounts->get('won', 0),
        ]);
    }

    public function create()
    {
        return view('leads.create', [
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateLead($request);

        $validated['assigned_to_user_id'] ??= $request->user()->id;
        try {
            Lead::create($validated);
        } catch (QueryException $exception) {
            if (!$this->isEmailUniqueViolation($exception, 'leads')) {
                throw $exception;
            }

            return back()
                ->withInput()
                ->withErrors(['email' => 'A lead with this email address already exists.']);
        }

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead created successfully.');
    }

    public function show(string $id)
    {
        $lead = Lead::with(['customer', 'activities.customer'])->findOrFail($id);
        abort_unless(request()->user()->canAccessRecord($lead), 404);
        if ($lead->customer && !request()->user()->canAccessRecord($lead->customer)) {
            $lead->setRelation('customer', null);
        }
        $lead->setRelation(
            'activities',
            $lead->activities->filter(fn ($activity) => request()->user()->canAccessRecord($activity))
        );

        $timeline = app(\App\Services\ActivityTimeline::class)
            ->forLead($lead, request()->user());

        return view('leads.show', compact('lead', 'timeline'));
    }

    public function edit(string $id)
    {
        $lead = Lead::findOrFail($id);
        abort_unless(request()->user()->canAccessRecord($lead), 404);

        return view('leads.edit', [
            'lead' => $lead,
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $lead = Lead::findOrFail($id);

        try {
            $lead->update($this->validateLead($request, $lead));
        } catch (QueryException $exception) {
            if (!$this->isEmailUniqueViolation($exception, 'leads')) {
                throw $exception;
            }

            return back()
                ->withInput()
                ->withErrors(['email' => 'A lead with this email address already exists.']);
        }

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead updated successfully.');
    }

    public function destroy(string $id)
    {
        $lead = Lead::findOrFail($id);
        abort_unless(request()->user()->canAccessRecord($lead), 404);
        $lead->delete();

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    private function validateLead(Request $request, ?Lead $lead = null): array
    {
        $request->merge([
            'email' => filled($request->input('email'))
                ? mb_strtolower(trim($request->input('email')))
                : $request->input('email'),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email:rfc',
                'max:255',
                function ($attribute, $value, $fail) use ($lead) {
                    $query = Lead::query()
                        ->whereRaw('LOWER(email) = ?', [mb_strtolower($value)]);

                    if ($lead) {
                        $query->whereKeyNot($lead->getKey());
                    }

                    if ($query->exists()) {
                        $fail('A lead with this email address already exists.');
                    }
                },
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\\+?(?=(?:\\D*\\d){7,15}\\D*$)[0-9][0-9\\s().-]{5,18}[0-9]$/',
            ],
            'source' => ['nullable', Rule::in(self::SOURCES)],
            'status' => ['required', Rule::in(self::STATUSES)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'assigned_to_user_id' => $request->user()->hasPermission('records.assign')
                ? ['nullable', 'integer', 'exists:users,id,is_active,1']
                : ['prohibited'],
        ]);
    }

    private function assignableUsers()
    {
        return request()->user()->hasPermission('records.assign')
            ? User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();
    }
}
