@extends('admin.master', ['menu' => 'recipe'])
@section('title', isset($title) ? $title : '')
@section('content')
    <div id="table-url" data-url="{{ route('admin.recipe.index') }}"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="breadcrumb__content">
                <div class="breadcrumb__content__left">
                    <div class="breadcrumb__title">
                        <h2>{{__('Recipes')}}</h2>
                    </div>
                </div>
                <div class="breadcrumb__content__right">
                    <nav aria-label="breadcrumb">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{__('Home')}}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{__('Recipes')}}</li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="customers__area bg-style mb-30">
                <div class="item-title d-flex justify-content-between">
                    <h2>{{__('Recipe List')}}</h2>
                    <a href="{{route('admin.recipe.create')}}" class="btn btn-success btn-sm"> <i class="fa fa-plus"></i> {{__('Add Recipe')}} </a>
                </div>
                <div class="customers__table">
                    <table id="RecipeTable" class="row-border data-table-filter table-style">
                        <thead>
                        <tr>
                            <th>{{__('Image')}}</th>
                            <th>{{__('Title')}}</th>
                            <th>{{__('Time to Cook')}}</th>
                            <th>{{__('Difficulty')}}</th>
                            <th>{{__('Status')}}</th>
                            <th>{{__('Action')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($recipes as $recipe)
                            <tr>
                                <td>
                                    @if($recipe->image)
                                        <img src="{{asset('uploaded_files/recipes/'.$recipe->image)}}" alt="recipe" width="50" height="50">
                                    @else
                                        <img src="{{asset('assets/images/placeholder.png')}}" alt="recipe" width="50" height="50">
                                    @endif
                                </td>
                                <td>{{$recipe->en_title}}</td>
                                <td>{{$recipe->time_to_cook}} {{__('mins')}}</td>
                                <td>
                                    @if($recipe->difficulty == 'easy')
                                        <span class="badge badge-success">{{__('Easy')}}</span>
                                    @elseif($recipe->difficulty == 'medium')
                                        <span class="badge badge-warning">{{__('Medium')}}</span>
                                    @else
                                        <span class="badge badge-danger">{{__('Hard')}}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($recipe->status == 1)
                                        <span class="badge badge-success">{{__('Active')}}</span>
                                    @else
                                        <span class="badge badge-danger">{{__('Inactive')}}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action__buttons">
                                        <a href="{{route('admin.recipe.edit', $recipe->id)}}" class="btn-action" title="{{__('Edit')}}">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>
                                        <a href="{{route('admin.recipe.delete', $recipe->id)}}" class="btn-action delete" title="{{__('Delete')}}">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                        @if($recipe->status == 1)
                                            <a href="{{route('admin.recipe.inactive', $recipe->id)}}" class="btn-action" title="{{__('Inactive')}}">
                                                <i class="fas fa-times-circle"></i>
                                            </a>
                                        @else
                                            <a href="{{route('admin.recipe.active', $recipe->id)}}" class="btn-action" title="{{__('Active')}}">
                                                <i class="fas fa-check-circle"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('post_scripts')
    <script>
        $(document).ready(function() {
            $('#RecipeTable').DataTable();
        });
    </script>
@endpush
