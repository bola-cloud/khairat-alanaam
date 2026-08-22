@extends('admin.master', ['menu' => 'catbad', 'submenu' => 'category'])
@section('title', isset($title) ? $title : '')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="breadcrumb__content">
                <div class="breadcrumb__content__left">
                    <div class="breadcrumb__title">
                        <h2>{{__('Edit Category')}}</h2>
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
                                            action="{{route('admin.category.update')}}">
                                            @csrf
                                            <input type="hidden" name="id" value="{{$edit->id}}">
                                            <div class="input__group mb-25">
                                                <label>{{ __('Category Name ' . langString('en'))}} <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" id="en_category_name" name="en_category_name"
                                                    value="{{$edit->en_Category_Name}}" placeholder="Name (English)" class="form-control">
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Category Name ' . langString('fr'))}} <span
                                                        class="text-danger">*</span> <span
                                                        class="badge badge-info">{{ __('Translate Here') }}</span></label>
                                                <input type="text" id="fr_category_name" name="fr_category_name"
                                                    value="{{$edit->fr_Category_Name}}" placeholder="الاسم (بالعربي)"
                                                    dir="rtl" class="form-control-lg border-primary">
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{ __('Order In Homepage')}}</label>
                                                <input type="text" id="order" name="order" value="{{ $edit->order }}"
                                                    placeholder="{{ __('Order') }}">
                                            </div>

                                            <div class="input__group mb-25">
                                                <label>{{ __('Icon')}} (200x200)</label>
                                                <input type="file" id="icon" name="icon" accept="image/*">
                                                @if($edit->Category_Icon)
                                                    <img src="{{ asset(CategoryImage() . $edit->Category_Icon) }}"
                                                        alt="Current Image" width="100">
                                                @endif
                                            </div>
                                            <div class="input__group mb-25" style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
                                                <div style="flex: 1; min-width: 250px;">
                                                    <input type="checkbox" id="show_on_home" name="show_on_home" value="1" {{ $edit->show_on_home ? 'checked' : '' }}>
                                                    <label for="show_on_home" style="display: inline-block; margin-bottom: 0; margin-left: 5px; font-weight: bold;">{{ __('Show on Homepage Categories') }}</label>
                                                    <p style="font-size: 11px; color: #777; margin-top: 5px; margin-right: 20px;">{{ __('Displays this category in the main categories carousel on the homepage.') }}</p>
                                                </div>
                                                <div style="flex: 1; min-width: 250px;">
                                                    <input type="checkbox" id="is_occasion" name="is_occasion" value="1" {{ $edit->is_occasion ? 'checked' : '' }}>
                                                    <label for="is_occasion" style="display: inline-block; margin-bottom: 0; margin-left: 5px; font-weight: bold;">{{ __('Show as Occasion') }}</label>
                                                    <p style="font-size: 11px; color: #777; margin-top: 5px; margin-right: 20px;">{{ __('Displays this category in the "Shop by Occasion" slider on the homepage.') }}</p>
                                                </div>
                                                <div style="flex: 1; min-width: 250px;">
                                                    <input type="checkbox" id="is_cut" name="is_cut" value="1" {{ $edit->is_cut ? 'checked' : '' }}>
                                                    <label for="is_cut" style="display: inline-block; margin-bottom: 0; margin-left: 5px; font-weight: bold;">{{ __('Show as Premium Cut') }}</label>
                                                    <p style="font-size: 11px; color: #777; margin-top: 5px; margin-right: 20px;">{{ __('Displays this category in the "Premium Cuts" grid on the homepage.') }}</p>
                                                </div>
                                            </div>
                                            {{-- <div class="input__group mb-25">
                                                <label>{{__('Description '.langString('en'))}}</label>
                                                <textarea name="en_description" id="en_description"
                                                    placeholder="Description (English)">{{$edit->en_Description}}</textarea>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label>{{__('Description '.langString('fr'))}}</label>
                                                <textarea name="fr_description" id="fr_description"
                                                    placeholder="Description (Arabic)">{{$edit->fr_Description}}</textarea>
                                            </div> --}}
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

<script src="/assets/js/jquery-3.6.0.min.js"></script>
<script>
    // preview image before upload
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#icon').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#icon").change(function () {
        readURL(this);
    });

</script>