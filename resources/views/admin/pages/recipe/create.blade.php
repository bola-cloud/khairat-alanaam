@extends('admin.master', ['menu' => 'recipe'])
@section('title', isset($title) ? $title : '')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="breadcrumb__content">
                <div class="breadcrumb__content__left">
                    <div class="breadcrumb__title">
                        <h2>{{__('Add Recipe')}}</h2>
                    </div>
                </div>
                <div class="breadcrumb__content__right">
                    <nav aria-label="breadcrumb">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{__('Home')}}</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admin.recipe.index')}}">{{__('Recipes')}}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{__('Add Recipe')}}</li>
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
                                        <form enctype="multipart/form-data" method="POST" action="{{route('admin.recipe.store')}}">
                                            @csrf
                                            <div class="input__group mb-25">
                                                <label>{{ __('Title (English)')}}</label>
                                                <input type="text" id="en_title" name="en_title" value="{{old('en_title')}}" required>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Title (Arabic/French)')}}</label>
                                                <input type="text" id="fr_title" name="fr_title" value="{{old('fr_title')}}" required>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Time to Cook (Minutes)')}}</label>
                                                <input type="number" id="time_to_cook" name="time_to_cook" value="{{old('time_to_cook') ?? 15}}" required>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Difficulty')}}</label>
                                                <select name="difficulty" id="difficulty" class="form-control">
                                                    <option value="easy">{{__('Easy')}}</option>
                                                    <option value="medium">{{__('Medium')}}</option>
                                                    <option value="hard">{{__('Hard')}}</option>
                                                </select>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Description (English)')}}</label>
                                                <textarea name="en_description" id="en_description" rows="4" required>{{old('en_description')}}</textarea>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Description (Arabic/French)')}}</label>
                                                <textarea name="fr_description" id="fr_description" rows="4" required>{{old('fr_description')}}</textarea>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Image')}}</label>
                                                <input type="file" id="image" name="image" accept="image/*">
                                            </div>
                                            <div class="input__group mb-25" style="display: flex; gap: 20px; align-items: flex-start;">
                                                <div style="flex: 1;">
                                                    <input type="checkbox" id="status" name="status" value="1" checked>
                                                    <label for="status" style="display: inline-block; margin-bottom: 0; margin-left: 5px; font-weight: bold;">{{ __('Active') }}</label>
                                                </div>
                                            </div>
                                            <div class="input__button">
                                                <button type="submit" class="btn btn-blue">{{ __('Add')}}</button>
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
