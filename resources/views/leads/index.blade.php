@extends('layouts.app')

@section('content')

<div class="page-wrapper module-page lead-crud-page crm-page leads-index-page">





    <!-- Header -->



    <div class="page-header crm-header">



        <div>



            <h1>

                Leads Management

            </h1>



            <p>

                Track, manage and convert your business opportunities

            </p>



        </div>



        <x-crm.validation-alert title="Please adjust the lead filters and try again." />





        @if(auth()->user()->hasPermission('leads.create'))

            <a href="{{ route('leads.create') }}" class="primary-btn">

                + Add New Lead

            </a>

        @endif





    </div>









    <!-- Stats -->





    <div class="stats-grid">





        <div class="stat-card crm-stat-card">





            <div class="stat-icon">



                <i class="bi bi-people"></i>



            </div>





            <div>



                <span>Total Leads</span>



                <h2>

                    {{ $totalLeads }}

                </h2>



            </div>





        </div>











        <div class="stat-card crm-stat-card">





            <div class="stat-icon">



                <i class="bi bi-person-plus"></i>



            </div>





            <div>



                <span>New Leads</span>



                <h2>

                    {{ $newLeads }}

                </h2>



            </div>





        </div>

        <div class="stat-card crm-stat-card">





            <div class="stat-icon">



                <i class="bi bi-check-circle"></i>



            </div>





            <div>



                <span>Qualified</span>



                <h2>

                    {{ $qualifiedLeads }}

                </h2>



            </div>





        </div>

















        <div class="stat-card crm-stat-card">





            <div class="stat-icon">



                <i class="bi bi-trophy"></i>



            </div>





            <div>



                <span>Converted</span>



                <h2>

                    {{ $convertedLeads }}

                </h2>



            </div>





        </div>







    </div>















    @if(session('success'))



    <div class="alert-success">



        {{ session('success') }}



    </div>



    @endif















    <div class="module-card lead-filter-card">

        <div class="lead-filter-heading">

            <div>

                <h2>Find Leads</h2>

                <small>Search your pipeline or filter by lead status</small>

            </div>

        </div>

        <form class="lead-filter-controls" method="GET" action="{{ route('leads.index') }}" novalidate>

            <label class="lead-search-control">

                <span class="visually-hidden">Search lead records</span>

                <i class="bi bi-search" aria-hidden="true"></i>

                <input type="search" name="search" @class(['crm-invalid-field' => $errors->has('search')]) aria-invalid="{{ $errors->has('search') ? 'true' : 'false' }}" @if($errors->has('search')) aria-describedby="crm-error-search" @endif value="{{ $filters['search'] ?? '' }}" placeholder="Search leads..." autocomplete="off">

                <x-crm.field-error field="search" />

            </label>

            <label>

                <span class="visually-hidden">Filter leads by status</span>

                <select name="status" @class(['crm-invalid-field' => $errors->has('status')]) aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="crm-error-status" @endif>

                    <option value="">All statuses</option>

                    @foreach(['new', 'qualified', 'proposal', 'won', 'lost'] as $status)

                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>

                    @endforeach

                </select>

                <x-crm.field-error field="status" />

            </label>

            <button type="submit" class="primary-btn"><i class="bi bi-search" aria-hidden="true"></i> Search</button>

            @if($filters)

                <a href="{{ route('leads.index') }}" class="module-secondary-btn">Clear</a>

            @endif

        </form>

    </div>



    <!-- Table Card -->





    <div class="table-card">







        <div class="table-top">





            <div>



                <h2>

                    All Leads

                </h2>



                <small>

                    Customer pipeline overview

                </small>



            </div>







        </div>















        <div class="table-responsive">





            <table>





                <thead>



                    <tr>



                        <th>#</th>



                        <th>Lead Information</th>



                        <th>Company</th>



                        <th>Contact</th>



                        <th>Source</th>



                        <th>Status</th>



                        <th>Actions</th>



                    </tr>





                </thead>











                <tbody>





                    @forelse($leads as $lead)





                    <tr class="module-filter-row" data-filter-search="{{ mb_strtolower($lead->name.' '.$lead->email.' '.($lead->company_name ?? '').' '.($lead->phone ?? '').' '.($lead->source ?? 'Direct')) }}" data-filter-status="{{ $lead->status }}">





                        <td>



                            {{ $leads->firstItem() + $loop->index }}



                        </td>









                        <td>





                            <div class="lead-profile">





                                <div class="avatar">



                                    {{ strtoupper(substr($lead->name,0,1)) }}



                                </div>







                                <div>



                                    <h4>

                                        {{ $lead->name }}

                                    </h4>





                                    <p>

                                        {{ $lead->email }}

                                    </p>



                                </div>





                            </div>





                        </td>













                        <td>



                            {{ $lead->company_name ?? '-' }}



                        </td>













                        <td>



                            {{ $lead->phone ?? '-' }}



                        </td>













                        <td>





                            <span class="source">



                                {{ $lead->source ?? 'Direct' }}



                            </span>





                        </td>















                        <td>





                            <span class="status crm-badge {{ $lead->status }}">



                                {{ ucfirst($lead->status) }}



                            </span>





                        </td>















                        <td>





                            <div class="d-flex gap-2">





                                <a href="{{ route('leads.show',$lead->id) }}" class="action-btn view-btn crm-action-btn crm-action-view" aria-label="View {{ $lead->name }}">



                                    <i class="bi bi-eye" aria-hidden="true"></i>



                                </a>









                                @if(auth()->user()->hasPermission('leads.update'))

                                <a href="{{ route('leads.edit',$lead->id) }}" class="action-btn edit-btn crm-action-btn crm-action-edit" aria-label="Edit {{ $lead->name }}">



                                    <i class="bi bi-pencil" aria-hidden="true"></i>



                                </a>

                                @endif













                                @if(auth()->user()->hasPermission('leads.delete'))

                                <form action="{{ route('leads.destroy',$lead->id) }}" method="POST"

                                    data-confirm="This will permanently delete the lead and its linked customer record." novalidate>





                                    @csrf



                                    @method('DELETE')







                                    <button type="submit" class="action-btn delete-btn crm-action-btn crm-action-delete" aria-label="Delete {{ $lead->name }}">





                                        <i class="bi bi-trash" aria-hidden="true"></i>





                                    </button>





                                </form>

                                @endif







                            </div>





                        </td>







                    </tr>







                    @empty





                    <tr>



                        <td class="crm-empty-cell" colspan="7">

                            @include('partials.crm-empty-state', [

                                'icon' => 'bi-person-lines-fill',

                                'title' => $filters ? 'No leads match these filters' : 'No leads found',

                                'description' => $filters

                                    ? 'Adjust the search or status filter to find matching leads.'

                                    : 'Add a lead to start building your pipeline and tracking new opportunities.',

                                'actionUrl' => !$filters && auth()->user()->hasPermission('leads.create') ? route('leads.create') : null,

                                'actionLabel' => !$filters && auth()->user()->hasPermission('leads.create') ? 'Add lead' : null,

                            ])

                        </td>



                    </tr>







                    @endforelse



                    @if($leads->isNotEmpty())

                    <tr class="module-filter-empty" hidden>

                        <td class="crm-empty-cell" colspan="7">

                            @include('partials.crm-empty-state', [

                                'icon' => 'bi-search',

                                'title' => 'No leads match',

                                'description' => 'Try another search term or status filter.',

                            ])

                        </td>

                    </tr>

                    @endif







                </tbody>





            </table>





        </div>



        {{ $leads->links() }}



    </div>





