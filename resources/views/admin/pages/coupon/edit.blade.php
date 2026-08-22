@extends('admin.master', ['menu' => 'coupon'])
@section('title', isset($title) ? $title : '')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="breadcrumb__content">
                <div class="breadcrumb__content__left">
                    <div class="breadcrumb__title">
                        <h2>{{ __('Edit Coupon') }}</h2>
                    </div>
                </div>
                <div class="breadcrumb__content__right">
                    <nav aria-label="breadcrumb">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Coupon') }}</li>
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
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-vertical__item bg-style">
                                        <form enctype="multipart/form-data" method="POST"
                                            action="{{ route('admin.coupon.update') }}">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $edit->id }}">
                                            <div class="input__group mb-25">
                                                <label for="exampleInputEmail1">{{ __('Coupon code') }}</label>
                                                <input type="text" class="form-control" id="coupon_code"
                                                    name="coupon_code" value="{{ $edit->CouponCode }}"
                                                    placeholder="{{ __('Code') }}">
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="exampleInputEmail1">{{ __('Amount') }}</label>
                                                <input type="number" min="1" step="0.01" class="form-control"
                                                    id="amount" name="amount" value="{{ $edit->Amount }}"
                                                    placeholder="{{ __('Amount') }}">
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="exampleInputEmail1">{{ __('Minimum Expense') }}</label>
                                                <input type="number" min="0" step="0.01" class="form-control"
                                                    id="min_expenses" name="min_expenses" value="{{ $edit->Min_Expenses }}"
                                                    placeholder="{{ __('Minimum Expenses') }}">
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="exampleInputEmail1">{{ __('Usage Count') }}</label>
                                                <input type="number" min="0" step="1" class="form-control"
                                                    id="usage_count" name="usage_count" value="{{ $edit->usage_count }}"
                                                    placeholder="{{ __('Usage Count') }}">
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="limit_per_user">{{ __('Limit Per User') }}</label>
                                                <input type="number" min="1" step="1" class="form-control"
                                                    id="limit_per_user" name="limit_per_user" value="{{ $edit->limit_per_user }}"
                                                    placeholder="{{ __('Limit Per User') }}">
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="user_id">{{ __('Select User') }}</label>
                                                <select id="user_id" name="user_id" class="form-control">
                                                    <option value="">{{ __('No User') }}</option>
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->id }}"
                                                            {{ $edit->user_id == $user->id ? 'selected' : '' }}>
                                                            {{ $user->name }} ({{ $user->number }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="input__group mb-25">
                                                <label for="exampleInputEmail1">{{ __('Expire Date') }}</label>
                                                <input type="date" class="form-control" id="expire_date"
                                                    name="expire_date" value="{{ $edit->ExpireDate }}">
                                            </div>
                                            <div class="input__button">
                                                <button type="submit" class="btn btn-blue">{{ __('Update') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('coupon_scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // JS removed
        });
    </script>
@endpush
