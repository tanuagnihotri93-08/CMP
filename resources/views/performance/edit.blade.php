@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="mb-4">

        <h2>
            Edit Project
        </h2>

        <p class="text-muted">
            Update CRM project details
        </p>

    </div>


    <div class="card">

        <div class="card-body">


            <form action="{{ route('projects.update', $project) }}" method="POST">

                @csrf
                @method('PUT')


                <div class="mb-3">

                    <label class="form-label">
                        Project Name
                    </label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name', $project->name) }}"
                           required>

                    @error('name')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>



                <div class="mb-3">

                    <label class="form-label">
                        Description
                    </label>

                    <textarea name="description"
                              class="form-control"
                              rows="4">{{ old('description', $project->description) }}</textarea>

                    @error('description')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>



                <div class="mb-3">

                    <label class="form-label">
                        Client Name
                    </label>

                    <input type="text"
                           name="client_name"
                           class="form-control"
                           value="{{ old('client_name', $project->client_name) }}">

                    @error('client_name')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>




                <div class="row">


                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Start Date
                        </label>

                        <input type="date"
                               name="start_date"
                               class="form-control"
                               value="{{ old('start_date', $project->start_date) }}">

                        @error('start_date')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>




                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            End Date
                        </label>

                        <input type="date"
                               name="end_date"
                               class="form-control"
                               value="{{ old('end_date', $project->end_date) }}">

                        @error('end_date')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>


                </div>





                <div class="mb-3">

                    <label class="form-label">
                        Status
                    </label>


                    <select name="status"
                            class="form-control"
                            required>


                        <option value="Planning"
                            {{ old('status', $project->status) == 'Planning' ? 'selected' : '' }}>
                            Planning
                        </option>


                        <option value="Active"
                            {{ old('status', $project->status) == 'Active' ? 'selected' : '' }}>
                            Active
                        </option>


                        <option value="Completed"
                            {{ old('status', $project->status) == 'Completed' ? 'selected' : '' }}>
                            Completed
                        </option>


                        <option value="Cancelled"
                            {{ old('status', $project->status) == 'Cancelled' ? 'selected' : '' }}>
                            Cancelled
                        </option>


                    </select>


                    @error('status')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror


                </div>




                <button type="submit"
                        class="btn btn-primary">

                    Update Project

                </button>



                <a href="{{ route('projects.show', $project) }}"
                   class="btn btn-secondary">

                    Cancel

                </a>



            </form>


        </div>

    </div>


</div>
<style>

/* ================================
   PROJECT EDIT CRM PRO DESIGN
================================ */


.project-edit-page .form-card{

    position:relative;
    overflow:hidden;

}


.project-edit-page .form-card::before{

    content:"";

    position:absolute;

    top:0;
    left:0;

    width:100%;
    height:4px;

    background:linear-gradient(
        90deg,
        #2563eb,
        #7c3aed
    );

}




.project-edit-page .form-control,
.project-edit-page .form-select,
.project-edit-page .form-textarea{


    background:#f8fafc;

    border:1px solid #e2e8f0;

    border-radius:14px;

    padding:15px 16px;

    color:#0f172a;

    transition:.3s;

}




.project-edit-page .form-control:focus,
.project-edit-page .form-select:focus,
.project-edit-page .form-textarea:focus{


    background:white;

    border-color:#2563eb;


    box-shadow:

    0 0 0 4px
    rgba(37,99,235,.12);


}




.project-edit-page textarea{

    min-height:140px;

    resize:none;

}




.project-edit-page .form-label{

    font-size:13px;

    font-weight:700;

    color:#334155;

    text-transform:uppercase;

    letter-spacing:.4px;

}




.project-edit-page .primary-btn{

    min-width:160px;

}




.project-edit-page .secondary-btn{

    min-width:130px;

    text-align:center;

}




@media(max-width:900px){

    .project-edit-page .form-grid{

        grid-template-columns:1fr;

    }


}


</style>
@endsection