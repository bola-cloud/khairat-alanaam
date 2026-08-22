@extends('v2.layouts.app')

@section('title', trans('v2_store.store_title'))

@section('content')
<div class="v2-container" style="padding: 40px 15px;">
    
    <!-- Breadcrumb -->
    <nav style="font-size: 13px; margin-bottom: 30px; color: var(--text-light);">
        <a href="{{ route('front') }}" style="color: var(--text-light); text-decoration: none;">@lang('v2_layout.nav_home')</a> 
        <span style="margin: 0 5px;">/</span> 
        <a href="#" style="color: var(--text-light); text-decoration: none;">@lang('v2_layout.nav_categories')</a>
    </nav>

    <form method="GET" action="{{ route('front.store') }}" id="filter-form">
        @if(request()->has('q'))
            <input type="hidden" name="q" value="{{ request('q') }}">
        @endif
        
        <!-- Top Bar (Desktop & Mobile Adaptive) -->
        <div class="store-top-bar">
            <div class="store-title-box">
                <h1 class="store-title">@lang('v2_store.all_products')</h1>
                <span class="store-count">@lang('v2_store.product_count', ['count' => $products->total()])</span>
            </div>
            
            <div class="store-actions">
                <div style="position: relative; flex: 1;">
                    <select name="sort" class="store-sort-select" style="appearance: none; -webkit-appearance: none;">
                        <option value="">@lang('v2_store.sort_default')</option>
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>@lang('v2_store.sort_latest')</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>@lang('v2_store.sort_price_asc')</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>@lang('v2_store.sort_price_desc')</option>
                    </select>
                    <i class="fas fa-chevron-down" style="position: absolute; {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'left' : 'right' }}: 15px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #888; font-size: 12px;"></i>
                </div>
                
                <!-- Mobile Filter Button -->
                <button type="button" class="mobile-filter-btn" onclick="toggleMobileFilter()">
                    @lang('v2_store.filter_sort') <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>

        <!-- MAIN GRID CONTAINER -->
        <div style="display: flex; gap: 30px; flex-wrap: wrap; position: relative;">
            
            <!-- Sidebar Filters -->
            <div class="mobile-filter-overlay" onclick="toggleMobileFilter()"></div>
            <aside class="filter-sidebar">
                <div class="mobile-filter-close" onclick="toggleMobileFilter()">
                    <i class="fas fa-times"></i>
                </div>
                <div style="background: #fafafa; padding: 25px; border-radius: 12px; border: 1px solid #eee; overflow: hidden;">
                    
                    <!-- Categories -->
                    <div style="margin-bottom: 25px;">
                        <h3 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 700; color: #333;">@lang('v2_layout.nav_categories')</h3>
                        @php $catFilter = request('category', []); @endphp
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach($categories as $category)
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 14px; color: #555;">
                                <input type="checkbox" name="category[]" value="{{ $category->id }}"  {{ in_array($category->id, $catFilter) ? 'checked' : '' }} style="accent-color: #e32636; width: 18px; height: 18px; flex-shrink: 0;"> 
                                <span>{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $category->fr_Category_Name : $category->en_Category_Name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <hr style="border: none; border-top: 1px solid #eee; margin: 0 0 25px 0;">

                    <!-- Availability -->
                    <div style="margin-bottom: 25px;">
                        <h3 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 700; color: #333;">@lang('v2_store.availability')</h3>
                        @php $stockFilter = request('stock', []); @endphp
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 14px; color: #555;">
                                <input type="checkbox" name="stock[]" value="in_stock"  {{ in_array('in_stock', $stockFilter) ? 'checked' : '' }} style="accent-color: #e32636; width: 18px; height: 18px; flex-shrink: 0;"> 
                                <span>@lang('v2_store.in_stock')</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 14px; color: #555;">
                                <input type="checkbox" name="stock[]" value="out_of_stock"  {{ in_array('out_of_stock', $stockFilter) ? 'checked' : '' }} style="accent-color: #e32636; width: 18px; height: 18px; flex-shrink: 0;"> 
                                <span>@lang('v2_store.out_of_stock')</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 14px; color: #555;">
                                <input type="checkbox" name="stock[]" value="limited"  {{ in_array('limited', $stockFilter) ? 'checked' : '' }} style="accent-color: #e32636; width: 18px; height: 18px; flex-shrink: 0;"> 
                                <span>@lang('v2_store.limited')</span>
                            </label>
                        </div>
                    </div>

                    <hr style="border: none; border-top: 1px solid #eee; margin: 0 0 25px 0;">

                    <!-- Price -->
                    <div style="margin-bottom: 25px;">
                        <h3 style="margin: 0 0 25px 0; font-size: 16px; font-weight: 700; color: #333;">@lang('v2_store.price')</h3>

                        <!-- Custom Dual Range Slider for RTL -->
                        <div class="price-slider-container" style="position: relative; height: 3px; background: #ddd; margin: 10px 10px 30px;">
                            <div class="slider-track" id="slider-track" style="position: absolute; height: 100%; background: #e32636;"></div>
                            <input type="range" min="0" max="2000" value="{{ request('price_min', 0) }}" id="slider-1" oninput="slideOne()"  style="position: absolute; width: 100%; appearance: none; background: none; pointer-events: none; top: -8px; left: 0;">
                            <input type="range" min="0" max="2000" value="{{ request('price_max', 2000) }}" id="slider-2" oninput="slideTwo()"  style="position: absolute; width: 100%; appearance: none; background: none; pointer-events: none; top: -8px; left: 0;">
                        </div>
                        
                        <style>
                            .price-slider-container input[type="range"]::-webkit-slider-thumb {
                                appearance: none; pointer-events: auto; width: 18px; height: 18px; background: #fff; border: 2px solid #e32636; border-radius: 50%; cursor: pointer;
                            }
                            .price-slider-container input[type="range"]::-moz-range-thumb {
                                appearance: none; pointer-events: auto; width: 18px; height: 18px; background: #fff; border: 2px solid #e32636; border-radius: 50%; cursor: pointer;
                            }
                        </style>

                        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 20px;">
                            <div style="flex: 1; position: relative;">
                                <i class="fas fa-box" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #888; font-size: 12px;"></i>
                                <input type="number" id="display_price_max" name="price_max" value="{{ request('price_max', 2000) }}" onchange="document.getElementById('slider-2').value=this.value; slideTwo();" style="width: 100%; padding: 10px 10px 10px 30px; border: 1px solid #eee; border-radius: 8px; box-sizing: border-box; text-align: center; font-family: inherit; font-size: 13px; color: #555; background: #fff;">
                            </div>
                            <div style="flex: 1; position: relative;">
                                <i class="fas fa-box" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #888; font-size: 12px;"></i>
                                <input type="number" id="display_price_min" name="price_min" value="{{ request('price_min', 0) }}" onchange="document.getElementById('slider-1').value=this.value; slideOne();" style="width: 100%; padding: 10px 10px 10px 30px; border: 1px solid #eee; border-radius: 8px; box-sizing: border-box; text-align: center; font-family: inherit; font-size: 13px; color: #555; background: #fff;">
                            </div>
                        </div>

                        <!-- Predefined Price Ranges -->
                        @php $priceRange = request('price_range', ''); @endphp
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; color: #555; white-space: nowrap;">
                                <input type="radio" name="price_range" value=""  {{ $priceRange == '' ? 'checked' : '' }} style="accent-color: #e32636; width: 14px; height: 14px; flex-shrink: 0;"> 
                                <span>@lang('v2_store.all_prices')</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; color: #555; white-space: nowrap;">
                                <input type="radio" name="price_range" value="0-20"  {{ $priceRange == '0-20' ? 'checked' : '' }} style="accent-color: #e32636; width: 14px; height: 14px; flex-shrink: 0;"> 
                                <span dir="ltr"> < 20</span> <i class="fas fa-box" style="font-size:9px; color:#888;"></i>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; color: #555; white-space: nowrap;">
                                <input type="radio" name="price_range" value="100-200"  {{ $priceRange == '100-200' ? 'checked' : '' }} style="accent-color: #e32636; width: 14px; height: 14px; flex-shrink: 0;"> 
                                <span dir="ltr">100 - 200</span> <i class="fas fa-box" style="font-size:9px; color:#888;"></i>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; color: #555; white-space: nowrap;">
                                <input type="radio" name="price_range" value="200-500"  {{ $priceRange == '200-500' ? 'checked' : '' }} style="accent-color: #e32636; width: 14px; height: 14px; flex-shrink: 0;"> 
                                <span dir="ltr">200 - 500</span> <i class="fas fa-box" style="font-size:9px; color:#888;"></i>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; color: #555; white-space: nowrap;">
                                <input type="radio" name="price_range" value="500-1000"  {{ $priceRange == '500-1000' ? 'checked' : '' }} style="accent-color: #e32636; width: 14px; height: 14px; flex-shrink: 0;"> 
                                <span dir="ltr">500 - 1000</span> <i class="fas fa-box" style="font-size:9px; color:#888;"></i>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; color: #555; white-space: nowrap;">
                                <input type="radio" name="price_range" value="1000-2000"  {{ $priceRange == '1000-2000' ? 'checked' : '' }} style="accent-color: #e32636; width: 14px; height: 14px; flex-shrink: 0;"> 
                                <span dir="ltr">1000+</span> <i class="fas fa-box" style="font-size:9px; color:#888;"></i>
                            </label>
                        </div>

                        <script>
                            window.onload = function() { slideOne(); slideTwo(); };
                            let sliderOne = document.getElementById("slider-1");
                            let sliderTwo = document.getElementById("slider-2");
                            let displayValOne = document.getElementById("display_price_min");
                            let displayValTwo = document.getElementById("display_price_max");
                            let minGap = 10;
                            let sliderTrack = document.getElementById("slider-track");
                            let sliderMaxValue = sliderOne.max;

                            function slideOne() {
                                if (parseInt(sliderTwo.value) - parseInt(sliderOne.value) <= minGap) {
                                    sliderOne.value = parseInt(sliderTwo.value) - minGap;
                                }
                                displayValOne.value = sliderOne.value;
                                fillColor();
                            }
                            function slideTwo() {
                                if (parseInt(sliderTwo.value) - parseInt(sliderOne.value) <= minGap) {
                                    sliderTwo.value = parseInt(sliderOne.value) + minGap;
                                }
                                displayValTwo.value = sliderTwo.value;
                                fillColor();
                            }
                            function fillColor() {
                                let percent1 = (sliderOne.value / sliderMaxValue) * 100;
                                let percent2 = (sliderTwo.value / sliderMaxValue) * 100;
                                // LTR Logic ensures 0 is on the left and max is on the right, which feels more natural for numbers even in Arabic
                                sliderTrack.style.left = percent1 + "%";
                                sliderTrack.style.width = (percent2 - percent1) + "%";
                                sliderTrack.style.right = "auto";
                            }
                        </script>
                    </div>

                    <hr style="border: none; border-top: 1px solid #eee; margin: 0 0 25px 0;">

                    <!-- Cut Style (نمط القص) - Using Subcategories -->
                    <div style="margin-bottom: 10px;">
                        <h3 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 700; color: #333;">@lang('v2_store.cut_style')</h3>
                        @php $subcatFilter = request('subcategory', []); @endphp
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            @foreach($subcategories as $subcat)
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: #555; white-space: nowrap;">
                                <input type="checkbox" name="subcategory[]" value="{{ $subcat->id }}"  {{ in_array($subcat->id, $subcatFilter) ? 'checked' : '' }} style="accent-color: #e32636; width: 16px; height: 16px; flex-shrink: 0;"> 
                                <span>{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $subcat->name_ar : $subcat->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                </div>
            </aside>

            <!-- Product Grid (Now placed SECOND so it appears on the LEFT in RTL) -->
            <main style="flex: 1; min-width: 0;">
                <div id="product-grid-container">
                    @include('v2.home.partials.product_grid', ['products' => $products])
                </div>
            </main>

        </div>
    </form>
</div>

<style>
    /* Mobile Filter Styles & Top Bar Adapative */
    .store-top-bar {
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;
    }
    .store-title-box {
        display: flex; align-items: baseline; gap: 10px;
    }
    .store-title {
        margin: 0; font-size: 28px; font-weight: 800;
    }
    .store-count {
        color: var(--text-light); font-size: 16px; white-space: nowrap;
    }
    .store-actions {
        display: flex; gap: 15px; align-items: center;
    }
    .store-sort-select {
        width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; background: #f9f9f9; font-family: inherit; font-size: 14px; min-width: 180px; cursor: pointer; color: #555; font-weight: 600;
    }
    
    .filter-sidebar {
        width: 280px;
        flex-shrink: 0;
        max-width: 100%;
    }
    .mobile-filter-btn {
        display: none; /* Hidden on desktop */
    }
    .mobile-filter-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    }
    .mobile-filter-close {
        display: none;
    }

    @media (max-width: 991px) {
        .store-top-bar {
            flex-direction: column; align-items: flex-start; gap: 15px;
        }
        .store-actions {
            width: 100%;
            justify-content: space-between;
        }
        .store-sort-select {
            min-width: unset;
        }
        .mobile-filter-btn {
            display: flex !important;
            flex: 1;
            align-items: center;
            justify-content: space-between;
            background: #f9f9f9 !important;
            color: #555 !important;
            border: 1px solid var(--border-color) !important;
            padding: 12px 15px !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            font-weight: 600;
            margin: 0; /* Remove previous margins */
        }
        .store-title { font-size: 20px; }
        .store-count { font-size: 14px; }
        
        aside.filter-sidebar {
            position: fixed;
            bottom: -100%; /* Start off-screen */
            left: 0;
            width: 100% !important;
            height: 85vh;
            background: #fff;
            z-index: 1000;
            transition: 0.3s ease-in-out;
            border-radius: 24px 24px 0 0;
            overflow-y: auto;
            padding-top: 60px !important; /* Space for close button */
        }
        aside.filter-sidebar.active {
            bottom: 0;
        }
        .mobile-filter-overlay.active {
            display: block;
        }
        .mobile-filter-close {
            display: flex;
            position: absolute;
            top: 15px;
            right: 20px;
            width: 35px;
            height: 35px;
            background: #f0f0f0;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
        }
    }
    
    /* Loading overlay */
    #product-grid-container {
        transition: opacity 0.3s ease;
    }
    #product-grid-container.loading {
        /* Removed opacity reduction to prevent flicker that feels like a page reload */
        pointer-events: none;
    }
