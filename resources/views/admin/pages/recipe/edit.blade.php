@extends('admin.master', ['menu' => 'recipe'])
@section('title', isset($title) ? $title : '')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="breadcrumb__content">
                <div class="breadcrumb__content__left">
                    <div class="breadcrumb__title">
                        <h2>{{__('Edit Recipe')}}</h2>
                    </div>
                </div>
                <div class="breadcrumb__content__right">
                    <nav aria-label="breadcrumb">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{__('Home')}}</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admin.recipe.index')}}">{{__('Recipes')}}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{__('Edit Recipe')}}</li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="gallery__area bg-style">
                <div class="gallery__content">
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-one" role="tabpanel" aria-labelledby="nav-one-tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-vertical__item bg-style">
                                        <form enctype="multipart/form-data" method="POST" action="{{route('admin.recipe.update', $recipe->id)}}">
                                            @csrf
                                            <div class="input__group mb-25">
                                                <label>{{ __('Title (English)')}}</label>
                                                <input type="text" id="en_title" name="en_title" value="{{$recipe->en_title}}" required>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Title (Arabic/French)')}}</label>
                                                <input type="text" id="fr_title" name="fr_title" value="{{$recipe->fr_title}}" required>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Time to Cook (Minutes)')}}</label>
                                                <input type="number" id="time_to_cook" name="time_to_cook" value="{{$recipe->time_to_cook}}" required>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Difficulty')}}</label>
                                                <select name="difficulty" id="difficulty" class="form-control">
                                                    <option value="easy" {{$recipe->difficulty == 'easy' ? 'selected' : ''}}>{{__('Easy')}}</option>
                                                    <option value="medium" {{$recipe->difficulty == 'medium' ? 'selected' : ''}}>{{__('Medium')}}</option>
                                                    <option value="hard" {{$recipe->difficulty == 'hard' ? 'selected' : ''}}>{{__('Hard')}}</option>
                                                </select>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Description (English)')}}</label>
                                                <textarea name="en_description" id="en_description" rows="4" required>{{$recipe->en_description}}</textarea>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Description (Arabic/French)')}}</label>
                                                <textarea name="fr_description" id="fr_description" rows="4" required>{{$recipe->fr_description}}</textarea>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Image')}}</label>
                                                <input type="file" id="image" name="image" accept="image/*">
                                                @if($recipe->image)
                                                    <img src="{{asset('uploaded_files/recipes/'.$recipe->image)}}" alt="Current Image" width="100" class="mt-2">
                                                @endif
                                            </div>
                                            <div class="input__group mb-25" style="display: flex; gap: 20px; align-items: flex-start;">
                                                <div style="flex: 1;">
                                                    <input type="checkbox" id="status" name="status" value="1" {{$recipe->status == 1 ? 'checked' : ''}}>
                                                    <label for="status" style="display: inline-block; margin-bottom: 0; margin-left: 5px; font-weight: bold;">{{ __('Active') }}</label>
                                                </div>
                                            </div>
                                            <div class="input__button">
                                                <button type="submit" class="btn btn-blue">{{ __('Update')}}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
