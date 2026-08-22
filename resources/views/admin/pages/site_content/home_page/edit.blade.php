@extends('admin.master', ['menu' => 'site_content', 'submenu' => 'content_home'])
@section('title', isset($title) ? $title : '')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="breadcrumb__content">
                <div class="breadcrumb__content__left">
                    <div class="breadcrumb__title">
                        <h2>{{ __('Edit Homepage') }}</h2>
                    </div>
                </div>
                <div class="breadcrumb__content__right">
                    <nav aria-label="breadcrumb">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Homepage') }}</li>
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
                            {{-- Render each homepage section as a separate card with its own update form --}}
                            <div class="row g-3">
                                @if(isset($sections) && $sections->count())
                                    @foreach($sections as $sec)
                                        @php
                                            if(in_array($sec->section_key, ['newdesign_brands', 'newdesign_sale_banner'])) continue;
                                        @endphp
                                        @php
                                            $secKey = $sec->section_key;
                                            // hide internal prefix like 'newdesign' from headings shown to client
                                            $displayTitle = preg_replace('/^newdesign[_\-]*/i', '', $secKey);
                                            $content_en = (array) ($sec->content_en ?? []);
                                            $content_fr = (array) ($sec->content_fr ?? []);
                                        @endphp
                                        <div class="col-md-6">
                                            <div class="form-vertical__item bg-style">
                                                <div class="item-top mb-20 d-flex justify-content-between align-items-center">
                                                    <h4 class="m-0">{{ ucwords(str_replace('_',' ', $displayTitle)) }}</h4>
                                                    <small class="text-muted">{{ $sec->status ? __('Active') : __('Inactive') }}</small>
                                                </div>
                                                <form enctype="multipart/form-data" method="POST" action="{{ route('admin.home.page.site.content.update') }}">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $sec->id }}">
                                                    <input type="hidden" name="location" value="{{ $secKey }}">

                                                    @if($secKey === 'newdesign_features')
                                                        {{-- Edit four features only --}}
                                                        <div class="mb-2"><strong>{{ __('English') }}</strong></div>
                                                        @for($i=1;$i<=4;$i++)
                                                            @php $it = $content_en['items'][$i-1] ?? ['title'=>'','desc'=>'','icon'=>'']; @endphp
                                                            <div class="input__group mb-2">
                                                                <label>{{ __('Feature') }} #{{ $i }} - {{ __('Title') }}</label>
                                                                <input type="text" class="form-control" name="en_feature_{{ $i }}_title" value="{{ old('en_feature_'.$i.'_title', $it['title'] ?? '') }}">
                                                            </div>
                                                            <div class="input__group mb-2">
                                                                <label>{{ __('Feature') }} #{{ $i }} - {{ __('Description') }}</label>
                                                                <input type="text" class="form-control" name="en_feature_{{ $i }}_desc" value="{{ old('en_feature_'.$i.'_desc', $it['desc'] ?? '') }}">
                                                            </div>
                                                        @endfor
                                                        <hr>
                                                        <div class="mb-2"><strong>{{ __('Arabic') }}</strong></div>
                                                        @for($i=1;$i<=4;$i++)
                                                            @php $itf = $content_fr['items'][$i-1] ?? ['title'=>'','desc'=>'','icon'=>'']; @endphp
                                                            <div class="input__group mb-2">
                                                                <label>{{ __('Feature') }} #{{ $i }} - {{ __('Title (AR)') }}</label>
                                                                <input type="text" class="form-control" name="fr_feature_{{ $i }}_title" value="{{ old('fr_feature_'.$i.'_title', $itf['title'] ?? '') }}">
                                                            </div>
                                                            <div class="input__group mb-2">
                                                                <label>{{ __('Feature') }} #{{ $i }} - {{ __('Description (AR)') }}</label>
                                                                <input type="text" class="form-control" name="fr_feature_{{ $i }}_desc" value="{{ old('fr_feature_'.$i.'_desc', $itf['desc'] ?? '') }}">
                                                            </div>
                                                        @endfor

                                                    @elseif($secKey === 'newdesign_hero')
                                                         <div class="input__group mb-2">
                                                             <label>{{ __('Hero Banner Image') }} <span class="text-danger" style="font-size: 12px; margin-right: 10px;">(المقاس المفضل: 1920x800 بيكسل)</span></label>
                                                             <input type="file" class="form-control" name="image">
                                                             @php
                                                                 $heroImg = null;
                                                                 if(!empty($sec->image)){
                                                                     if (file_exists(public_path($sec->image))) {
                                                                         $heroImg = asset($sec->image);
                                                                     } elseif (file_exists(public_path(PromotionImage() . $sec->image))) {
                                                                         $heroImg = asset(PromotionImage() . $sec->image);
                                                                     } else {
                                                                         $heroImg = asset(PromotionImage() . $sec->image);
                                                                     }
                                                                 }
                                                             @endphp
                                                             @if($heroImg)
                                                                 <img src="{{ $heroImg }}" style="max-height:80px; margin-top:8px; max-width:80px;" />
                                                             @endif
                                                         </div>
                                                         
                                                     @elseif($secKey === 'newdesign_coffee_crops_slider')
                                                         <div class="input__group mb-2" id="crops-slider-card-{{ $sec->id }}">
                                                             <label>{{ __('Manage Coffee Crops Slider Images') }}</label>
                                                             <div class="mb-2">
                                                                 <div id="cropsSliderUploadForm-{{ $sec->id }}" data-csrf="{{ csrf_token() }}">
                                                                     <input type="file" name="images[]" id="cropsSliderImages-{{ $sec->id }}" class="form-control mb-2" multiple accept="image/*">
                                                                     <div class="d-flex gap-2">
                                                                         <button type="button" class="btn btn-primary" id="uploadCropsSliderBtn-{{ $sec->id }}">{{ __('Upload Selected') }}</button>
                                                                         <button type="button" class="btn btn-secondary" id="clearCropsSliderBtn-{{ $sec->id }}">{{ __('Clear') }}</button>
                                                                         <span id="cropsSliderUploadStatus-{{ $sec->id }}" class="ms-2 text-muted"></span>
                                                                     </div>
                                                                 </div>
                                                             </div>
                                                             @php
                                                                 $sliderImages = $sec->content_en['images'] ?? [];
                                                             @endphp
                                                             <div class="d-flex gap-3 flex-wrap" id="cropsSliderList-{{ $sec->id }}">
                                                                 @forelse($sliderImages as $index => $img)
                                                                     @php
                                                                         $imgPublic = file_exists(public_path($img)) ? asset($img) : (isset($img) ? asset(PromotionImage() . $img) : '');
                                                                     @endphp
                                                                     <div class="card p-2 position-relative" style="width:140px;" id="crops-slide-img-{{ $index }}">
                                                                         <div class="text-center">
                                                                             @if($imgPublic)
                                                                                 <img src="{{ $imgPublic }}" alt="slide" style="max-height:60px; display:block; margin:auto;" />
                                                                             @else
                                                                                 <div class="text-muted">{{ __('No image') }}</div>
                                                                             @endif
                                                                             <button type="button" class="btn btn-danger btn-sm mt-2 delete-crop-slide-btn" data-image-path="{{ $img }}">{{ __('Delete') }}</button>
                                                                         </div>
                                                                     </div>
                                                                 @empty
                                                                     <div class="text-muted">{{ __('No slider images yet') }}</div>
                                                                 @endforelse
                                                             </div>
                                                         </div>
                                                         <script>
                                                             (function(){
                                                                 const uploadBtn = document.getElementById('uploadCropsSliderBtn-{{ $sec->id }}');
                                                                 const clearBtn = document.getElementById('clearCropsSliderBtn-{{ $sec->id }}');
                                                                 const fileInput = document.getElementById('cropsSliderImages-{{ $sec->id }}');
                                                                 const statusEl = document.getElementById('cropsSliderUploadStatus-{{ $sec->id }}');

                                                                 if(uploadBtn){
                                                                     uploadBtn.addEventListener('click', async function(){
                                                                         const files = fileInput.files;
                                                                         if(!files || files.length === 0){
                                                                             statusEl.textContent = '{{ __('No files selected') }}';
                                                                             return;
                                                                         }
                                                                         statusEl.textContent = '{{ __('Uploading...') }}';
                                                                         uploadBtn.disabled = true;

                                                                         const url = "{{ route('admin.advertise.bulk_store') }}";
                                                                         let csrf = null;
                                                                         const meta = document.querySelector('meta[name="csrf-token"]');
                                                                         if (meta) {
                                                                             csrf = meta.getAttribute('content');
                                                                         }
                                                                         
                                                                         const fd = new FormData();
                                                                         for(let i=0;i<files.length;i++){
                                                                             fd.append('images[]', files[i]);
                                                                         }
                                                                         fd.append('section_key', 'newdesign_coffee_crops_slider');
                                                                         fd.append('location', 'coffee_crops_slider');
                                                                         fd.append('display_order', 0);
                                                                         if (csrf) fd.append('_token', csrf);

                                                                         const headers = { 'Accept': 'application/json' };
                                                                         if (csrf) headers['X-CSRF-TOKEN'] = csrf;

                                                                         try{
                                                                             const resp = await fetch(url, { method: 'POST', body: fd, credentials: 'same-origin', headers });
                                                                             let data = null;
                                                                             try { data = await resp.json(); } catch(e) { }
                                                                             if(!resp.ok){
                                                                                 console.error('Upload error', resp.status, data);
                                                                                 if(data && data.errors){
                                                                                     statusEl.textContent = Object.values(data.errors).flat().join('; ');
                                                                                 } else if(data && data.message){
                                                                                     statusEl.textContent = data.message;
                                                                                 } else {
                                                                                     statusEl.textContent = '{{ __('Upload failed') }}';
                                                                                 }
                                                                                 uploadBtn.disabled = false;
                                                                                 return;
                                                                             }
                                                                             statusEl.textContent = '{{ __('Upload completed, reloading...') }}';
                                                                             setTimeout(function(){ location.reload(); }, 700);
                                                                         }catch(err){
                                                                             console.error(err);
                                                                             statusEl.textContent = '{{ __('Upload failed') }}';
                                                                             uploadBtn.disabled = false;
                                                                         }
                                                                     });
                                                                 }
                                                                 if(clearBtn){
                                                                     clearBtn.addEventListener('click', function(){ fileInput.value = ''; statusEl.textContent = ''; });
                                                                 }
                                                                 
                                                                 // Setup delete action
                                                                 document.querySelectorAll('.delete-crop-slide-btn').forEach(btn => {
                                                                     btn.addEventListener('click', async function() {
                                                                         if(!confirm('{{ __('Are you sure you want to delete this slide?') }}')) return;
                                                                         const imagePath = btn.getAttribute('data-image-path');
                                                                         const url = "{{ route('admin.homepage_section.delete_image') }}";
                                                                         let csrf = null;
                                                                         const meta = document.querySelector('meta[name="csrf-token"]');
                                                                         if (meta) csrf = meta.getAttribute('content');
                                                                         
                                                                         try {
                                                                             const resp = await fetch(url, {
                                                                                 method: 'POST',
                                                                                 headers: {
                                                                                     'Content-Type': 'application/json',
                                                                                     'Accept': 'application/json',
                                                                                     'X-CSRF-TOKEN': csrf
                                                                                 },
                                                                                 body: JSON.stringify({
                                                                                     section_key: 'newdesign_coffee_crops_slider',
                                                                                     image: imagePath
                                                                                 })
                                                                             });
                                                                             const data = await resp.json();
                                                                             if(data.success) {
                                                                                 location.reload();
                                                                             } else {
                                                                                 alert(data.message || 'Error deleting image');
                                                                             }
                                                                         } catch(err) {
                                                                             console.error(err);
                                                                             alert('Error deleting image');
                                                                         }
                                                                     });
                                                                 });
                                                             })();
                                                         </script>

                                                     @elseif($secKey === 'newdesign_stats')
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Title') }}</label>
                                                            <input type="text" class="form-control" name="en_title" value="{{ old('en_title', $content_en['title'] ?? '') }}">
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Subtitle / Lead') }}</label>
                                                            <input type="text" class="form-control" name="en_description_one" value="{{ old('en_description_one', $content_en['lead'] ?? '') }}">
                                                        </div>
                                                        @php
                                                            $statsEn = $content_en['stats'] ?? [];
                                                            $statsFr = $content_fr['stats'] ?? [];
                                                        @endphp
                                                        @for($i = 1; $i <= 4; $i++)
                                                            @php
                                                                $ste = $statsEn[$i - 1] ?? [];
                                                                $stf = $statsFr[$i - 1] ?? [];
                                                            @endphp
                                                            <div class="border p-3 rounded mb-3 bg-light">
                                                                <div class="font-bold mb-2 text-[#1A4231]">{{ __('Stat') }} #{{ $i }}</div>
                                                                <div class="input__group mb-2">
                                                                    <label>{{ __('Value') }} (e.g. 1.2M, 50k, 100%)</label>
                                                                    <input type="text" class="form-control" name="en_stat_{{ $i }}_val" value="{{ old('en_stat_'.$i.'_val', $ste['val'] ?? '') }}">
                                                                </div>
                                                                <div class="input__group mb-2">
                                                                    <label>{{ __('Label') }} (English)</label>
                                                                    <input type="text" class="form-control" name="en_stat_{{ $i }}_lbl" value="{{ old('en_stat_'.$i.'_lbl', $ste['lbl'] ?? '') }}">
                                                                </div>
                                                                <div class="input__group mb-2">
                                                                    <label>{{ __('Label (AR)') }}</label>
                                                                    <input type="text" class="form-control" name="fr_stat_{{ $i }}_lbl" value="{{ old('fr_stat_'.$i.'_lbl', $stf['lbl'] ?? '') }}">
                                                                </div>
                                                            </div>
                                                        @endfor
                                                        <hr>
                                                        <div class="mb-2"><strong>{{ __('Arabic Translations') }}</strong></div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Title (AR)') }}</label>
                                                            <input type="text" class="form-control" name="fr_title" value="{{ old('fr_title', $content_fr['title'] ?? '') }}">
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Subtitle / Lead (AR)') }}</label>
                                                            <input type="text" class="form-control" name="fr_description_one" value="{{ old('fr_description_one', $content_fr['lead'] ?? '') }}">
                                                        </div>

                                                    @elseif($secKey === 'newdesign_sale_banner')
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Title') }}</label>
                                                            <input type="text" class="form-control" name="en_title" value="{{ old('en_title', $content_en['title'] ?? '') }}">
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Lead / Short Description') }}</label>
                                                            <textarea class="form-control" name="en_description_one">{{ old('en_description_one', $content_en['lead'] ?? '') }}</textarea>
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Button Text') }}</label>
                                                            <input type="text" class="form-control" name="en_button_text" value="{{ old('en_button_text', data_get($content_en,'button.text','')) }}">
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Button URL') }}</label>
                                                            <input type="text" class="form-control" name="en_button_url" value="{{ old('en_button_url', data_get($content_en,'button.url','#')) }}">
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Banner Image') }} <span class="text-danger" style="font-size: 12px; margin-right: 10px;">(المقاس المفضل: 1920x400 بيكسل)</span></label>
                                                            <input type="file" class="form-control" name="image">
                                                            @php
                                                                $bannerImg = null;
                                                                if(!empty($sec->image)){
                                                                    if (file_exists(public_path($sec->image))) {
                                                                        $bannerImg = asset($sec->image);
                                                                    } elseif (file_exists(public_path(PromotionImage() . $sec->image))) {
                                                                        $bannerImg = asset(PromotionImage() . $sec->image);
                                                                    } else {
                                                                        $bannerImg = asset(PromotionImage() . $sec->image);
                                                                    }
                                                                }
                                                            @endphp
                                                            @if($bannerImg)
                                                                <img src="{{ $bannerImg }}" style="max-height:80px; margin-top:8px; max-width:80px;" />
                                                            @endif
                                                        </div>

                                                    @elseif($secKey === 'newdesign_why_choose')
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Title') }}</label>
                                                            <input type="text" class="form-control" name="en_title" value="{{ old('en_title', $content_en['title'] ?? '') }}">
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Lead / Paragraph') }}</label>
                                                            <textarea class="form-control" name="en_description_one">{{ old('en_description_one', $content_en['lead'] ?? '') }}</textarea>
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Points (one per line)') }}</label>
                                                            <textarea class="form-control" name="en_description_two">{{ old('en_description_two', is_array($content_en['points'] ?? null) ? implode("\n", $content_en['points']) : ($content_en['points'] ?? '')) }}</textarea>
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Image') }} <span class="text-danger" style="font-size: 12px; margin-right: 10px;">(المقاس المفضل: 800x800 بيكسل)</span></label>
                                                            <input type="file" class="form-control" name="image">
                                                            @php
                                                                $whyImg = null;
                                                                if(!empty($sec->image)){
                                                                    if (file_exists(public_path($sec->image))) {
                                                                        $whyImg = asset($sec->image);
                                                                    } elseif (file_exists(public_path(PromotionImage() . $sec->image))) {
                                                                        $whyImg = asset(PromotionImage() . $sec->image);
                                                                    } else {
                                                                        $whyImg = asset(PromotionImage() . $sec->image);
                                                                    }
                                                                }
                                                            @endphp
                                                            @if($whyImg)
                                                                <img src="{{ $whyImg }}" style="max-height:80px; margin-top:8px;  max-width:80px;" />
                                                            @endif
                                                        </div>

                                                        <hr>
                                                        <div class="mb-2"><strong>{{ __('Arabic') }}</strong></div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Title (AR)') }}</label>
                                                            <input type="text" class="form-control" name="fr_title" value="{{ old('fr_title', $content_fr['title'] ?? '') }}">
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Lead / Paragraph (AR)') }}</label>
                                                            <textarea class="form-control" name="fr_description_one">{{ old('fr_description_one', $content_fr['lead'] ?? '') }}</textarea>
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Points (one per line) (AR)') }}</label>
                                                            <textarea class="form-control" name="fr_description_two">{{ old('fr_description_two', is_array($content_fr['points'] ?? null) ? implode("\n", $content_fr['points']) : ($content_fr['points'] ?? '')) }}</textarea>
                                                        </div>
                                                        </div>

                                                    @elseif($secKey === 'newdesign_farm')
                                                        <div class="mb-2"><strong>{{ __('English') }}</strong></div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Title') }}</label>
                                                            <input type="text" class="form-control" name="en_title" value="{{ old('en_title', $content_en['title'] ?? '') }}">
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Subtitle / Lead') }}</label>
                                                            <textarea class="form-control" name="en_description_one" rows="3">{{ old('en_description_one', $content_en['lead'] ?? '') }}</textarea>
                                                        </div>
                                                        
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Main Farm Image') }} <span class="text-danger" style="font-size: 12px; margin-right: 10px;">(المقاس المفضل: 600x500 بيكسل)</span></label>
                                                            <input type="file" class="form-control" name="image">
                                                            @php
                                                                $farmImg = null;
                                                                if(!empty($sec->image)){
                                                                    if (file_exists(public_path($sec->image))) {
                                                                        $farmImg = asset($sec->image);
                                                                    } elseif (file_exists(public_path(PromotionImage() . $sec->image))) {
                                                                        $farmImg = asset(PromotionImage() . $sec->image);
                                                                    }
                                                                }
                                                            @endphp
                                                            @if($farmImg)
                                                                <img src="{{ $farmImg }}" style="max-height:80px; margin-top:8px; max-width:80px;" />
                                                            @endif
                                                        </div>

                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Inset Farm Image (Small)') }} <span class="text-danger" style="font-size: 12px; margin-right: 10px;">(المقاس المفضل: 300x300 بيكسل مربعة)</span></label>
                                                            <input type="file" class="form-control" name="image2">
                                                            @php
                                                                $farmImg2 = null;
                                                                if(isset($content_en['image2']) && !empty($content_en['image2'])){
                                                                    if (file_exists(public_path(PromotionImage() . $content_en['image2']))) {
                                                                        $farmImg2 = asset(PromotionImage() . $content_en['image2']);
                                                                    }
                                                                }
                                                            @endphp
                                                            @if($farmImg2)
                                                                <img src="{{ $farmImg2 }}" style="max-height:80px; margin-top:8px; max-width:80px;" />
                                                            @endif
                                                        </div>

                                                        @for($i=1;$i<=3;$i++)
                                                            @php $it = $content_en['items'][$i-1] ?? ['title'=>'','desc'=>'','icon'=>'']; @endphp
                                                            <div class="border p-3 rounded mb-3 bg-light">
                                                                <div class="font-bold mb-2 text-[#1A4231]">{{ __('Feature') }} #{{ $i }}</div>
                                                                <div class="input__group mb-2">
                                                                    <label>{{ __('Feature Title') }}</label>
                                                                    <input type="text" class="form-control" name="en_farm_feat_{{ $i }}_title" value="{{ old('en_farm_feat_'.$i.'_title', $it['title'] ?? '') }}">
                                                                </div>
                                                                <div class="input__group mb-2">
                                                                    <label>{{ __('Feature Description') }}</label>
                                                                    <input type="text" class="form-control" name="en_farm_feat_{{ $i }}_desc" value="{{ old('en_farm_feat_'.$i.'_desc', $it['desc'] ?? '') }}">
                                                                </div>
                                                                <div class="input__group mb-2">
                                                                    <label>{{ __('Feature Icon (FontAwesome Class)') }}</label>
                                                                    <input type="text" class="form-control" name="en_farm_feat_{{ $i }}_icon" value="{{ old('en_farm_feat_'.$i.'_icon', $it['icon'] ?? '') }}" placeholder="fas fa-check">
                                                                </div>
                                                            </div>
                                                        @endfor
                                                        
                                                        <hr>
                                                        <div class="mb-2"><strong>{{ __('Arabic') }}</strong></div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Title (AR)') }}</label>
                                                            <input type="text" class="form-control" name="fr_title" value="{{ old('fr_title', $content_fr['title'] ?? '') }}">
                                                        </div>
                                                        <div class="input__group mb-2">
                                                            <label>{{ __('Subtitle / Lead (AR)') }}</label>
                                                            <textarea class="form-control" name="fr_description_one" rows="3">{{ old('fr_description_one', $content_fr['lead'] ?? '') }}</textarea>
                                                        </div>
                                                        @for($i=1;$i<=3;$i++)
                                                            @php $itf = $content_fr['items'][$i-1] ?? ['title'=>'','desc'=>'','icon'=>'']; @endphp
                                                            <div class="border p-3 rounded mb-3 bg-light">
                                                                <div class="font-bold mb-2 text-[#1A4231]">{{ __('Feature') }} #{{ $i }} (AR)</div>
                                                                <div class="input__group mb-2">
                                                                    <label>{{ __('Feature Title (AR)') }}</label>
                                                                    <input type="text" class="form-control" name="fr_farm_feat_{{ $i }}_title" value="{{ old('fr_farm_feat_'.$i.'_title', $itf['title'] ?? '') }}">
                                                                </div>
                                                                <div class="input__group mb-2">
                                                                    <label>{{ __('Feature Description (AR)') }}</label>
                                                                    <input type="text" class="form-control" name="fr_farm_feat_{{ $i }}_desc" value="{{ old('fr_farm_feat_'.$i.'_desc', $itf['desc'] ?? '') }}">
                                                                </div>
                                                                <div class="input__group mb-2">
                                                                    <label>{{ __('Feature Icon (FontAwesome Class)') }}</label>
                                                                    <input type="text" class="form-control" name="fr_farm_feat_{{ $i }}_icon" value="{{ old('fr_farm_feat_'.$i.'_icon', $itf['icon'] ?? '') }}" placeholder="fas fa-check">
                                                                </div>
                                                            </div>
                                                        @endfor

                                                    @elseif($secKey === 'newdesign_brands')
                                                        <div class="input__group mb-2" id="brands-card-{{ $sec->id }}">
                                                            <label>{{ __('Manage Brand Logos') }}</label>
                                                            <div class="mb-2">
                                                                {{-- Inline multiple uploader: select many files and upload without leaving page --}}
                                                                <div id="brandUploadForm-{{ $sec->id }}" data-csrf="{{ csrf_token() }}">
                                                                    <input type="file" name="images[]" id="brandImages-{{ $sec->id }}" class="form-control mb-2" multiple accept="image/*">
                                                                    <div class="d-flex gap-2">
                                                                        <button type="button" class="btn btn-primary" id="uploadBrandsBtn-{{ $sec->id }}">{{ __('Upload Selected') }}</button>
                                                                        <button type="button" class="btn btn-secondary" id="clearBrandsBtn-{{ $sec->id }}">{{ __('Clear') }}</button>
                                                                        <span id="brandUploadStatus-{{ $sec->id }}" class="ms-2 text-muted"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @php
                                                                $sectionModel = \App\Models\Admin\SiteContent\HomepageSection::where('section_key','newdesign_brands')->first();
                                                                $brandImages = $sectionModel->content_en['images'] ?? [];
                                                            @endphp
                                                            <div class="d-flex gap-3 flex-wrap" id="brandsList-{{ $sec->id }}">
                                                                @forelse($brandImages as $img)
                                                                    @php
                                                                        $imgPublic = file_exists(public_path($img)) ? asset($img) : (isset($img) ? asset(PromotionImage() . $img) : '');
                                                                    @endphp
                                                                    <div class="card p-2" style="width:140px;">
                                                                        <div class="text-center">
                                                                            @if($imgPublic)
                                                                                <img src="{{ $imgPublic }}" alt="brand" style="max-height:48px; display:block; margin:auto;" />
                                                                            @else
                                                                                <div class="text-muted">{{ __('No image') }}</div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @empty
                                                                    <div class="text-muted">{{ __('No brand images yet') }}</div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                        <script>
                                                            (function(){
                                                                const uploadBtn = document.getElementById('uploadBrandsBtn-{{ $sec->id }}');
                                                                const clearBtn = document.getElementById('clearBrandsBtn-{{ $sec->id }}');
                                                                const fileInput = document.getElementById('brandImages-{{ $sec->id }}');
                                                                const statusEl = document.getElementById('brandUploadStatus-{{ $sec->id }}');

                                                                if(uploadBtn){
                                                                    uploadBtn.addEventListener('click', async function(){
                                                                        const files = fileInput.files;
                                                                        if(!files || files.length === 0){
                                                                            statusEl.textContent = '{{ __('No files selected') }}';
                                                                            return;
                                                                        }
                                                                        statusEl.textContent = '{{ __('Uploading...') }}';
                                                                        uploadBtn.disabled = true;

                                                                        const url = "{{ route('admin.advertise.bulk_store') }}";
                                                                        // Get CSRF token: prefer meta tag, fallback to the container data attribute
                                                                        let csrf = null;
                                                                        const meta = document.querySelector('meta[name="csrf-token"]');
                                                                        if (meta) {
                                                                            csrf = meta.getAttribute('content');
                                                                        } else {
                                                                            const container = document.getElementById('brandUploadForm-{{ $sec->id }}');
                                                                            if (container && container.dataset && container.dataset.csrf) {
                                                                                csrf = container.dataset.csrf;
                                                                            }
                                                                        }
                                                                        if (!csrf) {
                                                                            console.warn('CSRF token not found for brand uploader');
                                                                        }

                                                                        const fd = new FormData();
                                                                        for(let i=0;i<files.length;i++){
                                                                            fd.append('images[]', files[i]);
                                                                        }
                                                                        fd.append('location', 'company_logo');
                                                                        fd.append('display_order', 0);
                                                                        if (csrf) fd.append('_token', csrf);

                                                                        const headers = { 'Accept': 'application/json' };
                                                                        if (csrf) headers['X-CSRF-TOKEN'] = csrf;

                                                                        try{
                                                                            const resp = await fetch(url, { method: 'POST', body: fd, credentials: 'same-origin', headers });
                                                                            let data = null;
                                                                            try { data = await resp.json(); } catch(e) { /* ignore parse errors */ }
                                                                            if(!resp.ok){
                                                                                console.error('Upload error', resp.status, data);
                                                                                if(data && data.errors){
                                                                                    const messages = Object.values(data.errors).flat().join('; ');
                                                                                    statusEl.textContent = messages;
                                                                                } else if(data && data.message){
                                                                                    statusEl.textContent = data.message;
                                                                                } else {
                                                                                    statusEl.textContent = '{{ __('Upload failed') }}';
                                                                                }
                                                                                uploadBtn.disabled = false;
                                                                                return;
                                                                            }
                                                                            statusEl.textContent = '{{ __('Upload completed, reloading...') }}';
                                                                            setTimeout(function(){ location.reload(); }, 700);
                                                                        }catch(err){
                                                                            console.error(err);
                                                                            statusEl.textContent = '{{ __('Upload failed') }}';
                                                                            uploadBtn.disabled = false;
                                                                        }
                                                                    });
                                                                }
                                                                if(clearBtn){
                                                                    clearBtn.addEventListener('click', function(){ fileInput.value = ''; statusEl.textContent = ''; });
                                                                }
                                                            })();
                                                        </script>

                                                     @else

                                                         {{-- Generic fields for unexpected/custom sections --}}

                                                         <div class="mb-2"><strong>{{ __('English') }}</strong></div>

                                                         <div class="input__group mb-2">

                                                             <label>{{ __('Title') }}</label>

                                                             <input type="text" class="form-control" name="en_title" value="{{ old('en_title', $content_en['title'] ?? '') }}">

                                                         </div>

                                                         <div class="input__group mb-2">

                                                             <label>{{ __('Lead / Paragraph') }}</label>

                                                             <textarea class="form-control" name="en_description_one">{{ old('en_description_one', $content_en['lead'] ?? '') }}</textarea>

                                                         </div>

                                                         <div class="input__group mb-2">

                                                             <label>{{ __('Points (one per line)') }}</label>

                                                             <textarea class="form-control" name="en_description_two">{{ old('en_description_two', is_array($content_en['points'] ?? null) ? implode("\n", $content_en['points']) : ($content_en['points'] ?? '')) }}</textarea>

                                                         </div>

                                                         <div class="input__group mb-2">

                                                             <label>{{ __('Button Text') }}</label>

                                                             <input type="text" class="form-control" name="en_button_text" value="{{ old('en_button_text', data_get($content_en,'button.text','')) }}">

                                                         </div>

                                                         <div class="input__group mb-2">

                                                             <label>{{ __('Button URL') }}</label>

                                                             <input type="text" class="form-control" name="en_button_url" value="{{ old('en_button_url', data_get($content_en,'button.url','')) }}">

                                                         </div>

                                                         <div class="input__group mb-2">

                                                             <label>{{ __('Image') }}</label>

                                                             <input type="file" class="form-control" name="image">

                                                             @php

                                                                 $genericImg = null;

                                                                 if(!empty($sec->image)){

                                                                     if (file_exists(public_path($sec->image))) {

                                                                         $genericImg = asset($sec->image);

                                                                     } elseif (file_exists(public_path(PromotionImage() . $sec->image))) {

                                                                         $genericImg = asset(PromotionImage() . $sec->image);

                                                                     } else {

                                                                         $genericImg = asset(PromotionImage() . $sec->image);

                                                                     }

                                                                 }

                                                             @endphp

                                                             @if($genericImg)

                                                                 <img src="{{ $genericImg }}" style="max-height:80px; margin-top:8px; max-width:80px;" />

                                                             @endif

                                                         </div>

                                                         <hr>

                                                         <div class="mb-2"><strong>{{ __('Arabic') }}</strong></div>

                                                         <div class="input__group mb-2">

                                                             <label>{{ __('Title (AR)') }}</label>

                                                             <input type="text" class="form-control" name="fr_title" value="{{ old('fr_title', $content_fr['title'] ?? '') }}">

                                                         </div>

                                                         <div class="input__group mb-2">

                                                             <label>{{ __('Lead / Paragraph (AR)') }}</label>

                                                             <textarea class="form-control" name="fr_description_one">{{ old('fr_description_one', $content_fr['lead'] ?? '') }}</textarea>

                                                         </div>

                                                         <div class="input__group mb-2">

                                                             <label>{{ __('Points (one per line) (AR)') }}</label>

                                                             <textarea class="form-control" name="fr_description_two">{{ old('fr_description_two', is_array($content_fr['points'] ?? null) ? implode("\n", $content_fr['points']) : ($content_fr['points'] ?? '')) }}</textarea>

                                                         </div>

                                                         <div class="input__group mb-2">

                                                             <label>{{ __('Button Text (AR)') }}</label>

                                                             <input type="text" class="form-control" name="fr_button_text" value="{{ old('fr_button_text', data_get($content_fr,'button.text','')) }}">

                                                         </div>

                                                         <div class="input__group mb-2">

                                                             <label>{{ __('Button URL (AR)') }}</label>

                                                             <input type="text" class="form-control" name="fr_button_url" value="{{ old('fr_button_url', data_get($content_fr,'button.url','')) }}">

                                                         </div>



                                                    @endif

                                                    <div class="input__button mt-3">
                                                        <button type="submit" class="btn btn-blue">{{ __('Update') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="alert alert-info">{{ __('No homepage sections found.') }}</div>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('post_scripts')
    <script>
        "use strict";
        $(document).ready(function() {
            // initialize summernote for any editor IDs that may be present
            if (typeof $.fn.summernote !== 'undefined') {
                ["#summernote", "#summernote2", "#summernote3", "#summernote4"].forEach(function(id) {
                    if ($(id).length) {
                        $(id).summernote({
                            placeholder: 'Description',
                            height: 300
                        });
                    }
                });
            }
            $('.dropdown-toggle').dropdown();
        });
    </script>
@endpush