</style>

@push('scripts')
<script>
    function toggleMobileFilter() {
        $('.filter-sidebar, .mobile-filter-overlay').toggleClass('active');
    }

    // AJAX Filtering Logic
    $(document).ready(function() {
        let fetchTimer;

        // Listen for changes on the form inputs
        $('#filter-form').on('change', 'input, select', function(e) {
            e.preventDefault();
            clearTimeout(fetchTimer);
            fetchTimer = setTimeout(fetchProducts, 300); // Debounce
        });

        // Listen for slide changes on range inputs (debounced)
        $('#slider-1, #slider-2').on('input', function(e) {
            clearTimeout(fetchTimer);
            fetchTimer = setTimeout(fetchProducts, 800);
        });

        // Handle AJAX Pagination
        $(document).on('click', '.v2-pagination a', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            fetchProducts(url);
        });

        function fetchProducts(customUrl = null) {
            var form = $('#filter-form');
            var url = customUrl || form.attr('action');
            var data = form.serialize();

            // Update URL in browser history without reloading
            let newUrl = url.split('?')[0] + '?' + data;
            window.history.pushState({path: newUrl}, '', newUrl);

            $.ajax({
                url: url,
                data: customUrl ? null : data,
                type: 'GET',
                beforeSend: function() {
                    $('#product-grid-container').addClass('loading');
                },
                success: function(response) {
                    $('#product-grid-container').html(response);
                    $('#product-grid-container').removeClass('loading');
                },
                error: function(xhr) {
                    console.error("Error fetching products:", xhr);
                    $('#product-grid-container').removeClass('loading');
                }
            });
        }
    });
</script>
@endpush
@endsection
