@extends('v2.layouts.app')

@section('content')
<div class="container py-5 mt-5">
    <div class="row pt-4">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            @include('v2.user.sidebar')
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('user.profile.orders') }}" class="text-dark fs-4 {{ app()->getLocale() == 'ar' ? 'ms-3' : 'me-3' }}">
                    <i class="fas {{ app()->getLocale() == 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }}"></i>
                </a>
                <h3 class="fw-bold mb-0">@lang('v2_home.tracking_details')</h3>
            </div>

            <!-- Top Details -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small">@lang('v2_home.order_time')</span>
                        <h6 class="fw-bold mb-0 mt-1" dir="ltr">{{ $order->time_only }}</h6>
                    </div>
                    <div class="text-end">
                        <span class="text-muted small">@lang('v2_home.shipment_id')</span>
                        <h6 class="fw-bold mb-0 mt-1">{{ $order->Order_Number ?? 'ORD-'.$order->id }}</h6>
                    </div>
                </div>
            </div>

            <!-- Tracking Stepper -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-5">
                    @php
                        // Mocking statuses based on typical order flow
                        // 1: Pending/Confirmed, 2: Delivered
                        $statusLevel = 1;
                        if($order->Order_Status == ORDER_DELIVERED) $statusLevel = 4;
                        elseif($order->Order_Status == 1) $statusLevel = 2; // Processing
                    @endphp
                    
                    <div class="tracking-stepper position-relative d-flex justify-content-between text-center">
                        <!-- Progress Line -->
                        <div class="progress position-absolute top-50 start-0 w-100 translate-middle-y" style="height: 4px; z-index: 0; {{ app()->getLocale() == 'ar' ? 'transform: scaleX(-1);' : '' }}">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ ($statusLevel - 1) * 33.33 }}%;"></div>
                        </div>
                        
                        <!-- Step 1: Confirmed -->
                        <div class="step position-relative z-1">
                            <div class="step-icon bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 40px; height: 40px;">
                                <i class="fas fa-check"></i>
                            </div>
                            <span class="small fw-bold {{ $statusLevel >= 1 ? 'text-dark' : 'text-muted' }}">@lang('v2_home.order_confirmed')</span>
                        </div>
                        
                        <!-- Step 2: Processing -->
                        <div class="step position-relative z-1">
                            <div class="step-icon {{ $statusLevel >= 2 ? 'bg-danger text-white' : 'bg-light text-muted border' }} rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 40px; height: 40px;">
                                <i class="fas fa-box"></i>
                            </div>
                            <span class="small fw-bold {{ $statusLevel >= 2 ? 'text-dark' : 'text-muted' }}">@lang('v2_home.processing')</span>
                        </div>
                        
                        <!-- Step 3: On the way -->
                        <div class="step position-relative z-1">
                            <div class="step-icon {{ $statusLevel >= 3 ? 'bg-danger text-white' : 'bg-light text-muted border' }} rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 40px; height: 40px;">
                                <i class="fas fa-truck"></i>
                            </div>
                            <span class="small fw-bold {{ $statusLevel >= 3 ? 'text-dark' : 'text-muted' }}">@lang('v2_home.on_the_way')</span>
                        </div>
                        
                        <!-- Step 4: Delivered -->
                        <div class="step position-relative z-1">
                            <div class="step-icon {{ $statusLevel >= 4 ? 'bg-danger text-white' : 'bg-light text-muted border' }} rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 40px; height: 40px;">
                                <i class="fas fa-home"></i>
                            </div>
                            <span class="small fw-bold {{ $statusLevel >= 4 ? 'text-dark' : 'text-muted' }}">@lang('v2_home.delivered')</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-bold mb-0">@lang('v2_home.delivery_address')</h6>
                        <button class="btn btn-outline-danger btn-sm rounded-pill px-3">@lang('v2_home.update_address')</button>
                    </div>
                    
                    @php
                        $shipping = is_string($order->shipping_address) ? json_decode($order->shipping_address, true) : $order->shipping_address;
                    @endphp
                    
                    <p class="text-muted small mb-2 lh-lg">
                        {{ $shipping['address'] ?? ($order->Address ?? 'لا يوجد عنوان مسجل') }}<br>
                        {{ $shipping['city'] ?? '' }}
                    </p>
                    <div class="d-flex align-items-center small text-muted">
                        <span>{{ $shipping['name'] ?? ($user->name ?? '') }} , <span dir="ltr">{{ $shipping['phone_number'] ?? ($user->Number ?? '') }}</span></span>
                        <i class="fas fa-check-circle text-success ms-2 {{ app()->getLocale() == 'ar' ? 'me-2 ms-0' : '' }}"></i>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Order Summary -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4">@lang('v2_home.order_summary_invoice')</h6>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">@lang('v2_home.shipping_fees')</span>
                                <span class="fw-bold">0.00 <i class="fas fa-coins text-warning"></i></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-dark fw-bold">@lang('v2_home.total')</span>
                                <span class="fw-bold fs-5 text-danger">{{ number_format($order->Total_Ammount, 2) }} <i class="fas fa-coins text-warning"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Items Summary -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4">@lang('v2_home.items_summary')</h6>
                            
                            <div class="d-flex flex-column gap-3">
                                @foreach($order->order_details as $detail)
                                @if($detail->product)
                                <div class="d-flex align-items-center gap-3 bg-light p-2 rounded-3">
                                    <img src="{{ asset($detail->product->Primary_Image) }}" class="rounded-3 shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div class="flex-grow-1 text-start">
                                        <p class="text-muted small mb-0" style="font-size: 11px;">{{ app()->getLocale() == 'en' ? $detail->product->Category->en_Category_Name : $detail->product->Category->ar_Category_Name }}</p>
                                        <h6 class="fw-bold mb-1" style="font-size: 13px;">{{ app()->getLocale() == 'en' ? $detail->product->en_Product_Name : $detail->product->ar_Product_Name }}</h6>
                                        <div class="fw-bold text-dark small">
                                            {{ number_format($detail->Total_Price, 2) }} <i class="fas fa-coins text-warning {{ app()->getLocale() == 'ar' ? 'me-1' : 'ms-1' }}"></i>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection
