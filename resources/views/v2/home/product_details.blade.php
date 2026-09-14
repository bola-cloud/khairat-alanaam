@extends('v2.layouts.app')

@php
    $lang = app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? 'fr' : 'en';
    $productName = $lang == 'fr' ? $product->fr_Product_Name : $product->en_Product_Name;
    $productDesc = $lang == 'fr' ? $product->fr_Description : $product->en_Description;
    $productAdditional = $lang == 'fr' ? $product->fr_AdditionalInformation : $product->en_AdditionalInformation;
    
    // Fallbacks
    $productName = $productName ?: $product->en_Product_Name;
    $productDesc = $productDesc ?: $product->en_Description;
    $productAdditional = $productAdditional ?: $product->en_AdditionalInformation;
    
    $catName = $product->category ? ($lang == 'fr' ? $product->category->fr_Category_Name : $product->category->en_Category_Name) : '';
    $catName = $catName ?: ($product->category ? $product->category->en_Category_Name : '');
    
    $finalPrice = ($product->Discount_Price > 0 && $product->Discount_Price < $product->Price) ? (float)$product->Discount_Price : (float)$product->Price;
    $hasDiscount = $finalPrice < $product->Price;
    $discountPercent = $hasDiscount ? round((($product->Price - $finalPrice) / $product->Price) * 100) : 0;
@endphp

@section('title', $productName . ' - ' . __('v2_product.home'))

