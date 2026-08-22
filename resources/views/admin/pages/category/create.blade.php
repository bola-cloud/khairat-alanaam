@extends('admin.master', ['menu' => 'catbad', 'submenu' => 'category'])
@section('title', isset($title) ? $title : '')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="breadcrumb__content">
                <div class="breadcrumb__content__left">
                    <div class="breadcrumb__title">
                        <h2>{{__('Add Category')}}</h2>
                    </div>
                </div>
                <div class="breadcrumb__content__right">
                    <nav aria-label="breadcrumb">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{__('Home')}}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{__('Category')}}</li>
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
                                        <form enctype="multipart/form-data" method="POST"
                                            action="{{route('admin.category.store')}}">
                                            @csrf

                                            <div class="input__group mb-25">
                                                <label>{{ __('Category Name ' . langString('en'))}}</label>
                                                <input type="text" id="en_category_name" name="en_category_name"
                                                    value="{{ old('en_category_name') }}" placeholder="Name (English)">
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Category Name ' . langString('fr'))}}</label>
                                                <input type="text" id="fr_category_name" name="fr_category_name"
                                                    value="{{ old('fr_category_name') }}" placeholder="Name (Arabic)">
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __("Order In Homepage")}}</label>
                                                <input type="text" id="order" name="order" value="{{ old('order') }}"
                                                    placeholder="Order">
                                            </div>

                                            <div class="input__group mb-25">
                                                <label>{{ __('Icon')}} (200x200)</label>
                                                <input type="file" id="icon" name="icon" accept="image/*">
                                            </div>
                                            <div class="input__group mb-25" style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
                                                <div style="flex: 1; min-width: 250px;">
                                                    <input type="checkbox" id="show_on_home" name="show_on_home" value="1" {{ old('show_on_home') ? 'checked' : '' }}>
                                                    <label for="show_on_home" style="display: inline-block; margin-bottom: 0; margin-left: 5px; font-weight: bold;">{{ __('Show on Homepage Categories') }}</label>
                                                    <p style="font-size: 11px; color: #777; margin-top: 5px; margin-right: 20px;">{{ __('Displays this category in the main categories carousel on the homepage.') }}</p>
                                                </div>
                                                <div style="flex: 1; min-width: 250px;">
                                                    <input type="checkbox" id="is_occasion" name="is_occasion" value="1" {{ old('is_occasion') ? 'checked' : '' }}>
                                                    <label for="is_occasion" style="display: inline-block; margin-bottom: 0; margin-left: 5px; font-weight: bold;">{{ __('Show as Occasion') }}</label>
                                                    <p style="font-size: 11px; color: #777; margin-top: 5px; margin-right: 20px;">{{ __('Displays this category in the "Shop by Occasion" slider on the homepage.') }}</p>
                                                </div>
                                                <div style="flex: 1; min-width: 250px;">
                                                    <input type="checkbox" id="is_cut" name="is_cut" value="1" {{ old('is_cut') ? 'checked' : '' }}>
                                                    <label for="is_cut" style="display: inline-block; margin-bottom: 0; margin-left: 5px; font-weight: bold;">{{ __('Show as Premium Cut') }}</label>
                                                    <p style="font-size: 11px; color: #777; margin-top: 5px; margin-right: 20px;">{{ __('Displays this category in the "Premium Cuts" grid on the homepage.') }}</p>
                                                </div>
                                            </div>
                                            {{-- <div class="input__group mb-25">
                                                <label>{{__('Description '.langString('en'))}}</label>
                                                <textarea name="en_description" id="en_description"
                                                    placeholder="Description (English)">{{ old('en_description') }}</textarea>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{__('Description '.langString('fr'))}}</label>
                                                <textarea name="fr_description" id="fr_description"
                                                    placeholder="Description (Arabic)">{{ old('fr_description') }}</textarea>
                                            </div> --}}
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