</div>

<style>

.leads-index-page .table-card tbody tr[hidden] {

    display: none !important;

}



.leads-index-page .table-card tbody tr.module-filter-row:not([hidden]):hover {

    background: #f8fafc;

    transform: translateY(-2px);

    box-shadow: 0 8px 20px rgb(15 23 42 / 8%);

}



.page-wrapper {

    width: 100%;

}





/* HEADER */



.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 35px;

}





.page-header h1{



    font-size:32px;

    font-weight:800;

    letter-spacing:-.5px;

    color:#0f172a;



}





.page-header p{



    margin-top:8px;

    font-size:15px;

    color:#64748b;



}







.primary-btn{



    background:linear-gradient(

        135deg,

        #2563eb,

        #4f46e5

    );



    padding:13px 26px;



    border-radius:14px;



    transition:.3s;



}





.primary-btn:hover{



    transform:translateY(-3px);



}





/* STATS */





.stats-grid {



    display: grid;



    grid-template-columns: repeat(4, 1fr);



    gap: 22px;



    margin-bottom: 35px;



}





.stat-card{



    background:white;



    border-radius:20px;



    padding:24px;



    display:flex;



    align-items:center;



    gap:18px;



    border:1px solid #e2e8f0;



    transition:.3s;



}





.stat-card:hover{



    transform:translateY(-6px);



    box-shadow:

    0 20px 40px rgba(109, 29, 185, 0.55);



}





