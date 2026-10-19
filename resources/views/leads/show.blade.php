@extends('layouts.app')

@section('content')

<div class="page-wrapper crm-page">

    <div class="page-header crm-header">

        <div>
            <h1>
                Lead Details
            </h1>

            <p>
                View complete lead information and manage pipeline
            </p>
        </div>

        <a href="{{ route('leads.index') }}" class="lead-back-btn">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Back to leads
        </a>

    </div>


    <div class="lead-details-card crm-card crm-profile-card">

        @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
        @endif


        <div class="lead-profile-header">

            <div class="big-avatar">
                {{ strtoupper(substr($lead->name,0,1)) }}
            </div>


            <div class="profile-info">

                <h2>
                    {{ $lead->name }}
                </h2>

                <p>
                    {{ $lead->company_name ?? 'No Company' }}
                </p>

            </div>


            <div>
                <span class="status crm-badge {{ $lead->status }}">
                    {{ ucfirst($lead->status) }}
                </span>
            </div>

        </div>



        <div class="details-grid">


            <div class="detail-item">

                <label>
                    Company
                </label>

                <p>
                    {{ $lead->company_name ?? '-' }}
                </p>

            </div>


            <div class="detail-item">

                <label>
                    Email
                </label>

                <p>
                    {{ $lead->email ?? '-' }}
                </p>

            </div>

            <div class="detail-item">
                <label>City</label>
                <p>{{ $lead->city ?? '-' }}</p>
            </div>


            <div class="detail-item">

                <label>
                    Phone
                </label>

                <p>
                    {{ $lead->phone ?? '-' }}
                </p>

            </div>


            <div class="detail-item">

                <label>
                    Source
                </label>

                <p>
                    {{ $lead->source ?? '-' }}
                </p>

            </div>


            <div class="detail-item">

                <label>
                    Status
                </label>

                <p>
                    {{ ucfirst($lead->status) }}
                </p>

            </div>


        </div>



        <div class="notes-card">

            <h3>
                Notes
            </h3>

            <p>
                {{ $lead->notes ?? 'No notes available' }}
            </p>

        </div>

        <div class="notes-card">
            <h3>Remarks</h3>
            <p>{{ $lead->remarks ?? 'No remarks available' }}</p>
        </div>



        <div class="module-form-actions">


            @if($lead->customer && auth()->user()->hasPermission('customers.view'))

            <a class="primary-btn" href="{{ route('customers.show', $lead->customer) }}">
                <i class="bi bi-person-vcard" aria-hidden="true"></i>
                View Customer
            </a>


            @elseif($lead->status === 'won' && auth()->user()->hasPermission('customers.create'))

            <a class="primary-btn" href="{{ route('customers.create', ['lead_id' => $lead->id]) }}">
                <i class="bi bi-person-check" aria-hidden="true"></i>
                Convert to Customer
            </a>

            @endif



            @if(auth()->user()->hasPermission('follow_ups.create'))
                <a class="lead-action-btn" href="{{ route('followups.create', ['lead_id' => $lead->id]) }}">
                    <i class="bi bi-calendar-plus" aria-hidden="true"></i>
                    Schedule Follow Up
                </a>
            @endif


            @if(auth()->user()->hasPermission('leads.update'))
                <a class="lead-action-btn" href="{{ route('leads.edit',$lead->id) }}">
                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                    Edit Lead
                </a>
            @endif

            @if(auth()->user()->hasPermission('activities.create'))
                <a class="lead-action-btn" href="{{ route('activities.create', ['lead_id' => $lead->id]) }}">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i>
                    Add Activity
                </a>
            @endif


        </div>


    </div>

    <x-crm.activity-timeline :events="$timeline" />

    <div class="module-card crm-card crm-table-card">
        <div class="module-card-heading">
            <h2>Activities</h2>
            @if(auth()->user()->hasPermission('activities.view'))
                <a class="module-secondary-btn" href="{{ route('activities.index') }}">All Activities</a>
            @endif
        </div>
        <div class="module-table-wrap">
            <table class="module-table">
                <thead><tr><th>Date</th><th>Type</th><th>Subject</th><th>Customer</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>{{ $activity->activity_date?->format('M j, Y g:i A') ?? 'Date not set' }}</td>
                            <td>{{ $activity->type }}</td>
                            <td>{{ $activity->subject }}</td>
                            <td>
                                @if($activity->customer && auth()->user()->hasPermission('customers.view'))
                                    <a href="{{ route('customers.show', $activity->customer) }}">{{ $activity->customer->name }}</a>
                                @elseif($activity->customer)
                                    Linked customer
                                @else
                                    —
                                @endif
                            </td>
                            <td><span class="module-badge crm-badge {{ $activity->status }}">{{ ucfirst($activity->status) }}</span></td>
                            <td>
                                @if(auth()->user()->hasPermission('activities.view'))
                                    <a href="{{ route('activities.show', $activity) }}">View</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="module-empty">No activities recorded for this lead.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $activities->links() }}
    </div>


</div>

