@extends('front.layouts.new_design_layout')

@section('title', __('new_design.cart_page.title'))

@section('content')

@php
    $isRtl = app()->getLocale() != 'en';
    $dir = $isRtl ? 'rtl' : 'ltr';

    // Fetch active subscription & tax details
    $countryNameForTax = session()->get('billing_country', null) ?? 'Oman'; // Default to Oman
    
    if (auth()->check()) {
        $defaultAddress = auth()->user()->addresses()->where('is_default', 1)->first() 
            ?? auth()->user()->addresses()->first();
        if ($defaultAddress && $defaultAddress->country_id) {
            $countryNameForTax = $defaultAddress->country_id;
        }
    }
    
    $taxRate = tax_rate($countryNameForTax) / 100;

    $activeSubscription = null;
    $subDiscountPercent = 0;
    $subDiscountAmount = 0;
    $maxDiscountAmount = PHP_INT_MAX;

    if (auth()->check()) {
        $activeSubscription = \App\Models\UserSubscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->whereDate('end_at', '>=', now())
            ->with('subscription')
            ->latest()
            ->first();

        if ($activeSubscription && $activeSubscription->subscription) {
            if ($activeSubscription->subscription->tax_exempt) {
                $taxRate = 0;
            }
            $subDiscountPercent = $activeSubscription->subscription->discount_percent ?? 0;
            $maxDiscountAmount = $activeSubscription->subscription->max_discount_amount ?? PHP_INT_MAX;
            
            $subtotalVal = floatval(subtotal());
            $calculatedSubDiscount = ($subDiscountPercent / 100) * $subtotalVal;
            $subDiscountAmount = min($calculatedSubDiscount, $maxDiscountAmount);
        }
    }
@endphp

