@extends('v2.layouts.profile')

@section('profile_content')

<style>
    body {
        background-color: #fcfcfc !important; /* Very light gray/white background as in Image 1 */
    }
    .form-control-lg {
        font-size: 15px;
        border-radius: 8px;
    }
    .form-label {
        font-weight: 600;
        color: #555;
        font-size: 14px;
        margin-bottom: 8px;
        text-align: right;
        display: block;
    }
</style>

<!-- Header Title (Right aligned) -->
<div class="d-flex align-items-center justify-content-end mb-4">
    <div class="text-end">
        <h3 class="fw-bold mb-2">@lang('v2_home.your_account')</h3>
        <p class="text-muted mb-0">@lang('v2_home.update_contact_info')</p>
    </div>
</div>

@if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                    <i class="fas fa-check-circle mx-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                    <i class="fas fa-exclamation-circle mx-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('user.profile.update') }}" method="POST">
                @csrf
                <div class="card border border-light shadow-sm mb-4" style="border-radius: 12px; background-color: #ffffff;">
                    <div class="card-body p-4 p-md-5">
                        <h6 class="fw-bold mb-4" style="text-align: right;">@lang('v2_home.contact_personal_info')</h6>
                        
                        <div class="row g-4" dir="rtl">
                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label" style="text-align: right;">{{ __('Email') }}</label>
                                <input type="email" name="email" class="form-control form-control-lg" style="border: 1px solid #dee2e6; background-color: #fcfcfc; text-align: right !important;" value="{{ $user->email }}" required dir="ltr">
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            
                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label" style="text-align: right;">{{ __('Phone') }}</label>
                                <div class="input-group input-group-lg" dir="ltr">
                                    <input type="text" name="number" class="form-control" style="border: 1px solid #dee2e6; border-right: 0; background-color: #fcfcfc; text-align: right !important;" value="{{ str_replace('+968', '', $user->Number) }}" required dir="ltr">
                                    <span class="input-group-text bg-white d-flex align-items-center" style="border: 1px solid #dee2e6; border-left: 0; border-radius: 0 8px 8px 0; background-color: #fcfcfc !important;">
                                        <span class="me-2 text-muted">+968</span>
                                        <img src="https://flagcdn.com/w20/om.png" alt="Oman" width="20"> 
                                    </span>
                                </div>
                                @error('number') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            
                            <!-- Name -->
                            <div class="col-12 mt-4">
                                <label class="form-label" style="text-align: right;">{{ __('Name') }}</label>
                                <input type="text" name="name" class="form-control form-control-lg" style="border: 1px solid #dee2e6; background-color: #fcfcfc; text-align: right !important;" value="{{ $user->name }}" required>
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border border-light shadow-sm mb-4" style="border-radius: 12px; background-color: #ffffff;">
                    <div class="card-body p-4 p-md-5">
                        <h6 class="fw-bold mb-4" style="text-align: right;">@lang('v2_home.new_password')</h6>
                        
                        <div class="row g-4" dir="rtl">
                            <div class="col-md-6">
                                <label class="form-label" style="text-align: right;">@lang('v2_home.new_password')</label>
                                <div class="position-relative">
                                    <i class="far fa-eye-slash position-absolute top-50 translate-middle-y text-muted" style="cursor: pointer; left: 15px;" onclick="togglePassword('password', this)"></i>
                                    <input type="password" name="password" id="password" class="form-control form-control-lg" style="border: 1px solid #dee2e6; background-color: #fcfcfc; padding-left: 40px; text-align: right !important;" placeholder="........" dir="ltr">
                                </div>
                                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label" style="text-align: right;">@lang('v2_home.confirm_new_password')</label>
                                <div class="position-relative">
                                    <i class="far fa-eye-slash position-absolute top-50 translate-middle-y text-muted" style="cursor: pointer; left: 15px;" onclick="togglePassword('password_confirmation', this)"></i>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-lg" style="border: 1px solid #dee2e6; background-color: #fcfcfc; padding-left: 40px; text-align: right !important;" placeholder="........" dir="ltr">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-start mt-4" dir="ltr">
                    <button type="submit" class="btn btn-lg rounded-3 px-5 text-white" style="background-color: #d92624; border: none; font-size: 16px; font-weight: 600;">تحديث حسابك</button>
                </div>
            </form>
@endsection

@section('scripts')
<script>
function togglePassword(inputId, iconElement) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        iconElement.classList.remove('fa-eye-slash');
        iconElement.classList.add('fa-eye');
    } else {
        input.type = 'password';
        iconElement.classList.remove('fa-eye');
        iconElement.classList.add('fa-eye-slash');
    }
}
</script>
@endsection
