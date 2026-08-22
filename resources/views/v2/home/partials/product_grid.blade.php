<style>
    .store-product-grid {
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); 
        gap: 20px;
    }
    @media (max-width: 576px) {
        .store-product-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        /* Make mobile card contents slightly smaller to fit 2 cols nicely */
        .store-product-grid .product-card {
            padding: 10px !important;
        }
        .store-product-grid .product-card h3 {
            font-size: 13px !important;
        }
        .store-product-grid .product-card img {
            height: 140px !important;
        }
        .store-product-grid .product-card button {
            padding: 8px !important;
            font-size: 12px !important;
        }
        .store-product-grid .product-card .price {
            font-size: 14px !important;
        }
    }
</style>
<div class="store-product-grid">
    @forelse($products as $product)
    <div class="product-card" style="background:#fff; border: 1px solid var(--border-color); border-radius:16px; padding:12px; transition: 0.3s; position: relative; display: flex; flex-direction: column; height: 100%;">
        
        <!-- Wishlist Icon -->
        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist('{{ $product->id }}', this)" style="position: absolute; top: 20px; left: 20px; width: 32px; height: 32px; background: rgba(255,255,255,0.9); border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 2; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <i class="far fa-heart" style="font-size: 15px; color: #555;"></i>
        </button>

        <a href="{{ route('front.product_details', $product->en_Product_Slug ?: $product->id) }}" style="display:block; position: relative; margin-bottom: 12px; flex-shrink: 0;">
            <img src="{{ asset($product->Primary_Image && $product->Primary_Image != 'default.png' ? ProductImage().$product->Primary_Image : 'assets/images/placeholder.png') }}" alt="{{ $product->fr_Product_Name ?? $product->en_Product_Name }}" style="width:100%; height:160px; object-fit:cover; border-radius:12px;">
        </a>
        
        <div style="flex-grow: 1; display: flex; flex-direction: column;">
            <!-- Badge & Rating Row -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <!-- Stock Status Badge -->
                @if($product->Quantity <= 0)
                <span style="background: #ffebee; color: #e32636; padding: 3px 8px; border-radius: 20px; font-size: 10px; font-weight: 700;">
                    @lang('v2_store.out_of_stock')
                </span>
                @elseif($product->Quantity > 0 && $product->Quantity <= 5)
                <span style="background: #fff3e0; color: #e65100; padding: 3px 8px; border-radius: 20px; font-size: 10px; font-weight: 700;">
                    @lang('v2_store.limited')
                </span>
                @else
                <span style="background: #e8f5e9; color: #2e7d32; padding: 3px 8px; border-radius: 20px; font-size: 10px; font-weight: 700;">
                    @lang('v2_store.in_stock')
                </span>
                @endif

                <!-- Rating -->
                <div style="display: flex; align-items: center; gap: 3px; font-size: 11px; font-weight: 600; color: #555;">
                    <span style="color: #999; font-weight: normal;">(1.4K)</span>
                    <i class="fas fa-star" style="color: #e32636; font-size: 10px;"></i>
                    <span>(4.5)</span>
                </div>
            </div>

            <!-- Title & Description -->
            <h3 style="font-size:14px; font-weight: 800; margin:0 0 4px; line-height: 1.4;"><a href="{{ route('front.product_details', $product->en_Product_Slug ?: $product->id) }}" style="color:var(--text-color); text-decoration:none;">{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? ($product->fr_Product_Name ?? $product->en_Product_Name) : $product->en_Product_Name }}</a></h3>
            <p style="margin: 0; color: var(--text-light); font-size: 11px; line-height: 1.4;">{{ Str::limit(app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? ($product->fr_About ?? trans('v2_store.no_description')) : ($product->en_About ?? trans('v2_store.no_description')), 40) }}</p>
        </div>

        <!-- Fixed height bottom section for Price and Button -->
        <div style="margin-top: auto; padding-top: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                @if($product->Discount_Price > 0 && $product->Discount_Price < $product->Price)
                    <div style="font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 4px; color: #333;">
                        {{ $product->Discount_Price }} @lang('v2_layout.currency_omr')
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="text-decoration: line-through; color: var(--text-light); font-size: 11px;">{{ $product->Price }}</span>
                        <span style="color: #4caf50; font-weight: 700; font-size: 11px;">{{ round((($product->Price - $product->Discount_Price) / $product->Price) * 100) }}% <i class="fas fa-arrow-down" style="font-size: 8px;"></i></span>
                    </div>
                @else
                    <div style="font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 4px; color: #333;">
                        {{ $product->Price ?? '0.00' }} @lang('v2_layout.currency_omr')
                    </div>
                @endif
            </div>

            <button type="button" onclick="event.preventDefault(); event.stopPropagation(); addToCart('{{$product->id}}', '{{ $product->Discount_Price > 0 ? $product->Discount_Price : $product->Price }}')" style="width:100%; background-color: var(--primary-color); color: white; border: none; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; transition: 0.2s; font-family: inherit;">
                @lang('v2_home.add_to_cart')
            </button>
        </div>
    </div>
    @empty
    <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: var(--white); border-radius: 12px; border: 1px solid var(--border-color);">
        <i class="fas fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
        <p style="font-size: 16px; color: var(--text-light);">@lang('v2_store.no_products')</p>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($products->hasPages())
<div style="margin-top: 40px; display: flex; justify-content: center;" class="v2-pagination">
    {{ $products->appends(request()->query())->links() }}
</div>
<style>
    /* Basic styling for laravel paginator */
    .v2-pagination nav { display: flex; align-items: center; gap: 10px; }
    .v2-pagination span.relative, .v2-pagination a.relative { padding: 8px 15px; border-radius: 8px; border: 1px solid var(--border-color); color: var(--text-color); text-decoration: none; font-weight: 600; background: #fff; }
    .v2-pagination span[aria-current="page"] .relative { background: var(--primary-color); color: #fff; border-color: var(--primary-color); }
    .v2-pagination svg { width: 20px; height: 20px; }
    .v2-pagination .hidden { display: none; }
</style>
@endif
