@extends('admin.master', ['menu' => 'advertise'])
@section('title', isset($title) ? $title : '')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="breadcrumb__content">
                <div class="breadcrumb__content__left">
                    <div class="breadcrumb__title">
                        <h2>{{__('Edit Advertise')}}</h2>
                    </div>
                </div>
                <div class="breadcrumb__content__right">
                    <nav aria-label="breadcrumb">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{__('Home')}}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{__('Advertise')}}</li>
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
                                <form enctype="multipart/form-data" method="POST" action="{{route('admin.advertise.update')}}">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$edit->id}}">
                                    <div class="row">
                                        <!-- Image Section -->
                                        <div class="col-md-12 mb-4">
                                            <div class="form-vertical__item bg-style">
                                                <div class="item-top mb-30">
                                                    <h2>{{ __('Hero Image') }}</h2>
                                                </div>
                                                <div class="input__group mb-25">
                                                    <label for="image">{{ __('Hero Image')}} (1600x430)</label>
                                                    <input type="file" class="form-control" name="image" id="image">
                                                </div>
                                                @php
                                                    $heroUrl = '';
                                                    if (!empty($edit->image) && file_exists(public_path($edit->image))) {
                                                        $heroUrl = asset($edit->image);
                                                    } elseif (!empty($edit->image)) {
                                                        $heroUrl = asset(PromotionImage() . $edit->image);
                                                    }
                                                @endphp
                                                <div class="input__group mb-25">
                                                    <img class="admin_image" src="{{ $heroUrl ?: asset(PromotionImage().$edit->Image_One) }}" id="target1" style="max-height: 150px; border-radius: 8px;" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- English Content -->
                                        <div class="col-md-6 mb-4">
                                            <div class="form-vertical__item bg-style">
                                                <div class="item-top mb-30">
                                                    <h2>{{ __('English Content') }}</h2>
                                                </div>
                                                <div class="input__group mb-25">
                                                    <label for="en_badge">{{ __('Badge / Pre-Title (EN)')}}</label>
                                                    <input type="text" class="form-control" name="en_badge" id="en_badge" value="{{ $edit->en_badge ?? '' }}" placeholder="e.g. Established in 2015">
                                                </div>
                                                <div class="input__group mb-25">
                                                    <label for="en_title">{{ __('Title (EN)')}}</label>
                                                    <input type="text" class="form-control" name="en_title" id="en_title" value="{{ $edit->en_title ?? '' }}">
                                                </div>
                                                <div class="input__group mb-25">
                                                    <label for="en_subtitle">{{ __('Subtitle (EN)')}}</label>
                                                    <input type="text" class="form-control" name="en_subtitle" id="en_subtitle" value="{{ $edit->en_subtitle ?? '' }}">
                                                </div>
                                                <div class="input__group mb-25">
                                                    <label for="en_small_description">{{ __('Small Description (EN)')}}</label>
                                                    <textarea class="form-control" name="en_small_description" id="en_small_description" rows="3">{{ $edit->en_small_description ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Arabic Content -->
                                        <div class="col-md-6 mb-4">
                                            <div class="form-vertical__item bg-style">
                                                <div class="item-top mb-30">
                                                    <h2>{{ __('Arabic Content') }}</h2>
                                                </div>
                                                <div class="input__group mb-25">
                                                    <label for="ar_badge">{{ __('Badge / Pre-Title (AR)')}}</label>
                                                    <input type="text" class="form-control" name="ar_badge" id="ar_badge" value="{{ $edit->ar_badge ?? '' }}" placeholder="مثل: تأسست عام 2015">
                                                </div>
                                                <div class="input__group mb-25">
                                                    <label for="ar_title">{{ __('Title (AR)')}}</label>
                                                    <input type="text" class="form-control" name="ar_title" id="ar_title" value="{{ $edit->ar_title ?? $edit->fr_title ?? '' }}">
                                                </div>
                                                <div class="input__group mb-25">
                                                    <label for="ar_subtitle">{{ __('Subtitle (AR)')}}</label>
                                                    <input type="text" class="form-control" name="ar_subtitle" id="ar_subtitle" value="{{ $edit->ar_subtitle ?? $edit->fr_subtitle ?? '' }}">
                                                </div>
                                                <div class="input__group mb-25">
                                                    <label for="ar_small_description">{{ __('Small Description (AR)')}}</label>
                                                    <textarea class="form-control" name="ar_small_description" id="ar_small_description" rows="3">{{ $edit->ar_small_description ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Settings -->
                                        <div class="col-md-12">
                                            <div class="form-vertical__item bg-style">
                                                <div class="item-top mb-30">
                                                    <h2>{{ __('Settings') }}</h2>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="input__group mb-25">
                                                            <label for="link">{{ __('Link')}}</label>
                                                            <input type="text" class="form-control" name="link" id="link" value="{{ $edit->link ?? $edit->Link_One ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="input__group mb-25">
                                                            <label for="display_order">{{ __('Display Order')}}</label>
                                                            <input type="number" class="form-control" name="display_order" id="display_order" value="{{ $edit->display_order ?? 0 }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="hidden" name="location" value="hero">
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-12">
                                                        <div class="input__group mb-25">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="status" id="status" {{ ($edit->status ?? 0) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="status">{{ __('Active') }}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="input__button mt-3">
                                                    <button type="submit" class="btn btn-blue">{{ __('Update')}}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <script>
                                (function(){
                                    var input = document.getElementById('image');
                                    var img = document.getElementById('target1');
                                    if(input){
                                        input.addEventListener('change', function(e){
                                            var file = e.target.files && e.target.files[0];
                                            if(!file){ return; }
                                            var reader = new FileReader();
                                            reader.onload = function(ev){
                                                if(img){ img.src = ev.target.result; img.style.display = ''; }
                                            };
                                            reader.readAsDataURL(file);
                                        });
                                    }
                                })();
                            </script>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
