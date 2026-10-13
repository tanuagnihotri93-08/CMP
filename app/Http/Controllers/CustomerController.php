<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index()
    {
        $filters = request()->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
        $query = request()->user()
            ->constrainToAssignedRecords(Customer::query());
        $customerCounts = (clone $query)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $search = trim($filters['search'] ?? '');

        $customers = (clone $query)
            ->with('lead')
            ->when($search !== '', function ($query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function ($query) use ($term): void {
                    foreach (['name', 'company', 'email', 'phone'] as $column) {
                        $query->orWhere($column, 'like', $term);
                    }

                    if (request()->user()->hasPermission('leads.view')) {
                        $query->orWhereHas('lead', fn ($leadQuery) => $leadQuery->where('name', 'like', $term));
                    }
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
            'filters' => $filters,
            'totalCustomers' => $customerCounts->sum(),
            'activeCustomers' => $customerCounts->get('active', 0),
            'inactiveCustomers' => $customerCounts->get('inactive', 0),
        ]);
    }

    public function create()
    {
        $leads = request()->user()
            ->constrainToAssignedRecords(Lead::query())
            ->where('status', 'won')
            ->select(['id', 'name', 'company_name'])
            ->orderBy('name')
            ->orderBy('id')
            ->lazy(500);
        $lead = null;

        if (request()->filled('lead_id')) {
            $lead = request()->user()
                ->constrainToAssignedRecords(Lead::query())
                ->findOrFail(request('lead_id'));

            if ($lead->customer) {
                return redirect()
                    ->route('customers.show', $lead->customer)
                    ->with('success', 'This lead has already been converted to a customer.');
            }

            abort_unless($lead->status === 'won', 404);
        }

        return view('customers.create', [
            'lead' => $lead,
            'leads' => $leads,
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCustomer($request);

        if (!empty($validated['lead_id'])) {
            $lead = Lead::findOrFail($validated['lead_id']);
            abort_unless($request->user()->canAccessRecord($lead), 404);
            $this->ensureAssigneeCanAccess($request, $validated, $lead);

            if ($lead->status !== 'won') {
                return back()
                    ->withInput()
                    ->withErrors(['lead_id' => 'Only won leads can be converted to customers.']);
            }
        }

        $validated['assigned_to_user_id'] ??= $request->user()->id;
        try {
            $customer = DB::transaction(function () use ($validated) {
                if (empty($validated['lead_id'])) {
                    return Customer::create($validated);
                }

                $lead = Lead::query()
                    ->lockForUpdate()
                    ->findOrFail($validated['lead_id']);

                if ($lead->status !== 'won') {
                    throw ValidationException::withMessages([
                        'lead_id' => 'Only won leads can be converted to customers.',
                    ]);
                }

                return $lead->customer()->first()
                    ?? Customer::create($validated);
            });
        } catch (QueryException $exception) {
            if (!$this->isEmailUniqueViolation($exception, 'customers')) {
                throw $exception;
            }

            return back()
                ->withInput()
                ->withErrors(['email' => 'A customer with this email address already exists.']);
        }

        if (!empty($validated['lead_id']) && !$customer->wasRecentlyCreated) {
            return redirect()
                ->route('customers.show', $customer)
                ->with('success', 'This lead has already been converted to a customer.');
        }

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(string $id)
    {
        $customer = Customer::with(['activities.lead', 'lead.followUps' => function ($query) {
            $query->orderByDesc('date');
        }])->findOrFail($id);

        abort_unless(request()->user()->canAccessRecord($customer), 404);
        if ($customer->lead && !request()->user()->canAccessRecord($customer->lead)) {
            $customer->setRelation('lead', null);
        } elseif ($customer->lead) {
            $customer->lead->setRelation(
                'followUps',
                $customer->lead->followUps->filter(
                    fn ($followUp) => request()->user()->canAccessRecord($followUp)
                )
            );
        }
        $customer->setRelation(
            'activities',
            $customer->activities->filter(fn ($activity) => request()->user()->canAccessRecord($activity))
        );

        $timeline = app(\App\Services\ActivityTimeline::class)
            ->forCustomer($customer, request()->user());

        return view('customers.show', compact('customer', 'timeline'));
    }

    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);
        abort_unless(request()->user()->canAccessRecord($customer), 404);
        $lead = null;

        return view('customers.edit', [
            'customer' => $customer,
            'lead' => $lead,
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);
        $validated = $this->validateCustomer($request, $customer);
        if ($customer->lead) {
            $this->ensureAssigneeCanAccess(
                $request,
                $validated,
                $customer->lead,
                $customer->assigned_to_user_id
            );
        }

        try {
            $customer->update($validated);
        } catch (QueryException $exception) {
            if (!$this->isEmailUniqueViolation($exception, 'customers')) {
                throw $exception;
            }

            return back()
                ->withInput()
                ->withErrors(['email' => 'A customer with this email address already exists.']);
        }

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    private function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        $request->merge([
            'email' => filled($request->input('email'))
                ? mb_strtolower(trim($request->input('email')))
                : $request->input('email'),
        ]);

        return $request->validate([
            'lead_id' => [
                'nullable',
                'integer',
                'exists:leads,id',
                Rule::unique('customers', 'lead_id')->ignore($customer?->id),
            ],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email:rfc',
                'max:255',
                function ($attribute, $value, $fail) use ($customer) {
                    $query = Customer::query()
                        ->whereRaw('LOWER(email) = ?', [mb_strtolower($value)]);

                    if ($customer) {
                        $query->whereKeyNot($customer->getKey());
                    }

                    if ($query->exists()) {
                        $fail('A customer with this email address already exists.');
                    }
                },
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\\+?(?=(?:\\D*\\d){7,15}\\D*$)[0-9][0-9\\s().-]{5,18}[0-9]$/',
            ],
            'address' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
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
    ): void
    {
        if (!$request->user()->hasPermission('records.assign')) {
            return;
        }

        $assignee = User::findOrFail(
            $validated['assigned_to_user_id'] ?? $currentAssigneeId ?? $request->user()->id
        );
        abort_unless($assignee->canAccessRecord($lead), 422, 'Assign the lead to this user before converting it.');
    }

    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);
        abort_unless(request()->user()->canAccessRecord($customer), 404);
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
