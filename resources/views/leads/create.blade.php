@extends('layouts.app')


@section('content')


<div class="page-wrapper crm-page">



    <div class="page-header crm-header">


        <div>

            <h1>
                Add New Lead
            </h1>


            <p>
                Create and manage new business opportunities
            </p>


        </div>




        <a href="{{ route('leads.index') }}" class="secondary-btn">

            ← Back To Leads

        </a>


    </div>







    <div class="form-card crm-card crm-form-card">



        <form action="{{ route('leads.store') }}" method="POST">


            @csrf

            <x-crm.validation-alert />



            <div class="form-grid">





                <div class="form-group">


                    <label class="form-label">
                        Name <span>*</span>
                    </label>


                    <input
                    type="text"
                    id="name"
                    name="name"
                    required
                    maxlength="255"
                    @class(['form-control', 'crm-invalid-field' => $errors->has('name')])
                    aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                    @if($errors->has('name')) aria-describedby="crm-error-name" @endif
                    placeholder="Enter lead name"
                    value="{{ old('name') }}">

                    <x-crm.field-error field="name" />

                </div>









                <div class="form-group">


                    <label class="form-label">
                        Company Name <span>*</span>
                    </label>


                    <input
                    type="text"
                    id="company_name"
                    name="company_name"
                    required
                    maxlength="255"
                    @class(['form-control', 'crm-invalid-field' => $errors->has('company_name')])
                    aria-invalid="{{ $errors->has('company_name') ? 'true' : 'false' }}"
                    @if($errors->has('company_name')) aria-describedby="crm-error-company-name" @endif
                    placeholder="Enter company name"
                    value="{{ old('company_name') }}">

                    <x-crm.field-error field="company_name" id="crm-error-company-name" />


                </div>









                <div class="form-group">


                    <label class="form-label">
                        Email <span>*</span>
                    </label>


                    <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    maxlength="255"
                    @class(['form-control', 'crm-invalid-field' => $errors->has('email')])
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    @if($errors->has('email')) aria-describedby="crm-error-email" @endif
                    placeholder="Enter email address"
                    value="{{ old('email') }}">

                    <x-crm.field-error field="email" />

                </div>









                <div class="form-group">


                    <label class="form-label">
                        Phone <span>*</span>
                    </label>


                    <input
                    type="text"
                    id="phone"
                    name="phone"
                    required
                    maxlength="20"
                    @class(['form-control', 'crm-invalid-field' => $errors->has('phone')])
                    aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                    @if($errors->has('phone')) aria-describedby="crm-error-phone" @endif
                    placeholder="Enter phone number"
                    value="{{ old('phone') }}">

                    <x-crm.field-error field="phone" />


                </div>









                <div class="form-group">


                    <label class="form-label">
                        Lead Source <span>*</span>
                    </label>


                    <select id="source" name="source" required @class(['form-select', 'crm-invalid-field' => $errors->has('source')]) aria-invalid="{{ $errors->has('source') ? 'true' : 'false' }}" @if($errors->has('source')) aria-describedby="crm-error-source" @endif>


                        <option value="">
                            Select Source
                        </option>


                        <option value="Website" @selected(old('source') === 'Website')>
                            Website
                        </option>


                        <option value="Referral" @selected(old('source') === 'Referral')>
                            Referral
                        </option>


                        <option value="Social Media" @selected(old('source') === 'Social Media')>
                            Social Media
                        </option>


                        <option value="Direct" @selected(old('source') === 'Direct')>
                            Direct
                        </option>


                    </select>

                    <x-crm.field-error field="source" />


                </div>









                <div class="form-group">


                    <label class="form-label">
                        Status <span>*</span>
                    </label>


                    <select id="status" name="status" required @class(['form-select', 'crm-invalid-field' => $errors->has('status')]) aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="crm-error-status" @endif>


                        <option value="new" @selected(old('status', 'new') === 'new')>
                            New
                        </option>


                        <option value="qualified" @selected(old('status') === 'qualified')>
                            Qualified
                        </option>


                        <option value="proposal" @selected(old('status') === 'proposal')>
                            Proposal
                        </option>


                        <option value="won" @selected(old('status') === 'won')>
                            Won
                        </option>

                        <option value="lost" @selected(old('status') === 'lost')>
                            Lost
                        </option>



                    </select>

                    <x-crm.field-error field="status" />


                </div>

                <div class="form-group">
                    <label class="form-label" for="city">City</label>
                    <input type="text" id="city" name="city"
                        @class(['form-control', 'crm-invalid-field' => $errors->has('city')])
                        aria-invalid="{{ $errors->has('city') ? 'true' : 'false' }}"
                        @if($errors->has('city')) aria-describedby="crm-error-city" @endif
                        maxlength="100" placeholder="Enter city" value="{{ old('city') }}">
                    <x-crm.field-error field="city" />
                </div>



                @if($assignableUsers->isNotEmpty())
                <div class="form-group">
                    <label class="form-label">Assigned to</label>
                    <select id="assigned_to_user_id" name="assigned_to_user_id" @class(['form-select', 'crm-invalid-field' => $errors->has('assigned_to_user_id')]) aria-invalid="{{ $errors->has('assigned_to_user_id') ? 'true' : 'false' }}" @if($errors->has('assigned_to_user_id')) aria-describedby="crm-error-assigned-to-user-id" @endif>
                        @foreach($assignableUsers as $assignee)
                            <option value="{{ $assignee->id }}" @selected((string) old('assigned_to_user_id', auth()->id()) === (string) $assignee->id)>{{ $assignee->name }}</option>
                        @endforeach
                    </select>
                    <x-crm.field-error field="assigned_to_user_id" id="crm-error-assigned-to-user-id" />
                </div>
                @endif



            </div>









            <div class="form-group full-width">


                <label class="form-label">
                    Notes
                </label>



                <textarea
                id="notes"
                name="notes"
                @class(['form-textarea', 'crm-invalid-field' => $errors->has('notes')])
                aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}"
                @if($errors->has('notes')) aria-describedby="crm-error-notes" @endif
                rows="5"
                placeholder="Add notes about this lead">{{ old('notes') }}</textarea>

                <x-crm.field-error field="notes" />


            </div>

            <div class="form-group full-width">
                <label class="form-label" for="remarks">Remarks</label>
                <textarea id="remarks" name="remarks"
                    @class(['form-textarea', 'crm-invalid-field' => $errors->has('remarks')])
                    aria-invalid="{{ $errors->has('remarks') ? 'true' : 'false' }}"
                    @if($errors->has('remarks')) aria-describedby="crm-error-remarks" @endif
                    maxlength="1000" rows="4" placeholder="Add remarks">{{ old('remarks') }}</textarea>
                <x-crm.field-error field="remarks" />
            </div>









            <div class="form-actions">



                <a href="{{ route('leads.index') }}" class="secondary-btn">

                    Cancel

                </a>




                <button type="submit" class="primary-btn">

                    Save Lead

                </button>



            </div>





        </form>



    </div>