.stat-card span {



    color: #64748b;



    font-size: 14px;



}





.stat-card h2 {



    font-size: 36px;



    margin-top: 12px;



    color: #0f172a;



}











/* ALERT */





.alert-success {



    background: #dcfce7;



    color: #166534;



    padding: 15px;



    border-radius: 12px;



    margin-bottom: 20px;



}











/* TABLE CARD */





.table-card{



    background:white;



    border-radius:22px;



    padding:28px;



    border:1px solid #e2e8f0;



}







.table-top {



    display: flex;



    justify-content: space-between;



    align-items: center;



    margin-bottom: 25px;



}







.table-top h2 {



    color: #0f172a;



}





.table-top small {



    color: #64748b;



}







.search-box{

    border:1px solid #e2e8f0;

    background:#ffffff;

    box-shadow:0 8px 20px rgba(15,23,42,.05);

}



.search-box:focus{

    border-color:#2563eb;

    box-shadow:0 0 0 4px rgba(37,99,235,.12);

}











/* TABLE */





table {



    width: 100%;



    border-collapse: collapse;



}







thead{



    background:#0f172a;



}





thead th{



    color:white;



    font-size:13px;



    text-transform:uppercase;



    letter-spacing:.5px;



}







td {

    padding:20px 18px;

    border-bottom:1px solid #eef2f7;

}







tbody tr{



    transition:.25s;



}





tbody tr:hover{

    background:#f8fafc;

    transform:none;

}











/* LEAD PROFILE */





.lead-profile {



    display: flex;



    align-items: center;



    gap: 12px;



}