<style>
/* =====================================
   CRM PRO - LEAD DETAILS PREMIUM
===================================== */


/* MAIN CARD */

.lead-details-card {

    background: #ffffff;

    border-radius: 24px;

    padding: 35px;

    border: 1px solid #e5e7eb;

    box-shadow:
        0 18px 45px rgba(15, 23, 42, 0.08);

}



/* PROFILE SECTION */

.lead-profile-header {

    display: flex;

    align-items: center;

    gap: 22px;

    padding-bottom: 30px;

    border-bottom: 1px solid #e5e7eb;

}



.big-avatar {

    width: 80px;

    height: 80px;

    border-radius: 22px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        linear-gradient(135deg, #2563eb, #4f46e5);

    color: white;

    font-size: 32px;

    font-weight: 800;

    box-shadow:
        0 12px 25px rgba(37, 99, 235, .25);

}



.profile-info {

    flex: 1;

}



.profile-info h2 {

    margin: 0;

    font-size: 28px;

    font-weight: 800;

    color: #0f172a;

}



.profile-info p {

    margin-top: 6px;

    color: #64748b;

    font-size: 15px;

}





/* STATUS BADGE */

.status {

    padding: 8px 18px;

    border-radius: 50px;

    background: #eff6ff;

    color: #2563eb;

    font-size: 13px;

    font-weight: 700;

}





/* DETAILS GRID */

.details-grid {

    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 20px;

    margin-top: 30px;

}



.detail-item {

    padding: 20px;

    background: #f8fafc;

    border-radius: 18px;

    border: 1px solid #e2e8f0;

    transition: .3s;

}



.detail-item:hover {

    transform: translateY(-3px);

    box-shadow:
        0 12px 25px rgba(15, 23, 42, .08);

}



.detail-item label {

    display: block;

    font-size: 12px;

    font-weight: 700;

    color: #64748b;

    text-transform: uppercase;

    margin-bottom: 8px;

}



.detail-item p {

    margin: 0;

    font-size: 15px;

    font-weight: 600;

    color: #0f172a;

}





/* NOTES */

.notes-card {

    margin-top: 30px;

    padding: 25px;

    background: #f8fafc;

    border-radius: 20px;

    border: 1px solid #e2e8f0;

}



.notes-card h3 {

    margin: 0 0 12px;

    font-size: 18px;

    font-weight: 800;

    color: #0f172a;

}



.notes-card p {

    margin: 0;

    color: #475569;

    line-height: 1.7;

}





/* ACTION AREA */

.module-form-actions {

    display: flex;

    justify-content: flex-end;

    align-items: center;

    flex-wrap: wrap;

    gap: 15px;

    margin-top: 30px;

    padding-top: 25px;

    border-top: 1px solid #e5e7eb;

}


/* LEAD DETAILS ACTION BUTTONS */
.lead-action-btn{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    min-height:48px;

    padding:0 22px;

    border-radius:14px;

    background:#0f172a;

    border:1px solid #1e293be4;

    color:#ffffff;

    text-decoration:none;

    font-weight:700;

    line-height:1.2;

    white-space:nowrap;

    transition:.3s;

    box-shadow:
    0 10px 25px rgba(10, 10, 10, 0.55);

}

.lead-details-card .module-form-actions > .primary-btn {
    display:inline-flex;
    min-height:48px;
    align-items:center;
    justify-content:center;
    padding:0 22px;
    border:1px solid transparent;
    border-radius:14px;
    background:linear-gradient(135deg, #2563eb, #7c3aed) !important;
    box-shadow:0 10px 24px rgba(37, 99, 235, .24);
    color:#ffffff !important;
    font-size:15px;
    font-weight:700;
    line-height:1.2;
    text-decoration:none;
    white-space:nowrap;
    transition:transform .2s ease, box-shadow .2s ease, filter .2s ease;
}

.lead-details-card .module-form-actions > .primary-btn:hover {
    filter:brightness(1.08);
    transform:translateY(-2px);
    box-shadow:0 14px 28px rgba(37, 99, 235, .3);
}

.lead-details-card .module-form-actions > .lead-action-btn {
    color:#ffffff !important;
}


.lead-action-btn:hover{

    background:#2563eb;

    border-color:#2563eb;

    color:#ffffff !important;

    transform:translateY(-2px);

}

/* BACK BUTTON */

.lead-back-btn{

    display:inline-flex;

    align-items:center;

    gap:8px;

    padding:12px 22px;

    border-radius:14px;

    background:#061930;

    border:1px solid #061930;

    color:#ffffff;

    text-decoration:none;

    font-weight:700;

    transition:.3s;

}


.lead-back-btn:hover{

    background:#f1f5f9;

    color:#0f172a;

    transform:translateY(-2px);

}
/* MOBILE */

@media(max-width:900px) {


    .details-grid {

        grid-template-columns: 1fr;

    }


    .lead-profile-header {

        flex-direction: column;

        align-items: flex-start;

    }


    .module-form-actions {

        flex-direction: column;

        align-items: stretch;

    }


}
</style>
@endsection
