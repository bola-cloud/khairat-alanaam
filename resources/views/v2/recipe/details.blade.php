@extends('v2.layouts.master')
@section('title', $recipe->localized_title)
@section('content')
<div class="breadcrumb-area" style="background: #f9f9f9; padding: 30px 0;">
    <div class="v2-container">
        <ul class="breadcrumb-list" style="list-style: none; padding: 0; margin: 0; display: flex; gap: 10px; font-size: 14px; color: #666;">
            <li><a href="{{ route('front.v2.home') }}" style="color: #666; text-decoration: none;">@lang('v2_home.home')</a></li>
            <li>/</li>
            <li style="color: #e32636; font-weight: 600;">{{ $recipe->localized_title }}</li>
        </ul>
    </div>
</div>

<section class="recipe-details-area" style="padding: 60px 0; background: #fff;">
    <div class="v2-container">
        <div class="row">
            <div class="col-lg-8" style="margin: 0 auto;">
                <div class="recipe-header" style="text-align: center; margin-bottom: 40px;">
                    <div style="display: inline-block; padding: 5px 15px; border: 1px solid #ffb3b3; color: #e32636; border-radius: 20px; font-size: 13px; font-weight: 700; margin-bottom: 20px;">
                        <i class="fas fa-hat-chef"></i> {{ __('Recipe') }}
                    </div>
                    <h1 style="font-size: 36px; font-weight: 800; color: #333; margin-bottom: 20px;">{{ $recipe->localized_title }}</h1>
                    
                    <div class="recipe-meta" style="display: flex; justify-content: center; gap: 30px; color: #666; font-size: 15px; font-weight: 600;">
                        <span>
                            <i class="far fa-clock" style="color: #e32636; margin-inline-end: 5px;"></i> 
                            {{ $recipe->time_to_cook }} {{ __('minutes') }}
                        </span>
                        <span>
                            <i class="fas fa-signal" style="color: #e32636; margin-inline-end: 5px;"></i> 
                            @if($recipe->difficulty == 'easy')
                                {{ __('Easy') }}
                            @elseif($recipe->difficulty == 'medium')
                                {{ __('Medium') }}
                            @else
                                {{ __('Hard') }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="recipe-image" style="margin-bottom: 40px; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                    @if($recipe->image)
                        <img src="{{ asset('uploaded_files/recipes/' . $recipe->image) }}" alt="{{ $recipe->localized_title }}" style="width: 100%; height: auto; display: block;">
                    @else
                        <img src="{{ asset('assets/images/placeholder.png') }}" alt="{{ $recipe->localized_title }}" style="width: 100%; height: 400px; object-fit: cover; display: block;">
                    @endif
                </div>

                <div class="recipe-content" style="font-size: 16px; line-height: 1.8; color: #555;">
                    <h3 style="font-size: 24px; font-weight: 700; color: #333; margin-bottom: 20px;">{{ __('Description') }}</h3>
                    <p style="white-space: pre-line;">{{ $recipe->localized_description }}</p>
                </div>

            </div>
        </div>

        @if($latestRecipes->count() > 0)
        <div class="related-recipes" style="margin-top: 80px; padding-top: 60px; border-top: 1px solid #eee;">
            <h3 style="font-size: 28px; font-weight: 800; color: #333; text-align: center; margin-bottom: 40px;">{{ __('Other Recipes') }}</h3>
            
            <div class="grid-recipes" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};">
                @foreach($latestRecipes as $latRecipe)
                    <div style="border: 1px solid #eee; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: transform 0.3s ease;">
                        <div style="position: relative; height: 220px;">
                            @if($latRecipe->image)
                                <img src="{{ asset('uploaded_files/recipes/' . $latRecipe->image) }}" alt="{{ $latRecipe->localized_title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <img src="{{ asset('assets/images/placeholder.png') }}" alt="{{ $latRecipe->localized_title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @endif
                            <div style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.9); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #333; {{ app()->getLocale() == 'en' ? 'right: auto; left: 15px;' : '' }}">
                                @if($latRecipe->difficulty == 'easy')
                                    {{ __('Easy') }}
                                @elseif($latRecipe->difficulty == 'medium')
                                    {{ __('Medium') }}
                                @else
                                    {{ __('Hard') }}
                                @endif
                                &bull; <i class="far fa-clock" style="color: #e32636;"></i> {{ $latRecipe->time_to_cook }} {{ __('minutes') }}
                            </div>
                        </div>
                        <div style="padding: 25px;">
                            <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700; color: #333;">{{ $latRecipe->localized_title }}</h3>
                            <a href="{{ route('front.v2.recipe.details', $latRecipe->slug) }}" style="color: #e32636; text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">{{ __('View Recipe') }} <i class="fas fa-chevron-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}" style="font-size:10px;"></i></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
