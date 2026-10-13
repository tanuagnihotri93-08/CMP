@extends('layouts.app')

@section('content')

<div class="page-wrapper module-page lead-crud-page crm-page">

    <div class="page-header crm-header">

        <div>

            <h1>
                Projects
            </h1>

            <p>
                Manage your CRM projects and team workflow
            </p>

        </div>


        @if(auth()->user()->hasPermission('projects.create'))

        <a href="{{ route('projects.create') }}" class="primary-btn">

            <i class="bi bi-kanban"></i>

            Add Project

        </a>

        @endif


    </div>





    <div class="module-stats crm-stats">


        <div class="module-stat crm-stat-card">

            <span class="module-stat-icon">

                <i class="bi bi-kanban"></i>

            </span>


            <span>
                Total Projects
            </span>


            <strong>
                {{ $projects->count() }}
            </strong>


        </div>




        <div class="module-stat crm-stat-card">

            <span class="module-stat-icon">

                <i class="bi bi-play-circle"></i>

            </span>


            <span>
                Active Projects
            </span>


            <strong>
                {{ $projects->where('status','Active')->count() }}
            </strong>


        </div>





        <div class="module-stat crm-stat-card">

            <span class="module-stat-icon">

                <i class="bi bi-list-task"></i>

            </span>


            <span>
                Total Tasks
            </span>


            <strong>
                {{ $projects->sum('tasks_count') }}
            </strong>


        </div>


    </div>






    <div class="module-card enterprise-table-card crm-card crm-table-card">


        <div class="enterprise-table-heading">


            <div>

                <h2>
                    Project Directory
                </h2>


                <small>
                    Manage projects and assigned tasks
                </small>


            </div>


        </div>





        <div class="module-table-wrap">


            <table class="module-table">


                <thead>

                    <tr>

                        <th>
                            Project
                        </th>


                        <th>
                            Client
                        </th>


                        <th>
                            Status
                        </th>


                        <th>
                            Tasks
                        </th>


                        <th>
                            Actions
                        </th>


                    </tr>

                </thead>




                <tbody>


                    @forelse($projects as $project)


                    <tr>


                        <td>

                            <strong>
                                {{ $project->name }}
                            </strong>


                            <br>


                            <small>
                                {{ $project->description ?? 'No description' }}
                            </small>


                        </td>



                        <td>

                            {{ $project->client_name ?? '—' }}

                        </td>




                        <td>

                            <span class="module-badge crm-badge">

                                {{ ucfirst($project->status) }}

                            </span>

                        </td>




                        <td>

                            {{ $project->tasks_count }}

                        </td>




                        <td class="module-actions">

                            <div style="display:flex; gap:8px; align-items:center;">

                                <a href="{{ route('projects.show',$project) }}"
                                    class="module-action-btn crm-action-btn crm-action-view">

                                    <i class="bi bi-eye"></i>
                                    View

                                </a>


                                @if(auth()->user()->hasPermission('projects.update'))

                                <a href="{{ route('projects.edit',$project) }}"
                                    class="module-action-btn crm-action-btn crm-action-edit">

                                    <i class="bi bi-pencil"></i>
                                    Edit

                                </a>

                                @endif



                                @if(auth()->user()->hasPermission('projects.delete'))

                                <form action="{{ route('projects.destroy',$project) }}" method="POST">

                                    @csrf
                                    @method('DELETE')


                                    <button type="submit" class="module-action-btn crm-action-btn crm-action-delete">

                                        <i class="bi bi-trash"></i>
                                        Delete

                                    </button>


                                </form>

                                @endif


                            </div>

                        </td>



                    </tr>



                    @empty


                    <tr>


                        <td colspan="5" class="crm-empty-cell">


                            No Projects Found


                        </td>


                    </tr>



                    @endforelse



                </tbody>



            </table>



        </div>



    </div>



</div>


@endsection