</div>

<style>

/* =====================================
   CRM PRO - LEAD FORM PREMIUM STYLE
===================================== */


/* PAGE */

.page-wrapper{

    width:100%;

    padding:25px;

}





/* HEADER */

.page-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:35px;

}



.page-header h1{

    font-size:36px;

    font-weight:800;

    letter-spacing:-1px;

    color:#0f172a;

    margin:0;

}



.page-header p{

    margin-top:8px;

    font-size:15px;

    color:#64748b;

}






/* FORM CARD */


.form-card{


    background:#ffffff;


    border-radius:26px;


    padding:38px;


    border:1px solid #e2e8f0;


    box-shadow:

    0 25px 60px

    rgba(15,23,42,.08);


    position:relative;


    overflow:hidden;


}




.form-card::before{


    content:"";


    position:absolute;


    top:0;


    left:0;


    height:4px;


    width:100%;


    background:

    linear-gradient(
        90deg,
        #2563eb,
        #7c3aed
    );


}






/* FORM GRID */


.form-grid{


    display:grid;


    grid-template-columns:repeat(2,1fr);


    gap:26px;


}






/* FORM GROUP */


.form-group{


    margin-bottom:24px;


}






/* LABEL */


.form-label{


    display:block;


    margin-bottom:10px;


    font-size:13px;


    font-weight:700;


    color:#334155;


    text-transform:uppercase;


    letter-spacing:.5px;


}



.form-label span{


    color:#ef4444;


}








/* INPUTS */


.form-control,
.form-select,
.form-textarea{


    width:100%;


    background:#f8fafc;


    border:1px solid #e2e8f0;


    border-radius:15px;


    padding:15px 17px;


    font-size:14px;


    color:#0f172a;


    outline:none;


    transition:.3s;


}




.form-control:hover,
.form-select:hover,
.form-textarea:hover{


    border-color:#cbd5e1;


}






.form-control:focus,
.form-select:focus,
.form-textarea:focus{


    background:#ffffff;


    border-color:#2563eb;


    box-shadow:

    0 0 0 4px

    rgba(37,99,235,.12);


}






.form-textarea{


    min-height:130px;


    resize:none;


}







/* ACTION AREA */


.form-actions{


    display:flex;


    justify-content:flex-end;


    gap:15px;


    padding-top:25px;


    margin-top:20px;


    border-top:1px solid #e5e7eb;


}






/* PRIMARY BUTTON */


.primary-btn{


    background:


    linear-gradient(
        135deg,
        #2563eb,
        #4f46e5
    );


    color:white;


    padding:14px 32px;


    border-radius:15px;


    border:none;


    font-weight:700;


    text-decoration:none;


    cursor:pointer;


    transition:.35s;


    box-shadow:

    0 15px 35px

    rgba(37,99,235,.25);


}



.primary-btn:hover{


    transform:translateY(-3px);


    box-shadow:

    0 20px 45px

    rgba(37,99,235,.35);


    color:white;


}







/* SECONDARY BUTTON */


.secondary-btn{


    background:#ffffff;


    color:#334155;


    padding:14px 28px;


    border-radius:15px;


    border:1px solid #e2e8f0;


    font-weight:700;


    text-decoration:none;


    transition:.3s;


}





.secondary-btn:hover{


    background:#f8fafc;


    transform:translateY(-3px);


    color:#0f172a;


}








/* ERROR */


.error-text{


    display:block;


    margin-top:7px;


    color:#dc2626;


    font-size:13px;


}







/* RESPONSIVE */


@media(max-width:900px){


    .form-grid{


        grid-template-columns:1fr;


    }



    .page-header{


        flex-direction:column;


        align-items:flex-start;


        gap:15px;


    }



    .form-actions{


        justify-content:flex-start;


    }


}



</style>

@endsection