.avatar {



    width: 45px;



    height: 45px;



    border-radius: 14px;



    background: linear-gradient(135deg, #2563eb, #7c3aed);



    color: white;



    display: flex;



    align-items: center;



    justify-content: center;



    font-weight: 700;



}











/* STATUS */

.status{

    padding:8px 16px;

    border-radius:50px;

    font-size:12px;

    font-weight:700;

    letter-spacing:.3px;

}





.status.new {



    background: #dbeafe;



    color: #1d4ed8;



}





.status.qualified {



    background: #fef3c7;



    color: #92400e;



}





.status.proposal {



    background: #e0e7ff;



    color: #4338ca;



}





.status.won {



    background: #dcfce7;



    color: #166534;



}











/* SOURCE */





.source {



    background: #f1f5f9;



    padding: 7px 14px;



    border-radius: 20px;



}











/* ACTION BUTTONS */





.action-btn{



    width:38px;



    height:38px;



    border-radius:12px;



    display:flex;



    align-items:center;



    justify-content:center;



    transition:.3s;



}





.action-btn:hover{



    transform:translateY(-3px);



}











.empty {



    text-align: center;



    padding: 40px;



}











@media(max-width:900px) {



    .stats-grid {



        grid-template-columns: 1fr;



    }





    .page-header {



        flex-direction: column;



        gap: 15px;



    }

    /* MAIN BACKGROUND */



.page-wrapper{



    padding:20px;



    background:

    linear-gradient(

        135deg,

        #f8fafc,

        #eef2ff

    );



    min-height:100vh;



}







/* HEADER */



.page-header{



    margin-bottom:40px;



}





.page-header h1{



    font-size:34px;



    font-weight:800;



    letter-spacing:-1px;



    color:#0f172a;



}







.page-header p{



    margin-top:8px;



    color:#64748b;



    font-size:15px;



}







/* ADD BUTTON */



.primary-btn{



    background:

    linear-gradient(

        135deg,

        #2563eb,

        #7c3aed

    );



    padding:14px 28px;



    border-radius:16px;



    color:white;



    font-weight:700;



    box-shadow:



    0 15px 35px

    rgba(37,99,235,.25);



    transition:.35s;



}







.primary-btn:hover{



    transform:translateY(-4px);



    box-shadow:



    0 20px 45px

    rgba(37,99,235,.35);



}









/* STAT CARDS */





.stat-card{



    background:



    rgba(255,255,255,.75);



    backdrop-filter:blur(15px);



    border:



    1px solid

    rgba(255,255,255,.8);





    border-radius:24px;



    padding:28px;





    transition:.35s;





}

}





.stat-card:hover{



    transform:



    translateY(-8px);





    box-shadow:



    0 25px 60px

    rgba(15,23,42,.12);



}







.stat-icon{



    width:55px;



    height:55px;



    border-radius:18px;





    background:



    linear-gradient(

    135deg,

    #eff6ff,

    #dbeafe

    );





    display:flex;



    align-items:center;



    justify-content:center;





    font-size:24px;



    color:#2563eb;





}







/* TABLE */





.table-card{



    margin-top:35px;





    background:



    rgba(255,255,255,.85);





    backdrop-filter:blur(18px);





    border-radius:28px;





    padding:30px;





    border:



    1px solid

    rgba(226,232,240,.8);





    box-shadow:



    0 25px 70px

    rgba(15,23,42,.08);





}







/* TABLE HEADER */





thead{



    background:



    linear-gradient(

    135deg,

    #0f172a,

    #1e293b

    );



}





thead th{



    padding:18px;



    color:white;



    font-size:12px;



    letter-spacing:1px;



    text-transform:uppercase;



}









tbody tr{



    transition:.3s;



}







tbody tr:hover{



    background:#f8fafc;



    transform:scale(1.008);



}
tbody td {
    padding: 18px;
    color: #0f172a;
    font-size: 14px;
    font-weight: 500;
    border-bottom: 1px solid rgba(226, 232, 240, 0.6);
}










/* LEAD AVATAR */





.avatar{



    width:48px;



    height:48px;





    border-radius:16px;





    background:



    linear-gradient(

    135deg,

    #2563eb,

    #9333ea

    );





    color:white;



    font-size:18px;



    box-shadow:



    0 8px 20px

    rgba(37,99,235,.3);



}









/* STATUS */

/* Premium hover only */



.stat-card{



    transition:all .35s ease;



    position:relative;



    overflow:hidden;



}





.stat-card::before{



    content:"";



    position:absolute;



    top:0;

    left:0;



    width:100%;

    height:3px;



    background:linear-gradient(

        90deg,

        #2563eb,

        #7c3aed

    );



    transform:scaleX(0);



    transition:.35s;



}





.stat-card:hover::before{



    transform:scaleX(1);



}







.stat-card:hover{



    transform:translateY(-6px);



    box-shadow:

    0 18px 40px rgba(15,23,42,.12);



}



/* ACTION BUTTON */





.action-btn{



    width:40px;



    height:40px;



    border-radius:14px;



    border:none;



    display:flex;



    align-items:center;



    justify-content:center;





    transition:.3s;



}







.action-btn:hover{



    transform:



    translateY(-4px)

    scale(1.05);



}

.stat-card:nth-child(1) .stat-icon{

    background:linear-gradient(135deg,#dbeafe,#bfdbfe);

    color:#2563eb;

}





.stat-card:nth-child(2) .stat-icon{

    background:linear-gradient(135deg,#ede9fe,#ddd6fe);

    color:#7c3aed;

}





.stat-card:nth-child(3) .stat-icon{

    background:linear-gradient(135deg,#fef3c7,#fde68a);

    color:#d97706;

}





.stat-card:nth-child(4) .stat-icon{

    background:linear-gradient(135deg,#dcfce7,#bbf7d0);

    color:#16a34a;

}

.view-btn{

    background:#dbeafe;

    color:#2563eb;

}





.edit-btn{

    background:#ede9fe;

    color:#7c3aed;

}





.delete-btn{

    background:#fee2e2;

    color:#dc2626;

}



.leads-index-page .primary-btn,

.leads-index-page .action-btn,

.leads-index-page .stat-card,

.leads-index-page .lead-filter-card,

.leads-index-page .lead-filter-controls input,

.leads-index-page .lead-filter-controls select,

.leads-index-page .status {

    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background-color .2s ease;

}



.leads-index-page .primary-btn:hover {

    transform: translateY(-3px);

    box-shadow: 0 18px 36px rgb(37 99 235 / 30%);

}



.leads-index-page .action-btn {

    box-shadow: 0 5px 14px rgb(15 23 42 / 7%);

}



.leads-index-page .action-btn:hover {

    transform: translateY(-3px) scale(1.04);

    box-shadow: 0 11px 22px rgb(15 23 42 / 16%);

}



.leads-index-page .stat-card {

    box-shadow: 0 12px 30px rgb(15 23 42 / 5%);

}



.leads-index-page .stat-card:hover {

    transform: translateY(-4px);

    border-color: #d2deeb;

    box-shadow: 0 18px 38px rgb(15 23 42 / 9%);

}



.leads-index-page .lead-filter-card {

    border-color: rgb(226 232 240 / 88%);

    box-shadow: 0 12px 30px rgb(15 23 42 / 5%);

}



.leads-index-page .lead-filter-card:hover {

    transform: translateY(-3px);

    border-color: #d2deeb;

    box-shadow: 0 18px 38px rgb(15 23 42 / 9%);

}



.leads-index-page .lead-filter-controls input:focus,

.leads-index-page .lead-filter-controls select:focus {

    transform: translateY(-1px);

    border-color: #93b4e9;

    box-shadow: 0 0 0 4px rgb(37 99 235 / 11%), 0 8px 20px rgb(15 23 42 / 5%);

}



.leads-index-page .table-card tbody tr.module-filter-row {

    transition: transform .2s ease, background-color .2s ease, box-shadow .2s ease;

}



.leads-index-page .table-card tbody tr.module-filter-row:hover {

    transform: translateY(-1px);

    background: #f7faff;

    box-shadow: 0 7px 16px rgb(15 23 42 / 7%);

}



.leads-index-page .status {

    border: 1px solid transparent;

}



.leads-index-page .status:hover {

    transform: translateY(-2px);

    border-color: currentColor;

    box-shadow: 0 6px 14px rgb(15 23 42 / 12%);

}



.leads-index-page .primary-btn:focus-visible,

.leads-index-page .action-btn:focus-visible,

.leads-index-page .lead-filter-controls input:focus-visible,

.leads-index-page .lead-filter-controls select:focus-visible {

    outline: 3px solid rgb(37 99 235 / 28%);

    outline-offset: 2px;

}



@media (prefers-reduced-motion: reduce) {

    .leads-index-page .primary-btn,

    .leads-index-page .action-btn,

    .leads-index-page .stat-card,

    .leads-index-page .lead-filter-card,

    .leads-index-page .lead-filter-controls input,

    .leads-index-page .lead-filter-controls select,

    .leads-index-page .table-card tbody tr.module-filter-row,

    .leads-index-page .status {

        transition-duration: .01ms;

    }

}

</style>

@endsection