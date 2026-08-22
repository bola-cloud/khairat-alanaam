@extends('v2.layouts.profile')

@section('profile_content')
            <div class="d-flex align-items-center justify-content-end mb-4">
                <div class="text-end">
                    <h3 class="fw-bold mb-1">@lang('v2_home.orders')</h3>
                    <p class="text-muted mb-0">@lang('v2_home.track_orders_desc')</p>
                </div>
            </div>

            <!-- Pending Orders -->
            <h5 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                <span>@lang('v2_home.pending_orders')</span>
                <span class="text-muted small">({{ $pendingOrders->count() }} عناصر)</span>
            </h5>
            
            <div class="row g-4 mb-5">
                @forelse($pendingOrders as $order)
                <div class="col-12">
                    <div class="card border border-light shadow-sm mb-4" style="border-radius: 12px; background-color: #ffffff; h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                <!-- Status (Right in RTL) -->
                                <div class="d-flex align-items-center gap-2 text-danger fw-bold">
                                    <a href="{{ route('user.profile.order.track', $order->id) }}" class="btn btn-light rounded-circle p-2 text-danger shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-box-open"></i>
                                    </a>
                                    <span>@lang('v2_home.expected_arrival') {{ $order->time_only ?? $order->confirmed_at ?? 'قريباً' }}</span>
                                </div>
                                
                                <!-- 3 dots (Left in RTL) -->
                                <div class="d-flex align-items-center gap-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 35px; height: 35px;">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu border-0 shadow-sm">
                                            <li><a class="dropdown-item py-2" href="{{ route('user.profile.order.track', $order->id) }}"><i class="fas fa-map-marker-alt {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }} text-primary"></i> @lang('v2_home.tracking_details')</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Order Items -->
                            <div class="row g-3">
                                @foreach($order->order_details as $detail)
                                @if($detail->product)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 p-2 bg-light rounded-3">
                                        <!-- Image First -->
                                        <img src="{{ asset($detail->product->Primary_Image) }}" class="rounded-3 shadow-sm" style="width: 70px; height: 70px; object-fit: cover;">
                                        
                                        <!-- Text Second -->
                                        <div class="flex-grow-1 text-start">
                                            <p class="text-muted small mb-0">{{ app()->getLocale() == 'en' ? $detail->product->Category->en_Category_Name : $detail->product->Category->ar_Category_Name }}</p>
                                            <h6 class="fw-bold mb-1">{{ app()->getLocale() == 'en' ? $detail->product->en_Product_Name : $detail->product->ar_Product_Name }}</h6>
                                            <div class="fw-bold text-dark small">
                                                {{ number_format($detail->Total_Price, 2) }} <i class="fas fa-coins text-warning {{ app()->getLocale() == 'ar' ? 'me-1' : 'ms-1' }}"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box-open fs-1 text-muted mb-3"></i>
                    <p class="text-muted">لا توجد طلبات قيد التنفيذ حالياً.</p>
                </div>
                @endforelse
            </div>

            <!-- Completed Orders -->
            <h5 class="fw-bold mb-3 d-flex justify-content-between align-items-center mt-5">
                <span>@lang('v2_home.completed_orders')</span>
            </h5>
            
            <div class="row g-4">
                @forelse($completedOrders as $order)
                <div class="col-12">
                    <div class="card border border-light shadow-sm mb-4" style="border-radius: 12px; background-color: #ffffff; h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                <!-- Status (Right in RTL) -->
                                <div class="d-flex align-items-center gap-2 text-muted fw-bold">
                                    <a href="{{ route('user.profile.order.track', $order->id) }}" class="btn btn-light rounded-circle p-2 text-muted shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-box"></i>
                                    </a>
                                    <span>@lang('v2_home.delivered_on') {{ $order->delivered_at ?? $order->confirmed_at ?? 'مؤخراً' }}</span>
                                </div>
                                
                                <!-- 3 dots & Rate (Left in RTL) -->
                                <div class="d-flex align-items-center gap-3">
                                    <button class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">@lang('v2_home.rate_experience')</button>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 35px; height: 35px;">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu border-0 shadow-sm">
                                            <li><a class="dropdown-item py-2" href="{{ route('user.profile.order.track', $order->id) }}"><i class="fas fa-file-invoice {{ app()->getLocale() == 'ar' ? 'ms-2' : 'me-2' }} text-primary"></i> @lang('v2_home.order_summary_invoice')</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Order Items -->
                            <div class="row g-3">
                                @foreach($order->order_details as $detail)
                                @if($detail->product)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 p-2 bg-light rounded-3">
                                        <img src="{{ asset($detail->product->Primary_Image) }}" class="rounded-3 shadow-sm" style="width: 70px; height: 70px; object-fit: cover;">
                                        
                                        <div class="flex-grow-1 text-start">
                                            <p class="text-muted small mb-0">{{ app()->getLocale() == 'en' ? $detail->product->Category->en_Category_Name : $detail->product->Category->ar_Category_Name }}</p>
                                            <h6 class="fw-bold mb-1">{{ app()->getLocale() == 'en' ? $detail->product->en_Product_Name : $detail->product->ar_Product_Name }}</h6>
                                            <div class="fw-bold text-dark small">
                                                {{ number_format($detail->Total_Price, 2) }} <i class="fas fa-coins text-warning {{ app()->getLocale() == 'ar' ? 'me-1' : 'ms-1' }}"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box fs-1 text-muted mb-3"></i>
                    <p class="text-muted">لا توجد طلبات مكتملة.</p>
                </div>
                @endforelse
            </div>
            
@endsection
