@extends('v2.layouts.app')

@section('title')
    @yield('profile_title', __('Profile'))
@endsection

@section('content')
<div class="container py-5 mt-5">
    <div class="row pt-4">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            @include('v2.user.sidebar')
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            @yield('profile_content')
        </div>
    </div>
</div>
@endsection
