@extends('v2.layouts.app')

@section('title', __('new_design.checkout_page.title'))

@section('content')

@php
    $isRtl = app()->getLocale() != 'en';
    $dir = $isRtl ? 'rtl' : 'ltr';
    $subtotalVal = subtotal();

    $activeSubscription = null;
    $subDiscountPercent = 0;
    $subDiscountAmount = 0;
    $maxDiscountAmount = PHP_INT_MAX;
    $countryNameForTax = session()->get('billing_country', null) ?? 'Oman';
    if (auth()->check()) {
        $defaultAddress = auth()->user()->addresses()->where('is_default', 1)->first() 
            ?? auth()->user()->addresses()->first();
        if ($defaultAddress && $defaultAddress->country_id) {
            $countryNameForTax = $defaultAddress->country_id;
        }
    }
    $taxRate = tax_rate($countryNameForTax) / 100;

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
            
            $calculatedSubDiscount = ($subDiscountPercent / 100) * $subtotalVal;
            $subDiscountAmount = min($calculatedSubDiscount, $maxDiscountAmount);
        }
    }

    $subtotalAfterSub = $subtotalVal - $subDiscountAmount;
    $taxVal = $subtotalAfterSub * $taxRate;
    
    $couponAmount = session()->get('CouponAmount', 0);
    $grandTotalVal = $subtotalAfterSub + $taxVal - $couponAmount;
@endphp