<!-- Main Wrapper with White Background -->
<div class="cart-page bg-white text-[#1A4231] pb-24" dir="{{ $dir }}" style="font-family: 'Cairo', sans-serif;" x-data="cartComponent()" x-cloak>

    <!-- Top Spacer -->
    <div class="h-6 bg-white"></div>

    <!-- Wide Container (Max-Width 1360px matching standard layout) -->
    <div class="container mx-auto px-4 lg:px-8 flex flex-col gap-8 max-w-[1360px]">

        <!-- Page Title -->
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-[#1A4231] text-start tracking-wide">
            {{ __('new_design.cart_page.items_title') }}
        </h1>

        <!-- Cart Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Empty state -->
            <div x-show="items.length === 0" class="col-span-1 lg:col-span-3 flex flex-col items-center justify-center py-20 text-center bg-[#FAF9F5] border border-gray-150 rounded-[24px] p-8 shadow-sm">
                <div class="w-24 h-24 rounded-full bg-[#1A4231]/5 flex items-center justify-center text-[#1A4231] mb-6">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-black text-[#1A4231] mb-2">{{ $isRtl ? 'حقيبة التسوق فارغة' : 'Your shopping cart is empty' }}</h2>
                <p class="text-gray-500 font-semibold text-sm max-w-md mb-8">{{ $isRtl ? 'اكتشف محصولاتنا وأدواتنا الفاخرة وابدأ التسوق الآن.' : 'Discover our premium crops and tools and start shopping now.' }}</p>
                <a href="{{ route('front.store') }}" class="bg-[#1A4231] hover:bg-[#2C624A] text-white font-extrabold px-8 py-3.5 rounded-full text-sm transition-all shadow-md">
                    {{ $isRtl ? 'انتقل إلى المتجر' : 'Go to Store' }}
                </a>
            </div>

            <!-- Right Column: Cart Items (Takes 2 columns in large screens) -->
            <div x-show="items.length > 0" class="lg:col-span-2 flex flex-col gap-6">
                
                <template x-for="item in items" :key="item.rowId">
                    <div class="bg-[#FAF9F5] border border-gray-150 rounded-[24px] p-5 flex items-center gap-5 sm:gap-6 shadow-sm hover:shadow-md transition-shadow">
                        <!-- Image -->
                        <div class="w-[100px] h-[100px] sm:w-[120px] sm:h-[120px] lg:w-[140px] lg:h-[140px] rounded-[20px] overflow-hidden shrink-0 border border-gray-200/50 bg-white flex items-center justify-center">
                            <img :src="resolveProductImage(item.options.image)" :alt="item.name" class="w-full h-full object-cover">
                        </div>
                        <!-- Info & Actions -->
                        <div class="flex-grow flex flex-col justify-between self-stretch py-1 text-start">
                            <!-- Title & Remove Icon -->
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-base sm:text-lg lg:text-xl font-bold text-[#1A4231] leading-tight" x-text="locale !== 'en' && item.options.name_ar ? item.options.name_ar : item.name"></h3>
                                    <!-- Options (size / weight) -->
                                    <div class="flex flex-wrap gap-2 mt-1.5">
                                        <template x-if="item.options.size">
                                            <span class="inline-flex px-2 py-0.5 rounded bg-[#1A4231]/5 text-[#1A4231] text-[10px] font-bold" x-text="'{{ __('Size') }}: ' + (locale !== 'en' && item.options.size_ar ? item.options.size_ar : item.options.size)"></span>
                                        </template>
                                        <template x-if="item.options.weight">
                                            <span class="inline-flex px-2 py-0.5 rounded bg-[#1A4231]/5 text-[#1A4231] text-[10px] font-bold" x-text="'{{ __('Weight') }}: ' + item.options.weight.weight + 'g'"></span>
                                        </template>
                                        <template x-if="item.options.is_custom_box">
                                            <div class="flex flex-col gap-1.5 mt-2 bg-[#1A4231]/5 p-3 rounded-xl border border-[#1A4231]/10 text-xs text-[#1A4231] font-semibold w-full">
                                                <div>
                                                    <span class="opacity-75">{{ $isRtl ? 'القالب:' : 'Template:' }}</span> 
                                                    <span x-text="item.options.template"></span>
                                                </div>
                                                <div>
                                                    <span class="opacity-75">{{ $isRtl ? 'السعة:' : 'Capacity:' }}</span> 
                                                    <span x-text="item.options.capacity + ' {{ $isRtl ? 'محاصيل' : 'Crops' }}'"></span>
                                                </div>
                                                <template x-if="item.options.print_name">
                                                    <div>
                                                        <span class="opacity-75">{{ $isRtl ? 'الاسم المطبوع:' : 'Printed Name:' }}</span> 
                                                        <span x-text="item.options.print_name"></span>
                                                    </div>
                                                </template>
                                                <template x-if="item.options.gift_message">
                                                    <div>
                                                        <span class="opacity-75">{{ $isRtl ? 'رسالة الإهداء:' : 'Gift Message:' }}</span> 
                                                        <span x-text="item.options.gift_message"></span>
                                                    </div>
                                                </template>
                                                <div class="mt-1 border-t border-[#1A4231]/10 pt-1 text-[11px] leading-relaxed">
                                                    <span class="opacity-75 block mb-0.5">{{ $isRtl ? 'المحتويات:' : 'Contents:' }}</span>
                                                    <span class="font-bold text-[#1A4231]/90" x-text="item.options.custom_box_details"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <!-- Remove button -->
                                <button @click="removeItem(item.rowId)" class="text-gray-400 hover:text-red-500 transition-colors shrink-0 p-1.5 rounded-lg hover:bg-red-50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                            <!-- Subtitle / Tag -->
                            <p class="text-xs sm:text-sm font-semibold text-gray-400 mt-1" x-text="item.options.item_tag === 'Beginner' ? '{{ __('new_design.store_page.tag_beginner') }}' : (item.options.item_tag === 'Pro' ? '{{ __('new_design.store_page.tag_pro') }}' : '')"></p>
                            <!-- Price & Quantity Selector -->
                            <div class="flex items-center justify-between gap-4 mt-auto pt-2">
                                <span class="text-base sm:text-lg lg:text-xl font-black text-[#1A4231] whitespace-nowrap" x-text="parseFloat(item.price).toFixed(2) + ' ' + currencySymbol"></span>
                                <!-- Quantity selector -->
                                <div class="flex items-center bg-white border border-gray-200 rounded-xl overflow-hidden px-1 py-1 shadow-sm">
                                    <button @click="decreaseQty(item)" class="w-8 h-8 flex items-center justify-center text-[#1A4231] hover:bg-gray-50 transition-colors font-bold text-lg select-none">-</button>
                                    <span class="w-8 text-center font-extrabold text-[#1A4231] text-sm select-none" x-text="item.qty"></span>
                                    <button @click="increaseQty(item)" class="w-8 h-8 flex items-center justify-center text-[#1A4231] hover:bg-gray-50 transition-colors font-bold text-lg select-none">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

            </div>

            <!-- Left Column: Order Summary Sidebar (1 column) -->
            <div x-show="items.length > 0" class="lg:col-span-1">
                <div class="bg-white p-6 lg:p-8 rounded-[32px] border border-[#1A4231] shadow-sm flex flex-col gap-6">
                    
                    <!-- Sidebar Title -->
                    <h2 class="text-xl lg:text-2xl font-black text-[#1A4231] text-start border-b border-gray-100 pb-4">
                        {{ __('new_design.cart_page.summary_title') }}
                    </h2>
                    
                    <!-- Pricing Details -->
                    <div class="flex flex-col gap-4">
                        <div class="flex justify-between items-center text-sm lg:text-base font-bold text-gray-500">
                            <span>{{ __('new_design.cart_page.summary_subtotal') }}</span>
                            <span class="font-extrabold text-[#1A4231]" x-text="subtotal.toFixed(2) + ' ' + currencySymbol"></span>
                        </div>

                        <div class="flex justify-between items-center text-sm lg:text-base font-bold text-gray-500">
                            <span>{{ __('new_design.cart_page.summary_tax') }} (<span x-text="(taxRate * 100).toFixed(0) + '%'"></span>)</span>
                            <span class="font-extrabold text-[#1A4231]" x-text="tax.toFixed(2) + ' ' + currencySymbol"></span>
                        </div>
                        
                        <!-- Subscription Discount (If any) -->
                        <template x-if="subscriptionDiscount > 0">
                            <div class="flex justify-between items-center text-sm lg:text-base font-bold text-green-600">
                                <span>{{ $isRtl ? 'خصم الاشتراك' : 'Subscription Discount' }}</span>
                                <span class="font-extrabold" x-text="'-' + subscriptionDiscount.toFixed(2) + ' ' + currencySymbol"></span>
                            </div>
                        </template>

                        <!-- Discount (If any) -->
                        <template x-if="discountAmount > 0">
                            <div class="flex justify-between items-center text-sm lg:text-base font-bold text-red-500">
                                <span>{{ $isRtl ? 'الخصم' : 'Discount' }} (<span x-text="discountCode"></span>)</span>
                                <span class="font-extrabold" x-text="'-' + discountAmount.toFixed(2) + ' ' + currencySymbol"></span>
                            </div>
                        </template>
                        
                        <!-- Grand Total -->
                        <div class="pt-5 border-t border-gray-100 flex justify-between items-center text-[#1A4231]">
                            <span class="text-lg lg:text-xl font-black">{{ __('new_design.cart_page.summary_total') }}</span>
                            <span class="text-xl lg:text-2xl font-black" x-text="total + ' ' + currencySymbol"></span>
                        </div>
                    </div>

                    <!-- Discount Code Input -->
                    <form action="{{ route('apply.coupon') }}" method="POST" class="flex flex-col gap-2">
                        @csrf
                        <span class="text-xs sm:text-sm font-bold text-gray-400 text-start">
                            {{ __('new_design.cart_page.summary_discount_lbl') }}
                        </span>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="coupon_code" :value="discountCode" placeholder="{{ __('new_design.cart_page.summary_discount_placeholder') }}" 
                                   class="flex-grow w-full px-4 py-3 text-xs sm:text-sm font-bold rounded-xl border border-gray-200 outline-none focus:ring-1 focus:ring-[#1A4231] transition-all text-start placeholder:text-gray-300">
                            <button type="submit" class="bg-[#E5E0D8] hover:bg-[#D5D0C8] text-[#1A4231] font-extrabold px-6 py-3 rounded-xl text-xs sm:text-sm transition-all whitespace-nowrap">
                                {{ __('new_design.cart_page.summary_discount_btn') }}
                            </button>
                        </div>
                    </form>

                    <!-- Checkout Button -->
                    <a href="{{ route('checkout') }}" class="w-full bg-[#1A4231] hover:bg-[#2C624A] text-white py-4 rounded-[16px] text-sm lg:text-base font-black transition-all shadow-md mt-2 block text-center">
                        {{ __('new_design.cart_page.summary_checkout') }}
                    </a>
                    
                </div>
            </div>

        </div>

    </div>

</div>

<!-- CTA Section (هل أنت مستعد لبدء رحلتك معنا؟) -->
<section class="py-24 relative overflow-hidden bg-[#F9F8F6] border-t border-gray-150" dir="{{ $dir }}" style="background-image: url('{{ asset('assets/elketar/fff.png') }}'); background-size: cover; background-position: center;">
    <div class="container mx-auto px-4 relative z-10 text-center flex flex-col items-center">
        <h2 class="text-3xl lg:text-5xl font-black text-[#1A4231] mb-10 max-w-2xl leading-tight">
            {{ __('new_design.about.cta_title') }}
        </h2>
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-center w-full max-w-lg">
            <a href="{{ route('coffee.crops') }}" class="w-full sm:w-auto inline-flex items-center justify-center bg-[#1A4231] hover:bg-[#133224] text-white font-black rounded-full px-10 py-4 transition-all duration-300 shadow-xl transform hover:scale-[1.02] active:scale-[0.98]">
                {{ __('new_design.about.cta_btn_crops') }}
            </a>
            <a href="{{ route('experts') }}" class="w-full sm:w-auto inline-flex items-center justify-center border-2 border-[#1A4231] text-[#1A4231] hover:bg-[#1A4231]/15 font-black rounded-full px-10 py-4 transition-all duration-300 shadow-lg transform hover:scale-[1.02] active:scale-[0.98]">
                {{ __('new_design.about.cta_btn_expert') }}
            </a>
        </div>
    </div>
</section>
<div class="h-20 bg-white"></div>    

@endsection

@stack('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('cartComponent', () => ({
            items: @json(Cart::content()->values()),
            subtotal: {{ floatval(subtotal()) }},
            taxRate: {{ floatval($taxRate) }},
            subscriptionDiscountPercent: {{ floatval($subDiscountPercent) }},
            subscriptionDiscount: {{ floatval($subDiscountAmount) }},
            shipping: 0.00, 
            discountCode: '{{ session("couponCode", "") }}',
            discountAmount: {{ floatval(session("CouponAmount", 0)) }},
            currencySymbol: '{{ __('new_design.coffee_crops.currency') }}',
            locale: '{{ app()->getLocale() }}',

            get tax() {
                const sub = parseFloat(this.subtotal) || 0;
                const subDisc = parseFloat(this.subscriptionDiscount) || 0;
                return Math.max(0, sub - subDisc) * this.taxRate;
            },

            get total() {
                const sub = parseFloat(this.subtotal) || 0;
                const sh = parseFloat(this.shipping) || 0;
                const t = parseFloat(this.tax) || 0;
                const subDisc = parseFloat(this.subscriptionDiscount) || 0;
                const disc = parseFloat(this.discountAmount) || 0;
                return Math.max(0, sub + sh + t - subDisc - disc).toFixed(2);
            },

            resolveProductImage(img) {
                if (!img) {
                    return '/assets/elketar/coffee.png';
                }
                if (img.startsWith('http') || img.startsWith('/')) {
                    return img;
                }
                const elketarAssets = [
                    'card1.png', 'card2.png', 'card3.png', 
                    'Background.png', 'Background (1).png', 
                    'trail-box.png', 'ddd.png', 'coffee.png'
                ];
                if (elketarAssets.includes(img)) {
                    return '/assets/elketar/' + img;
                }
                return '/uploaded_files/product_image/' + img;
            },

            decreaseQty(item) {
                if (item.qty <= 1) {
                    this.removeItem(item.rowId);
                    return;
                }
                $.ajax({
                    url: "{{ route('cart.decrease') }}",
                    data: { id: item.rowId },
                    success: (data) => {
                        this.updateCartData(data);
                        toastr.success("{{ __('Quantity updated successfully') }}");
                    },
                    error: (xhr) => {
                        toastr.error("{{ __('Failed to update quantity') }}");
                    }
                });
            },

            increaseQty(item) {
                $.ajax({
                    url: "{{ route('cart.increase') }}",
                    data: { id: item.rowId },
                    success: (data) => {
                        this.updateCartData(data);
                        toastr.success("{{ __('Quantity updated successfully') }}");
                    },
                    error: (xhr) => {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.error) {
                            toastr.error(xhr.responseJSON.error);
                        } else {
                            toastr.error("{{ __('Failed to update quantity') }}");
                        }
                    }
                });
            },

            removeItem(rowId) {
                $.ajax({
                    url: "{{ route('cart.delete') }}",
                    data: { id: rowId },
                    success: (data) => {
                        this.updateCartData(data);
                        toastr.success("{{ __('Item removed from cart') }}");
                    },
                    error: (xhr) => {
                        toastr.error("{{ __('Failed to remove item') }}");
                    }
                });
            },

            updateCartData(data) {
                // data format: [tc, ta, cd, ...]
                const cartItemsObj = data[2];
                this.items = Object.values(cartItemsObj);
                this.subtotal = parseFloat(data[1]) || 0;
                
                // Calculate subscription discount dynamically in JS
                if (this.subscriptionDiscountPercent > 0) {
                    const maxDiscount = {{ floatval($maxDiscountAmount) }};
                    const calculatedSubDiscount = (this.subtotal * this.subscriptionDiscountPercent) / 100;
                    this.subscriptionDiscount = Math.min(calculatedSubDiscount, maxDiscount);
                } else {
                    this.subscriptionDiscount = 0;
                }
                
                // Update global headers
                $(".totalCountItem").html(data[0]);
                $(".totalAmount").html(data[1] + " OMR");
                
                // Dispatch event
                window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
            }
        }));
    });
</script>
