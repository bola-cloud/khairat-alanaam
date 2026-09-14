@extends('v2.layouts.app')

@php
    $lang = app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'fr' : 'en';
@endphp

@section('title', __('v2_store.store_title') . ' - المفضلة')

@section('content')
<div class="v2-container" style="padding: 40px 15px;">
    
    <!-- Breadcrumb -->
    <nav style="font-size: 14px; margin-bottom: 30px; color: var(--text-light);">
        <a href="{{ route('front') }}" style="color: var(--text-color); text-decoration: none;">@lang('v2_product.home')</a> 
        <span style="margin: 0 5px;">/</span> 
        <span>@lang('v2_home.favorites_title')</span>
    </nav>

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 28px; font-weight: 800; color: #333;">@lang('v2_home.favorites_title') <span style="font-size: 16px; color: var(--text-light); font-weight: normal;">({{ $wishlists->count() }} @lang('v2_home.products_count'))</span></h1>
        
        <div style="display: flex; gap: 15px;">
            <!-- Sort By -->
            <select style="padding: 10px 15px; border-radius: 8px; border: 1px solid var(--border-color); background: #f9f9f9; color: var(--text-color); outline: none; font-family: inherit; font-size: 14px; min-width: 150px;">
                <option value="">@lang('v2_store.sort_default')</option>
                <option value="latest">@lang('v2_store.sort_latest')</option>
                <option value="price_asc">@lang('v2_store.sort_price_asc')</option>
                <option value="price_desc">@lang('v2_store.sort_price_desc')</option>
            </select>
        </div>
    </div>

    @if($wishlists->count() > 0)
    <div class="grid-products" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));">
        @foreach($wishlists as $wishlist)
            @php
                $product = $wishlist->product;
                if(!$product) continue;
                
                $productName = $lang == 'fr' ? $product->fr_Product_Name : $product->en_Product_Name;
                $productName = $productName ?: $product->en_Product_Name;
                $productAbout = $lang == 'fr' ? $product->fr_About : $product->en_About;
                
                $discountAmount = $product->Discount ?? 0;
                $hasDiscount = $discountAmount > 0;
                
                $finalPrice = $product->Price;
                if ($hasDiscount) {
                    if (strpos($discountAmount, '%') !== false) {
                        $percent = (float) str_replace('%', '', $discountAmount);
                        $finalPrice = $product->Price - ($product->Price * ($percent / 100));
                    } else {
                        $finalPrice = $product->Price - (float) $discountAmount;
                    }
                }
            @endphp
            <div class="product-card" style="background:#fff; border: 1px solid var(--border-color); border-radius:12px; padding:15px; transition: 0.3s; position: relative; display: flex; flex-direction: column; height: 100%;">
                
                <!-- Heart Icon (Red) -->
                <button type="button" onclick="removeFromFavorites({{ $wishlist->id }}, this)" style="position: absolute; top: 25px; {{ $lang == 'fr' ? 'right' : 'left' }}: 25px; width: 35px; height: 35px; background: #fff; border: 1px solid #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 3; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <i class="fas fa-heart" style="font-size: 16px; color: #e32636;"></i>
                </button>

                <!-- Discount Badge -->
                @if($hasDiscount)
                <div style="position: absolute; top: 15px; {{ $lang == 'fr' ? 'left' : 'right' }}: 15px; background: var(--primary-color); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; z-index: 2;">
                    {{ $discountAmount }}{{ strpos($discountAmount, '%') === false ? '%' : '' }} خصم
                </div>
                @endif
                
                <!-- Stock Status Badge -->
                @if($product->Quantity <= 0)
                <span style="position: absolute; top: 190px; {{ $lang == 'fr' ? 'left' : 'right' }}: 25px; background: #e32636; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; z-index: 2;">
                    @lang('v2_store.out_of_stock')
                </span>
                @elseif($product->Quantity > 0 && $product->Quantity <= 5)
                <span style="position: absolute; top: 190px; {{ $lang == 'fr' ? 'left' : 'right' }}: 25px; background: #f59e0b; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; z-index: 2;">
                    @lang('v2_store.limited')
                </span>
                @else
                <span style="position: absolute; top: 190px; {{ $lang == 'fr' ? 'left' : 'right' }}: 25px; background: rgba(240,240,240,0.9); color: #555; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; z-index: 2;">
                    @lang('v2_store.in_stock')
                </span>
                @endif

                <a href="{{ route('front.product_details', $product->en_Product_Slug ?: $product->id) }}" style="display:block; position: relative; margin-bottom: 15px; flex-shrink: 0;">
                    <img src="{{ $product->Primary_Image && $product->Primary_Image != 'default.png' ? asset(ProductImage() . $product->Primary_Image) : asset('assets/images/placeholder.png') }}" alt="{{ $productName }}" style="width:100%; height:200px; object-fit:cover; border-radius:12px;">
                </a>
                
                <div style="flex-grow: 1; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px;">
                        <div style="padding-left: 10px;">
                            <h3 style="font-size:16px; font-weight: 800; margin:0 0 5px; line-height: 1.4;"><a href="{{ route('front.product_details', $product->en_Product_Slug ?: $product->id) }}" style="color:var(--text-color); text-decoration:none;">{{ $productName }}</a></h3>
                            <p style="margin: 0; color: var(--text-light); font-size: 12px; line-height: 1.4;">{{ Str::limit($productAbout ?: trans('v2_store.no_description'), 50) }}</p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; flex-shrink: 0; margin-top: 2px;">
                            <span>(4.5)</span>
                            <i class="fas fa-star" style="color: #e32636; font-size: 11px;"></i>
                            <span style="color: var(--text-light); font-weight: normal;">( 1.4K )</span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: auto; padding-top: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        @if($hasDiscount)
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="text-decoration: line-through; color: var(--text-light); font-size: 12px;">{{ number_format($product->Price, 3) }} @lang('v2_layout.currency_omr')</span>
                            </div>
                        @else
                            <div></div>
                        @endif
                        <div style="font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 5px; color: var(--primary-color);">
                            {{ number_format($finalPrice, 3) }} @lang('v2_layout.currency_omr')
                        </div>
                    </div>
                    
                    <form action="{{ route('front.cart') }}" method="GET">
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" style="width:100%; padding: 10px; background: var(--primary-color); color:#fff; border:none; border-radius:8px; font-weight: 700; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                            @lang('v2_home.add_to_cart')
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    @else
    <div style="text-align: center; padding: 100px 20px; background: #fff; border-radius: 12px; border: 1px solid var(--border-color);">
        <i class="far fa-heart" style="font-size: 60px; color: #ddd; margin-bottom: 20px;"></i>
        <h3 style="font-size: 24px; font-weight: 800; color: #333; margin-bottom: 10px;">@lang('v2_home.favorites_empty_title')</h3>
        <p style="color: var(--text-light); margin-bottom: 30px;">@lang('v2_home.favorites_empty_desc')</p>
        <a href="{{ route('front.store') }}" class="btn-primary" style="padding: 12px 30px; font-size: 16px;">@lang('v2_home.browse_store')</a>
    </div>
    @endif
</div>

<script>
    function removeFromFavorites(wishlistId, btn) {
        if(confirm('{{ __("v2_home.confirm_remove_fav") }}')) {
            fetch("{{ route('wishlist.delete') }}?id=" + wishlistId, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(res => res.text()).then(data => {
                if(data) {
                    // Remove card from UI
                    btn.closest('.product-card').remove();
                }
            });
        }
    }
</script>
@endsection