@section('content')
<div class="v2-container" style="padding: 40px 15px;">
    
    <!-- Breadcrumb -->
    <nav style="font-size: 14px; margin-bottom: 30px; color: var(--text-light);">
        <a href="{{ route('front') }}" style="color: var(--text-color); text-decoration: none;">@lang('v2_product.home')</a> 
        <span style="margin: 0 5px;">/</span> 
        <a href="{{ route('front.store') }}?category[]={{ $product->category->id ?? '' }}" style="color: var(--text-color); text-decoration: none;">{{ $catName }}</a>
        <span style="margin: 0 5px;">/</span> 
        <span>{{ $productName }}</span>
    </nav>

    <div style="display: flex; gap: 40px; flex-wrap: wrap;">
        
        <!-- Product Image Gallery -->
        <div style="flex: 1; min-width: 300px;">
            <div style="position: relative; background: var(--white); border-radius: 12px; padding: 20px; border: 1px solid var(--border-color); margin-bottom: 15px;">
                @if($hasDiscount)
                <div style="position: absolute; top: 15px; {{ $lang == 'fr' ? 'right' : 'left' }}: 15px; background: var(--primary-color); color: #fff; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 700; z-index: 2;">
                    {{ $discountPercent }}% خصم
                </div>
                @endif
                <img id="main-product-image" src="{{ asset($product->Primary_Image && $product->Primary_Image != 'default.png' ? ProductImage() . $product->Primary_Image : 'assets/images/placeholder.png') }}" alt="{{ $productName }}" style="width: 100%; height: auto; border-radius: 8px; max-height: 500px; object-fit: contain;">
            </div>
            <div class="gallery-thumbnails" style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 10px;">
                @if($product->Primary_Image && $product->Primary_Image != 'default.png')
                <div class="thumbnail active" onclick="changeMainImage(this, '{{ asset(ProductImage() . $product->Primary_Image) }}')" style="width: 80px; height: 80px; flex-shrink: 0; background: var(--white); border: 2px solid var(--primary-color); border-radius: 8px; padding: 5px; cursor: pointer;">
                    <img src="{{ asset(ProductImage() . $product->Primary_Image) }}" style="width:100%; height:100%; object-fit:cover; border-radius:4px;">
                </div>
                @endif
                @if($product->Image2 && $product->Image2 != 'default.png')
                <div class="thumbnail" onclick="changeMainImage(this, '{{ asset(ProductImage() . $product->Image2) }}')" style="width: 80px; height: 80px; flex-shrink: 0; background: var(--white); border: 1px solid var(--border-color); border-radius: 8px; padding: 5px; cursor: pointer;">
                    <img src="{{ asset(ProductImage() . $product->Image2) }}" style="width:100%; height:100%; object-fit:cover; border-radius:4px;">
                </div>
                @endif
                @if($product->Image3 && $product->Image3 != 'default.png')
                <div class="thumbnail" onclick="changeMainImage(this, '{{ asset(ProductImage() . $product->Image3) }}')" style="width: 80px; height: 80px; flex-shrink: 0; background: var(--white); border: 1px solid var(--border-color); border-radius: 8px; padding: 5px; cursor: pointer;">
                    <img src="{{ asset(ProductImage() . $product->Image3) }}" style="width:100%; height:100%; object-fit:cover; border-radius:4px;">
                </div>
                @endif
                @if($product->Image4 && $product->Image4 != 'default.png')
                <div class="thumbnail" onclick="changeMainImage(this, '{{ asset(ProductImage() . $product->Image4) }}')" style="width: 80px; height: 80px; flex-shrink: 0; background: var(--white); border: 1px solid var(--border-color); border-radius: 8px; padding: 5px; cursor: pointer;">
                    <img src="{{ asset(ProductImage() . $product->Image4) }}" style="width:100%; height:100%; object-fit:cover; border-radius:4px;">
                </div>
                @endif
                @if($product->Image5 && $product->Image5 != 'default.png')
                <div class="thumbnail" onclick="changeMainImage(this, '{{ asset(ProductImage() . $product->Image5) }}')" style="width: 80px; height: 80px; flex-shrink: 0; background: var(--white); border: 1px solid var(--border-color); border-radius: 8px; padding: 5px; cursor: pointer;">
                    <img src="{{ asset(ProductImage() . $product->Image5) }}" style="width:100%; height:100%; object-fit:cover; border-radius:4px;">
                </div>
                @endif
            </div>
            
            <script>
                function changeMainImage(element, src) {
                    document.getElementById('main-product-image').src = src;
                    document.querySelectorAll('.thumbnail').forEach(el => {
                        el.style.border = '1px solid var(--border-color)';
                    });
                    element.style.border = '2px solid var(--primary-color)';
                }
            </script>
        </div>

        <!-- Product Info -->
        <div style="flex: 1.5; min-width: 300px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                <h1 style="font-size: 32px; font-weight: 800; margin-top: 0; line-height: 1.3; flex: 1;">{{ $productName }}</h1>
                <div style="display: flex; align-items: center; gap: 5px; padding-top: 5px; text-align: {{ $lang == 'fr' ? 'left' : 'right' }};">
                    <span style="font-size: 13px; color: var(--text-light); font-weight: 600;">{{ str_replace(':count', $reviewsCount, __('v2_product.reviews_count')) }}</span>
                    <div style="color: #f1c40f; font-size: 12px; display: flex; gap: 2px;">
                        @for($i=1; $i<=5; $i++)
                            @if($i <= $avgRating)
                                <i class="fas fa-star"></i>
                            @elseif($i - 0.5 <= $avgRating)
                                <i class="fas fa-star-half-alt"></i>
                            @else
                                <i class="far fa-star"></i>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>

            <p style="color: var(--text-light); line-height: 1.8; margin-bottom: 25px; font-size: 15px;">
                {{ strip_tags(html_entity_decode($productDesc)) }}
            </p>

            <div style="display: flex; align-items: baseline; gap: 15px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                <div style="font-size: 32px; font-weight: 800; color: var(--primary-color);">
                    {{ number_format($finalPrice, 3) }} @lang('v2_layout.currency_omr')
                </div>
                @if($hasDiscount)
                <div style="font-size: 18px; color: var(--text-light); text-decoration: line-through;">
                    {{ number_format($product->Price, 3) }} @lang('v2_layout.currency_omr')
                </div>
                @endif
            </div>

            <form action="{{ route('add.to.cart') }}" method="POST" id="add-to-cart-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="price" value="{{ $product->Discount_Price ?: $product->Price }}">
                
                @php
                    $validSizes = $product->sizes ? $product->sizes->filter(function($s) use ($lang) {
                        $name = $lang == 'fr' ? $s->name_ar : $s->name;
                        return !empty(trim($name));
                    })->values() : collect();
                @endphp

                @if($validSizes->count() > 0)
                <!-- Sizes -->
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <strong style="font-size: 15px; color: #333;">{{ $lang == 'fr' ? 'الخيارات' : 'Options' }}</strong>
                    </div>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        @foreach($validSizes as $index => $size)
                        <label style="cursor: pointer;">
                            <input type="radio" name="size" value="{{ $size->id }}" style="display: none;" {{ $index == 0 ? 'checked' : '' }} onchange="updateOptions(this)">
                            <div class="option-box" style="padding: 10px 20px; border: {{ $index == 0 ? '2px solid var(--primary-color)' : '1px solid var(--border-color)' }}; border-radius: 8px; color: {{ $index == 0 ? 'var(--primary-color)' : 'var(--text-light)' }}; font-size: 14px; font-weight: 600; background: {{ $index == 0 ? '#fff5f5' : 'transparent' }};">
                                {{ $lang == 'fr' ? $size->name_ar : $size->name }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($product->additions && $product->additions->count() > 0)
                <!-- Spices/Additions -->
                <div style="margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <strong style="font-size: 15px; color: #333;">@lang('v2_product.spices')</strong>
                    </div>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        @foreach($product->additions as $index => $addition)
                        <label style="cursor: pointer;">
                            <input type="radio" name="addition" value="{{ $addition->id }}" style="display: none;" {{ $index == 0 ? 'checked' : '' }} onchange="updateOptions(this)">
                            <div class="option-box" style="padding: 10px 20px; border: {{ $index == 0 ? '2px solid var(--primary-color)' : '1px solid var(--border-color)' }}; border-radius: 8px; color: {{ $index == 0 ? 'var(--primary-color)' : 'var(--text-light)' }}; font-size: 14px; font-weight: 600; background: {{ $index == 0 ? '#fff5f5' : 'transparent' }};">
                                {{ $lang == 'fr' ? $addition->name_ar : $addition->name }}
                                @if($addition->price > 0)
                                    (+{{ $addition->price }})
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div style="margin-bottom: 30px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; height: 50px;">
                        <button type="button" onclick="updateQty(1)" style="background: #f5f5f5; border: none; padding: 0 20px; height: 100%; cursor: pointer; font-size: 18px; color: #333;">+</button>
                        <input type="number" id="qty-input" name="quantity" value="1" min="1" style="width: 50px; text-align: center; border: none; font-size: 16px; outline: none; height: 100%; -moz-appearance: textfield;">
                        <button type="button" onclick="updateQty(-1)" style="background: #f5f5f5; border: none; padding: 0 20px; height: 100%; cursor: pointer; font-size: 18px; color: #333;">-</button>
                    </div>
                    
                    <button type="button" onclick="submitAddToCart()" class="btn-primary" style="padding: 0 40px; font-size: 16px; flex: 1; height: 50px; display: flex; align-items: center; justify-content: center; gap: 10px; border-radius: 8px;">
                        @lang('v2_product.add_to_cart')
                    </button>
                    
                    <button type="button" onclick="toggleWishlist({{ $product->id }}, this)" style="background: var(--white); border: 1px solid var(--border-color); color: var(--text-color); padding: 0 20px; height: 50px; border-radius: 8px; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
            </form>
            
            <script>
                function submitAddToCart() {
                    let formData = $('#add-to-cart-form').serialize();
                    $.ajax({
                        url: "{{ route('add.to.cart') }}",
                        type: "POST",
                        data: formData,
                        success: function(data) {
                            openCartModal(data[0]);
                        },
                        error: function(xhr) {
                            if(xhr.responseJSON && xhr.responseJSON.error) {
                                alert(xhr.responseJSON.error);
                            } else {
                                alert('حدث خطأ، يرجى المحاولة مرة أخرى');
                            }
                        }
                    });
                }
            
                function updateOptions(input) {
                    // Reset all siblings
                    const name = input.name;
                    document.querySelectorAll('input[name="'+name+'"]').forEach(el => {
                        const box = el.nextElementSibling;
                        box.style.border = '1px solid var(--border-color)';
                        box.style.color = 'var(--text-light)';
                        box.style.background = 'transparent';
                    });
                    // Set active
                    const activeBox = input.nextElementSibling;
                    activeBox.style.border = '2px solid var(--primary-color)';
                    activeBox.style.color = 'var(--primary-color)';
                    activeBox.style.background = '#fff5f5';
                }

                function updateQty(change) {
                    const input = document.getElementById('qty-input');
                    let val = parseInt(input.value) + change;
                    if (val < 1) val = 1;
                    input.value = val;
                }
            </script>
            
            <!-- Features List (Grid style from design) -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 30px;">
                <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; text-align: center;">
                    <div style="color: var(--primary-color); font-size: 20px; margin-bottom: 8px;"><i class="fas fa-truck-fast"></i></div>
                    <div style="font-size: 12px; font-weight: 700; color: #333; margin-bottom: 3px;">@lang('v2_product.safe_delivery')</div>
                </div>
                <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; text-align: center;">
                    <div style="color: var(--primary-color); font-size: 20px; margin-bottom: 8px;"><i class="fas fa-shield-alt"></i></div>
                    <div style="font-size: 12px; font-weight: 700; color: #333; margin-bottom: 3px;">@lang('v2_product.quality_guaranteed')</div>
                </div>
                <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; text-align: center;">
                    <div style="color: var(--primary-color); font-size: 20px; margin-bottom: 8px;"><i class="fas fa-certificate"></i></div>
                    <div style="font-size: 12px; font-weight: 700; color: #333; margin-bottom: 3px;">@lang('v2_product.fresh_delivery')</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Description Tabs -->
    <div style="margin-top: 60px;">
        <div style="display: flex; border-bottom: 1px solid var(--border-color); gap: 30px;">
            <div class="tab-btn active" onclick="switchTab('desc')" style="padding: 10px 0; border-bottom: 2px solid var(--primary-color); color: var(--primary-color); font-weight: 700; cursor: pointer; font-size: 15px;">@lang('v2_product.tab_description')</div>
            @if($productAdditional)
            <div class="tab-btn" onclick="switchTab('spices')" style="padding: 10px 0; border-bottom: 2px solid transparent; color: var(--text-light); font-weight: 600; cursor: pointer; font-size: 15px;">@lang('v2_product.tab_spices')</div>
            @endif
            <div class="tab-btn" onclick="switchTab('reviews')" style="padding: 10px 0; border-bottom: 2px solid transparent; color: var(--text-light); font-weight: 600; cursor: pointer; font-size: 15px;">@lang('v2_product.tab_reviews')</div>
        </div>
        
        <div id="tab-desc" class="tab-content" style="padding: 30px 0; line-height: 1.8; color: var(--text-color);">
            <h3 style="margin-top: 0; font-size: 20px; font-weight: 800; color: #333;">{{ $productName }}</h3>
            <div style="font-size: 15px; color: #555;">
                {!! $productDesc !!}
            </div>
        </div>

        @if($productAdditional)
        <div id="tab-spices" class="tab-content" style="display: none; padding: 30px 0; line-height: 1.8; color: var(--text-color);">
            <h3 style="margin-top: 0; font-size: 20px; font-weight: 800; color: #333;">@lang('v2_product.additional_info')</h3>
            <div style="font-size: 15px; color: #555;">
                {!! $productAdditional !!}
            </div>
        </div>
        @endif

        <div id="tab-reviews" class="tab-content" style="display: none; padding: 30px 0; line-height: 1.8;">
            @if($product->product_reviews && $product->product_reviews->count() > 0)
                @foreach($product->product_reviews->where('is_visible', true) as $review)
                <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 20px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <strong style="font-size: 15px;">{{ $review->user ? $review->user->name : 'مستخدم' }}</strong>
                        <div style="color: #f1c40f; font-size: 12px;">
                            @for($i=1; $i<=5; $i++)
                                @if($i <= $review->rating)
                                    <i class="fas fa-star"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                    </div>
                    <p style="margin: 0; font-size: 14px; color: var(--text-light);">{{ $review->feedback ?? $review->review }}</p>
                </div>
                @endforeach
            @else
                <p style="color: var(--text-light);">لا توجد تقييمات بعد.</p>
            @endif

            @auth
                <div style="margin-top: 30px; background: #f9f9f9; padding: 25px; border-radius: 8px;">
                    <h4 style="margin-top: 0; font-size: 16px; font-weight: 700;">@lang('v2_product.add_review', [], app()->getLocale() == 'en' ? 'en' : 'ar')</h4>
                    <form action="{{ route('product.review.store', $product->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600;">{{ app()->getLocale() == 'en' ? 'Rating' : 'التقييم' }}</label>
                            <select name="rating" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px; outline: none; background: #fff;">
                                <option value="5">5 - {{ app()->getLocale() == 'en' ? 'Excellent' : 'ممتاز' }}</option>
                                <option value="4">4 - {{ app()->getLocale() == 'en' ? 'Very Good' : 'جيد جداً' }}</option>
                                <option value="3">3 - {{ app()->getLocale() == 'en' ? 'Good' : 'جيد' }}</option>
                                <option value="2">2 - {{ app()->getLocale() == 'en' ? 'Acceptable' : 'مقبول' }}</option>
                                <option value="1">1 - {{ app()->getLocale() == 'en' ? 'Poor' : 'سيء' }}</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600;">{{ app()->getLocale() == 'en' ? 'Review text' : 'نص التقييم' }}</label>
                            <textarea name="comment" rows="4" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px; outline: none; background: #fff;"></textarea>
                        </div>
                        
                        <button type="submit" class="btn-primary" style="padding: 10px 25px; border: none; border-radius: 5px; cursor: pointer;">{{ app()->getLocale() == 'en' ? 'Submit Review' : 'إرسال التقييم' }}</button>
                    </form>
                </div>
            @else
                <div style="margin-top: 30px; padding: 15px; background: #fff3cd; color: #856404; border-radius: 5px; font-size: 14px;">
                    {{ app()->getLocale() == 'en' ? 'You must be logged in to add a review.' : 'يجب عليك تسجيل الدخول لإضافة تقييم.' }} <a href="{{ route('login') }}" style="color: #856404; font-weight: bold; text-decoration: underline;">{{ app()->getLocale() == 'en' ? 'Login Now' : 'سجل الدخول الآن' }}</a>
                </div>
            @endauth
        </div>
    </div>
    
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('active');
                el.style.borderBottom = '2px solid transparent';
                el.style.color = 'var(--text-light)';
            });
            
            document.getElementById('tab-' + tabId).style.display = 'block';
            event.target.style.borderBottom = '2px solid var(--primary-color)';
            event.target.style.color = 'var(--primary-color)';
        }
    </script>

    <!-- Related Products -->
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
    <div style="margin-top: 60px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
            <h2 style="margin: 0; font-size: 24px; font-weight: 800; color: #333;">@lang('v2_product.you_may_also_like')</h2>
            <a href="{{ route('front.store') }}?category[]={{ $product->category->id ?? '' }}" style="color: var(--primary-color); text-decoration: none; font-weight: 600; font-size: 14px;">@lang('v2_product.view_all') <i class="fas fa-chevron-{{ $lang == 'fr' ? 'left' : 'right' }}"></i></a>
        </div>
        <div class="grid-products">
            @foreach($relatedProducts as $relProduct)
                @php
                    $relName = $lang == 'fr' ? $relProduct->fr_Product_Name : $relProduct->en_Product_Name;
                    $relName = $relName ?: $relProduct->en_Product_Name;
                    
                    $relFinalPrice = ($relProduct->Discount_Price > 0 && $relProduct->Discount_Price < $relProduct->Price) ? (float)$relProduct->Discount_Price : (float)$relProduct->Price;
                    $relHasDiscount = $relFinalPrice < $relProduct->Price;
                    $relDiscountPercent = $relHasDiscount ? round((($relProduct->Price - $relFinalPrice) / $relProduct->Price) * 100) : 0;
                @endphp
                <div class="product-card" style="border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; background: #fff; position: relative;">
                    @if($relHasDiscount)
                        <div style="position: absolute; top: 10px; {{ $lang == 'fr' ? 'right' : 'left' }}: 10px; background: var(--primary-color); color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; z-index: 2;">
                            {{ $relDiscountPercent }}% خصم
                        </div>
                    @endif
                    <a href="{{ route('front.product_details', $relProduct->en_Product_Slug ?: $relProduct->id) }}" style="display: block; position: relative; height: 200px; padding: 20px;">
                        <img src="{{ asset($relProduct->Primary_Image && $relProduct->Primary_Image != 'default.png' ? ProductImage() . $relProduct->Primary_Image : 'assets/images/placeholder.png') }}" alt="{{ $relName }}" style="width: 100%; height: 100%; object-fit: contain; transition: transform 0.3s ease;">
                    </a>
                    <div style="padding: 20px; border-top: 1px solid var(--border-color);">
                        <div style="color: #f1c40f; font-size: 12px; margin-bottom: 10px;">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                            <span style="color: var(--text-light); margin: 0 5px;">(4.5)</span>
                        </div>
                        <h4 style="margin: 0 0 10px; font-size: 16px; font-weight: 700; color: #333;">
                            <a href="{{ route('front.product_details', $relProduct->en_Product_Slug ?: $relProduct->id) }}" style="color: inherit; text-decoration: none;">{{ $relName }}</a>
                        </h4>
                        <div style="color: var(--text-light); font-size: 12px; margin-bottom: 15px;">{{ $catName }}</div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 800; color: #333; font-size: 18px;">{{ number_format($relFinalPrice, 3) }}</div>
                                @if($relHasDiscount)
                                    <div style="color: var(--text-light); text-decoration: line-through; font-size: 12px;">{{ number_format($relProduct->Price, 3) }}</div>
                                @endif
                            </div>
                            <button class="btn-primary" style="padding: 8px 15px;"><i class="fas fa-shopping-cart"></i></button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- FAQs Section -->
    @if(isset($faqs) && $faqs->count() > 0)
    <div style="margin-top: 80px; text-align: center;">
        <h2 style="font-size: 28px; font-weight: 800; color: #333; margin: 0 0 10px;">@lang('v2_product.faqs_title')</h2>
        <p style="color: var(--text-light); font-size: 15px; margin: 0 0 40px;">@lang('v2_product.faqs_desc')</p>
        
        <div style="max-width: 800px; margin: 0 auto; text-align: {{ $lang == 'fr' ? 'right' : 'left' }};">
            @foreach($faqs as $faq)
            <div class="faq-item" style="border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 15px; overflow: hidden; background: #fff;">
                <div class="faq-header" onclick="toggleFaq(this)" style="padding: 20px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; font-weight: 700; color: #333; font-size: 15px;">
                    <span>{{ $lang == 'fr' ? $faq->question : ($faq->question_fr ?: $faq->question) }}</span>
                    <i class="fas fa-chevron-down faq-icon" style="color: var(--primary-color); transition: transform 0.3s ease;"></i>
                </div>
                <div class="faq-body" style="display: none; padding: 0 20px 20px; color: var(--text-light); line-height: 1.8; font-size: 14px; border-top: 1px solid #f9f9f9; padding-top: 15px;">
                    {{ $lang == 'fr' ? $faq->answer : ($faq->answer_fr ?: $faq->answer) }}
                </div>
            </div>
            @endforeach
        </div>
        
        <script>
            function toggleFaq(header) {
                const body = header.nextElementSibling;
                const icon = header.querySelector('.faq-icon');
                const isOpen = body.style.display === 'block';
                
                // Close all
                document.querySelectorAll('.faq-body').forEach(b => b.style.display = 'none');
                document.querySelectorAll('.faq-icon').forEach(i => i.style.transform = 'rotate(0deg)');
                
                if (!isOpen) {
                    body.style.display = 'block';
                    icon.style.transform = 'rotate(180deg)';
                }
            }
        </script>
    </div>
    @endif
</div>

<style>
    /* Remove arrows from number input */
    input[type="number"]::-webkit-inner-spin-button, 
    input[type="number"]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
</style>
@endsection