<style>
    .checkout-container { padding: 40px 15px; font-family: 'Cairo', sans-serif; }
    .checkout-card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 25px; }
    .checkout-title { font-size: 20px; font-weight: 800; color: var(--text-color); margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    
    .form-group { margin-bottom: 20px; text-align: start; }
    .form-label { font-size: 14px; font-weight: 700; color: var(--text-color); display: block; margin-bottom: 8px; }
    .form-control { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; transition: 0.3s; background: #fff; }

    .address-card { border: 2px solid #eee; border-radius: 12px; padding: 15px; cursor: pointer; transition: 0.3s; height: 100%; background: #fff; text-align: start; }
    .address-card:hover { border-color: var(--primary-color); background: #fffaf9; }
    .address-card.selected { border-color: var(--primary-color); background: #fffaf9; box-shadow: 0 4px 12px rgba(217, 38, 36, 0.1); }

    .form-control:focus { border-color: var(--primary-color); }
    
    .shipping-method { display: flex; align-items: center; justify-content: space-between; padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; cursor: pointer; transition: 0.3s; text-align: start; }
    .shipping-method:hover { border-color: var(--primary-color); background: #f9f9f9; }
    .shipping-method.active { border-color: var(--primary-color); border-width: 2px; background: rgba(227, 38, 54, 0.05); }
    
    .payment-method { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px 10px; border: 1px solid #ddd; border-radius: 8px; cursor: pointer; transition: 0.3s; text-align: center; height: 100%; }
    .payment-method:hover { border-color: var(--primary-color); background: #f9f9f9; }
    .payment-method.active { border-color: var(--primary-color); border-width: 2px; background: rgba(227, 38, 54, 0.05); }
    .payment-icon { font-size: 30px; margin-bottom: 10px; color: var(--text-color); }
    .payment-method.active .payment-icon { color: var(--primary-color); }
    
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; color: var(--text-color); }
    .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 18px; font-weight: 800; color: var(--primary-color); }
    
    .btn-submit { width: 100%; padding: 15px; background: var(--primary-color); color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 800; cursor: pointer; transition: 0.3s; margin-top: 20px; }
    .btn-submit:hover { background: #c21e2c; }
</style>

<div class="v2-container checkout-container" dir="{{ $dir }}">
    <!-- Breadcrumb -->
    <nav style="font-size: 14px; margin-bottom: 30px; color: var(--text-light); text-align: start;">
        <a href="{{ route('front') }}" style="color: var(--text-color); text-decoration: none;">الرئيسية</a> 
        <span style="margin: 0 5px;">/</span> 
        <a href="{{ route('cart.content') }}" style="color: var(--text-color); text-decoration: none;">سلة التسوق</a>
        <span style="margin: 0 5px;">/</span>
        <span>إتمام الطلب</span>
    </nav>

    @if ($errors->any())
        <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 25px; text-align: start;">
            <ul style="margin: 0; padding-inline-start: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('checkout.order') }}" method="POST" id="checkout-form">
        @csrf
        <input type="hidden" name="billing_country" value="Oman">
        <input type="hidden" name="billing_zipcode" value="00000">
        <input type="hidden" id="payment_method_input" name="payment" value="thawani">
        <input type="hidden" id="collection_method_input" name="collection_method" value="delivery">

        <div class="row">
            <!-- Left Side (Form) -->
            <div class="col-lg-8">
                
                <!-- Shipping Info Card -->
                <div class="checkout-card">
                    <h2 class="checkout-title" style="text-align: start;"><i class="fas fa-map-marker-alt" style="margin-inline-end: 10px; color: var(--primary-color);"></i> معلومات التوصيل</h2>
                    
                    @if(isset($saved_addresses) && count($saved_addresses) > 0)
                        <div class="form-group text-start">
                            <label class="form-label mb-3">اختر عنوان التوصيل</label>
                            <div class="row">
                                @foreach($saved_addresses as $addr)
                                    <div class="col-md-6 mb-3">
                                        <div class="address-card {{ $addr->is_default ? 'selected' : '' }}" 
                                            onclick="selectAddressCard(this)"
                                            data-name="{{ auth()->user()->name }}"
                                            data-state="{{ $addr->state_id ?? $addr->state }}"
                                            data-city="{{ $addr->city_id ?? $addr->city }}"
                                            data-area="{{ $addr->area_id ?? $addr->area }}"
                                            data-street="{{ $addr->address_line1 }}"
                                            data-phone="{{ $addr->phone }}">
                                            <div style="font-weight: 800; color: var(--primary-color); margin-bottom: 5px;">
                                                <i class="fas fa-home"></i> {{ $addr->label ?? 'عنواني' }}
                                                @if($addr->is_default) <span class="badge bg-danger" style="font-size: 10px;">الافتراضي</span> @endif
                                            </div>
                                            <div style="font-size: 13px; color: var(--text-color); line-height: 1.5;">
                                                {{ $addr->address_line1 }}<br>
                                                <i class="fas fa-phone-alt" style="font-size:11px;"></i> {{ $addr->phone }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <!-- Add New Address Option -->
                                <div class="col-md-6 mb-3">
                                    <div class="address-card" id="btn_show_manual_form" onclick="showManualForm()" style="height: 100%; display: flex; align-items: center; justify-content: center; min-height: 80px; border: 2px dashed var(--primary-color); background: #fffaf9;">
                                        <div style="text-align: center; color: var(--primary-color);">
                                            <i class="fas fa-plus mb-2" style="font-size: 20px;"></i><br>
                                            <span style="font-weight: bold;">إضافة عنوان جديد</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning text-start">
                            <strong>لا يوجد عناوين محفوظة!</strong> يرجى إضافة عنوان أولاً لإتمام الطلب.
                            <br><br>
                            <a href="{{ route('user.profile', ['tab' => 'addresses']) }}" class="btn btn-sm btn-primary" style="background: var(--primary-color); border:none;">إضافة عنوان جديد</a>
                        </div>
                    @endif

                    <!-- Manual Form Fields (Auto-filled or manual entry) -->
                    <div id="address_form_fields" style="{{ (isset($saved_addresses) && count($saved_addresses) > 0) ? 'display: none;' : '' }} margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                        <h4 style="font-size: 16px; font-weight: bold; margin-bottom: 15px; color: var(--text-color); text-align: start;">أدخل بيانات التوصيل</h4>
                        <div class="form-group text-start">
                            <label class="form-label">الاسم الكامل</label>
                            <input type="text" id="billing_name" name="billing_name" value="{{ old('billing_name', $billing->Name ?? $user->name ?? '') }}" required class="form-control" placeholder="الاسم الكامل">
                        </div>

                        <div class="row text-start">
                            <div class="col-md-6 form-group">
                                <label class="form-label">المنطقة / المحافظة</label>
                                <select name="billing_state" id="billing_state_select" required class="form-control">
                                    <option value="">اختر المحافظة</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->id }}" {{ (old('billing_state', $billing->State ?? '') == $state->id) ? 'selected' : '' }}>
                                            {{ $state->name_ar ?? $state->name_en }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">المدينة / الولاية</label>
                                <select name="billing_city" id="billing_city_select" required class="form-control">
                                    <option value="">اختر الولاية</option>
                                </select>
                            </div>
                        </div>

                        <div class="row text-start">
                            <div class="col-md-6 form-group">
                                <label class="form-label">الحي / المنطقة</label>
                                <select name="billing_area" id="billing_area_select" required class="form-control">
                                    <option value="">اختر الحي</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="form-label">رقم الجوال</label>
                                <input type="text" id="billing_phone" name="billing_phone" value="{{ old('billing_phone', $billing->phone_number ?? $user->Number ?? '') }}" required class="form-control" dir="ltr" style="text-align: {{ $isRtl ? 'right' : 'left' }}">
                            </div>
                        </div>

                        <div class="form-group mb-0 text-start">
                            <label class="form-label">العنوان بالتفصيل</label>
                            <input type="text" id="billing_street_address" name="billing_street_address" value="{{ old('billing_street_address', $billing->Street ?? '') }}" required class="form-control" placeholder="رقم المبنى، الشارع، المعالم القريبة">
                        </div>
                    </div>
                </div>

                <!-- Shipping Method Card -->
                <div class="checkout-card">
                    <h2 class="checkout-title" style="text-align: start;"><i class="fas fa-truck" style="margin-inline-end: 10px; color: var(--primary-color);"></i> طريقة التوصيل</h2>
                    
                    <div class="shipping-method active" id="method_delivery" onclick="selectCollectionMethod('delivery')">
                        <div style="display: flex; align-items: center;">
                            <i class="fas fa-shipping-fast" style="font-size: 24px; margin-inline-end: 15px; color: var(--primary-color);"></i>
                            <div>
                                <div style="font-weight: 800;">توصيل سريع</div>
                                <div style="font-size: 12px; color: var(--text-light);">سيتم التوصيل للعنوان المحدد أعلاه</div>
                            </div>
                        </div>
                        <div style="font-weight: 800; color: var(--primary-color);" class="dynamic-shipping-fee">-- @lang('v2_layout.currency_omr')</div>
                    </div>

                    <div class="shipping-method" id="method_pickup" onclick="selectCollectionMethod('store_pickup')">
                        <div style="display: flex; align-items: center;">
                            <i class="fas fa-store" style="font-size: 24px; margin-inline-end: 15px; color: var(--text-light);"></i>
                            <div>
                                <div style="font-weight: 800;">الاستلام من الفرع</div>
                                <div style="font-size: 12px; color: var(--text-light);">استلم طلبك مجاناً من أقرب فرع</div>
                            </div>
                        </div>
                        <div style="font-weight: 800; color: var(--primary-color);">0.00 @lang('v2_layout.currency_omr')</div>
                    </div>
                </div>

                <!-- Payment Method Card -->
                <div class="checkout-card">
                    <h2 class="checkout-title" style="text-align: start;"><i class="fas fa-wallet" style="margin-inline-end: 10px; color: var(--primary-color);"></i> طريقة الدفع</h2>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="payment-method active" id="pay_thawani" onclick="selectPayment('thawani')">
                                <i class="fas fa-credit-card payment-icon"></i>
                                <div style="font-weight: 800; font-size: 14px;">دفع إلكتروني (ثواني)</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="payment-method" id="pay_cod" onclick="selectPayment('COD')">
                                <i class="fas fa-hand-holding-usd payment-icon"></i>
                                <div style="font-weight: 800; font-size: 14px;">الدفع عند الاستلام</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Side (Summary) -->
            <div class="col-lg-4">
                <div class="checkout-card" style="position: sticky; top: 20px;">
                    <h2 class="checkout-title" style="text-align: center;">ملخص الطلب</h2>
                    
                    <!-- Products -->
                    <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px; padding-right: 5px; text-align: start;">
                        @foreach($content as $item)
                            <div style="display: flex; gap: 15px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                                <div style="width: 60px; height: 60px; border-radius: 8px; overflow: hidden; background: #f9f9f9; flex-shrink: 0;">
                                    @if(!empty($item->options->is_custom_box) && $item->options->image === 'trail-box.png')
                                        <img src="{{ asset('assets/elketar/trail-box.png') }}" alt="product" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <img src="{{ resolve_product_image($item->options->image) }}" alt="product" style="width: 100%; height: 100%; object-fit: cover;">
                                    @endif
                                </div>
                                <div style="flex-grow: 1;">
                                    <div style="font-weight: 700; font-size: 13px; line-height: 1.4; margin-bottom: 5px;">{{ $isRtl ? ($item->options->name_ar ?? $item->name) : $item->name }}</div>
                                    <div style="font-size: 12px; color: var(--text-light); margin-bottom: 5px;">
                                        @if($item->options->size) {{ $item->options->size }} @endif
                                        <span style="font-weight: bold; color: var(--text-color); margin-inline-start: 10px;">{{ $item->qty }}x</span>
                                    </div>
                                    <div style="font-weight: 800; font-size: 14px; color: var(--primary-color);">{{ number_format($item->price, 2) }} @lang('v2_layout.currency_omr')</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Coupon Section -->
                    <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee; text-align: start;">
                        <label class="form-label">هل لديك كود خصم؟</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="coupon_code_input" class="form-control" placeholder="أدخل الكود" style="flex-grow: 1;">
                            <button type="button" onclick="applyCoupon()" style="background: var(--text-color); color: #fff; border: none; border-radius: 8px; padding: 0 20px; font-weight: bold; transition: 0.3s; cursor: pointer;">تطبيق</button>
                        </div>
                        <p id="coupon-message" style="font-size: 12px; margin-top: 10px; font-weight: bold; display: none;"></p>
                    </div>

                    <!-- Totals Breakdown -->
                    <div class="summary-row">
                        <span>المجموع الفرعي</span>
                        <span style="font-weight: bold;">{{ number_format($subtotalVal, 2) }} @lang('v2_layout.currency_omr')</span>
                    </div>

                    <div class="summary-row">
                        <span>رسوم الشحن</span>
                        <span id="summary-shipping-val" style="font-weight: bold;">0.00 @lang('v2_layout.currency_omr')</span>
                    </div>

                    <div class="summary-row">
                        <span>الضريبة (<span id="summary-tax-rate">{{ number_format($taxRate * 100, 0) }}</span>%)</span>
                        <span id="summary-tax-val" style="font-weight: bold;">{{ number_format($taxVal, 2) }} @lang('v2_layout.currency_omr')</span>
                    </div>

                    @if($subDiscountAmount > 0)
                        <div class="summary-row" style="color: #2e7d32;">
                            <span>خصم الاشتراك</span>
                            <span style="font-weight: bold;">-{{ number_format($subDiscountAmount, 2) }} @lang('v2_layout.currency_omr')</span>
                        </div>
                    @endif

                    <div class="summary-row" id="summary-discount-row" style="color: var(--primary-color); {{ $couponAmount > 0 ? '' : 'display: none;' }}">
                        <span>خصم الكوبون</span>
                        <span id="summary-discount-val" style="font-weight: bold;">-{{ number_format($couponAmount, 2) }} @lang('v2_layout.currency_omr')</span>
                    </div>

                    <div class="summary-total">
                        <span>الإجمالي</span>
                        <span id="summary-total-val">{{ number_format($grandTotalVal, 2) }} @lang('v2_layout.currency_omr')</span>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-lock" style="margin-inline-end: 8px;"></i> إتمام الطلب
                    </button>
                    
                    <div style="text-align: center; margin-top: 15px; font-size: 11px; color: var(--text-light);">
                        بالنقر على إتمام الطلب، فإنك توافق على الشروط والأحكام وسياسة الخصوصية الخاصة بالمتجر
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
    const isRtl = "{{ $isRtl }}";

    function selectCollectionMethod(method) {
        document.getElementById('collection_method_input').value = method;
        document.getElementById('method_delivery').classList.remove('active');
        document.getElementById('method_pickup').classList.remove('active');
        document.getElementById('method_' + (method === 'store_pickup' ? 'pickup' : 'delivery')).classList.add('active');
        
        const isPickup = method === 'store_pickup';
        const shippingFields = ['billing_state_select', 'billing_city_select', 'billing_area_select', 'billing_street_address'];
        
        shippingFields.forEach(fieldId => {
            const el = document.getElementById(fieldId);
            if(el) {
                if (isPickup) {
                    el.removeAttribute('required');
                    el.disabled = true;
                } else {
                    el.setAttribute('required', 'required');
                    el.disabled = false;
                }
            }
        });

        if (isPickup) {
            document.getElementById('summary-shipping-val').innerText = '0.00 ' + (isRtl ? 'ر.ع' : 'OMR');
            recalculateTotal(0);
        } else {
            const selectedArea = document.getElementById('billing_area_select').value;
            if (selectedArea) {
                updateDeliveryChargeByArea(selectedArea);
            } else {
                recalculateTotal(0);
            }
        }
    }

    function selectPayment(method) {
        document.getElementById('payment_method_input').value = method;
        document.getElementById('pay_thawani').classList.remove('active');
        document.getElementById('pay_cod').classList.remove('active');
        if(method === 'thawani') {
            document.getElementById('pay_thawani').classList.add('active');
        } else {
            document.getElementById('pay_cod').classList.add('active');
        }
    }

    // Dynamic Address Selection from Saved Cards
    function selectAddressCard(cardEl) {
        // Deselect all
        document.querySelectorAll('.address-card').forEach(el => el.classList.remove('selected'));
        // Select clicked
        cardEl.classList.add('selected');

        // Hide manual form if it was visible
        document.getElementById('address_form_fields').style.display = 'none';

        const name = cardEl.getAttribute('data-name');
        const phone = cardEl.getAttribute('data-phone');
        const street = cardEl.getAttribute('data-street');
        const stateId = cardEl.getAttribute('data-state');
        const cityId = cardEl.getAttribute('data-city');
        const areaId = cardEl.getAttribute('data-area');

        document.getElementById('billing_name').value = name;
        document.getElementById('billing_phone').value = phone;
        document.getElementById('billing_street_address').value = street;
        
        if (stateId) {
            document.getElementById('billing_state_select').value = stateId;
            loadCities(stateId, cityId, () => {
                if (cityId) {
                    loadAreas(cityId, areaId, () => {
                        if (areaId) {
                            document.getElementById('billing_area_select').value = areaId;
                            updateDeliveryChargeByArea(areaId);
                        }
                    });
                }
            });
        }
    }

    function showManualForm() {
        // Deselect all address cards
        document.querySelectorAll('.address-card').forEach(el => el.classList.remove('selected'));
        // Select the manual card
        document.getElementById('btn_show_manual_form').classList.add('selected');
        
        // Clear fields for manual entry
        document.getElementById('billing_name').value = "{{ old('billing_name', auth()->user()->name ?? '') }}";
        document.getElementById('billing_phone').value = "{{ old('billing_phone', auth()->user()->Number ?? '') }}";
        document.getElementById('billing_street_address').value = "";
        document.getElementById('billing_state_select').value = "";
        document.getElementById('billing_city_select').innerHTML = '<option value="">اختر الولاية</option>';
        document.getElementById('billing_area_select').innerHTML = '<option value="">اختر الحي</option>';
        
        // Show the manual form
        document.getElementById('address_form_fields').style.display = 'block';
        
        // Reset delivery charge until they select an area
        document.querySelector('.dynamic-shipping-fee').innerText = "-- @lang('v2_layout.currency_omr')";
        document.getElementById('summary-shipping-val').innerText = "0.00 @lang('v2_layout.currency_omr')";
        calculateTotal();
    }

    // Initialize with default selected card if exists
    document.addEventListener('DOMContentLoaded', function() {
        const defaultCard = document.querySelector('.address-card.selected');
        if (defaultCard) {
            selectAddressCard(defaultCard);
        }

        const stateSelect = document.getElementById('billing_state_select');
        const citySelect = document.getElementById('billing_city_select');
        const areaSelect = document.getElementById('billing_area_select');
        
        stateSelect.addEventListener('change', function() {
            if (this.value) {
                loadCities(this.value);
            } else {
                citySelect.innerHTML = '<option value="">اختر الولاية</option>';
                areaSelect.innerHTML = '<option value="">اختر الحي</option>';
            }
        });

        citySelect.addEventListener('change', function() {
            if (this.value) {
                loadAreas(this.value);
            } else {
                areaSelect.innerHTML = '<option value="">اختر الحي</option>';
            }
        });

        areaSelect.addEventListener('change', function() {
            if (this.value) {
                updateDeliveryChargeByArea(this.value);
            }
        });
    });

    function loadCities(stateId, selectedCityId = '', callback = null) {
        fetch(`/get-cities-by-state/${stateId}`)
            .then(res => res.json())
            .then(cities => {
                const citySelect = document.getElementById('billing_city_select');
                citySelect.innerHTML = '<option value="">اختر الولاية</option>';
                cities.forEach(city => {
                    const name = isRtl ? (city.name_ar || city.name_en) : (city.name_en || city.name_ar);
                    const selected = (city.id == selectedCityId) ? 'selected' : '';
                    citySelect.innerHTML += `<option value="${city.id}" ${selected}>${name}</option>`;
                });
                if (callback) callback();
            })
            .catch(err => console.error('Error fetching cities:', err));
    }

    function loadAreas(cityId, selectedAreaId = '', callback = null) {
        fetch(`/get-areas-by-city/${cityId}`)
            .then(res => res.json())
            .then(areas => {
                const areaSelect = document.getElementById('billing_area_select');
                areaSelect.innerHTML = '<option value="">اختر الحي</option>';
                areas.forEach(area => {
                    const name = isRtl ? (area.name_ar || area.name_en) : (area.name_en || area.name_ar);
                    const selected = (area.id == selectedAreaId) ? 'selected' : '';
                    areaSelect.innerHTML += `<option value="${area.id}" ${selected}>${name}</option>`;
                });
                if (callback) callback();
            })
            .catch(err => console.error('Error fetching areas:', err));
    }

    function updateDeliveryChargeByArea(areaId) {
        fetch(`/get-area-charge/${areaId}`)
            .then(res => res.json())
            .then(data => {
                const formattedCharge = data.formatted_charge || `${data.delivery_charge.toFixed(2)} ${isRtl ? 'ر.ع' : 'OMR'}`;
                document.getElementById('summary-shipping-val').innerText = formattedCharge;
                
                document.querySelectorAll('.dynamic-shipping-fee').forEach(el => {
                    el.innerText = formattedCharge;
                });

                if (data.tax_show) {
                    document.getElementById('summary-tax-val').innerText = data.tax_show;
                }
                if (data.tax_rate !== undefined) {
                    const taxRateEl = document.getElementById('summary-tax-rate');
                    if (taxRateEl) {
                        taxRateEl.innerText = (data.tax_rate * 100).toFixed(0);
                    }
                }
                if (data.total_cost) {
                    document.getElementById('summary-total-val').innerText = data.total_cost;
                }
            })
            .catch(err => console.error('Error fetching area charge:', err));
    }

    function recalculateTotal(shippingFeeStr) {
        // Fallback calculation if not using ajax for store pickup
        const subtotal = parseFloat("{{ $subtotalVal }}");
        const subPercent = parseFloat("{{ $subDiscountPercent }}") || 0;
        const maxSubDiscount = parseFloat("{{ $maxDiscountAmount }}") || 0;
        let subDiscount = (subtotal * subPercent) / 100;
        subDiscount = Math.min(subDiscount, maxSubDiscount);
        const subtotalAfterSub = subtotal - subDiscount;
        const taxRate = parseFloat("{{ $taxRate }}") || 0;
        const tax = subtotalAfterSub * taxRate;
        const couponValStr = document.getElementById('summary-discount-val')?.innerText || '0';
        const coupon = parseFloat(couponValStr.replace(/[^0-9.]/g, '')) || 0;
        const total = Math.max(0, subtotalAfterSub + tax - coupon);
        document.getElementById('summary-total-val').innerText = total.toFixed(2) + ' ' + (isRtl ? 'ر.ع' : 'OMR');
    }

    function applyCoupon() {
        const code = document.getElementById('coupon_code_input').value.trim();
        const msgEl = document.getElementById('coupon-message');
        if (!code) {
            msgEl.style.color = "#c62828";
            msgEl.innerText = "الرجاء إدخال كود الخصم";
            msgEl.style.display = 'block';
            return;
        }

        fetch("{{ route('apply.coupon') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ coupon_code: code })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                msgEl.style.color = "#2e7d32";
                msgEl.innerText = "تم تطبيق الكوبون بنجاح!";
                msgEl.style.display = 'block';

                const selectedArea = document.getElementById('billing_area_select').value;
                if (selectedArea) {
                    updateDeliveryChargeByArea(selectedArea);
                } else {
                    location.reload(); 
                }
            } else {
                msgEl.style.color = "#c62828";
                msgEl.innerText = data.message || "كود الخصم غير صالح";
                msgEl.style.display = 'block';
            }
        })
        .catch(err => {
            msgEl.style.color = "#c62828";
            msgEl.innerText = "حدث خطأ أثناء تطبيق كود الخصم";
            msgEl.style.display = 'block';
        });
    }
</script>
@endsection
