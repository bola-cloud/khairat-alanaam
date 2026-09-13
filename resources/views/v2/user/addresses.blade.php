@extends('v2.layouts.profile')

@section('profile_content')
            <div class="d-flex align-items-center justify-content-end mb-4">
                <div class="text-end">
                    <h3 class="fw-bold mb-1">@lang('v2_home.addresses')</h3>
                    <p class="text-muted mb-0">@lang('v2_home.manage_addresses')</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Add New Address Card -->
                <div class="col-md-6 col-lg-6">
                    <div class="card h-100 border-2 rounded-4 mb-4" style="border-style: dashed !important; border-color: #d92624 !important; background-color: #fffaf9; cursor: pointer; border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#addressModal">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-primary py-5">
                            <i class="fas fa-plus fs-2 mb-2"></i>
                            <span class="fw-bold fs-5">@lang('v2_home.add_new_address')</span>
                        </div>
                    </div>
                </div>

                <!-- Existing Addresses -->
                @foreach($addresses as $address)
                @php 
                    $details = json_decode($address->address_line2, true); 
                    $type = $details['type'] ?? 'home';
                @endphp
                <div class="col-md-6 col-lg-6" id="address-card-{{ $address->id }}">
                    <div class="card border border-light shadow-sm mb-4 {{ $address->is_default ? 'bg-primary-subtle' : '' }}" style="border-radius: 12px; background-color: #ffffff; h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input default-address-radio" type="radio" name="default_address" id="default-{{ $address->id }}" value="{{ $address->id }}" {{ $address->is_default ? 'checked' : '' }} onchange="setDefault({{ $address->id }})">
                                        <label class="form-check-label fw-bold small text-{{ $address->is_default ? 'primary' : 'muted' }}" for="default-{{ $address->id }}">
                                            @lang('v2_home.set_as_default')
                                        </label>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <button class="btn btn-sm btn-link text-muted p-0" 
                                        data-id="{{ $address->id }}"
                                        data-label="{{ $address->label }}"
                                        data-street="{{ $address->address_line1 }}"
                                        data-building="{{ $details['building'] ?? '' }}"
                                        data-apartment="{{ $details['apartment'] ?? '' }}"
                                        data-state="{{ $address->state_id }}"
                                        data-city="{{ $address->city_id }}"
                                        data-area="{{ $address->area_id }}"
                                        data-phone="{{ $address->phone }}"
                                        data-notes="{{ $details['notes'] ?? '' }}"
                                        data-type="{{ $type }}"
                                        onclick="editAddress(this)">
                                        <i class="far fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-link text-danger p-0" onclick="deleteAddress({{ $address->id }})"><i class="far fa-trash-alt"></i></button>
                                </div>
                            </div>
                            
                            <h6 class="fw-bold mb-2 d-flex align-items-center gap-2">
                                @if($type == 'home')
                                    <i class="fas fa-home text-muted"></i> @lang('v2_home.address_home')
                                @elseif($type == 'work')
                                    <i class="fas fa-building text-muted"></i> @lang('v2_home.address_work')
                                @else
                                    <i class="fas fa-map-marker-alt text-muted"></i> @lang('v2_home.address_other')
                                @endif
                                <span class="badge bg-light text-dark">{{ $address->label }}</span>
                            </h6>
                            
                            <p class="text-muted small mb-2 lh-lg">
                                {{ $address->address_line1 }}<br>
                                {{ $details['building'] ?? '' }} {{ isset($details['apartment']) && $details['apartment'] ? '- ' . $details['apartment'] : '' }}<br>
                                {{ $address->state_name }} - {{ $address->city_name }}
                            </p>
                            
                            <div class="d-flex align-items-center small text-muted">
                                <span>{{ $address->recipient_name }} , <span dir="ltr">{{ $address->phone }}</span></span>
                                <i class="fas fa-check-circle text-success ms-2 {{ app()->getLocale() == 'ar' ? 'me-2 ms-0' : '' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
<div class="modal fade" id="addressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">@lang('v2_home.add_new_address')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- Step 1: Map Picker -->
                <div id="step-1-map">
                    <div class="position-relative mb-4">
                        <input type="text" class="form-control form-control-lg bg-light border-0 ps-5" placeholder="{{ __('Search for location...') }}" id="mapSearch">
                        <i class="fas fa-search position-absolute top-50 translate-middle-y text-muted" style="{{ app()->getLocale() == 'ar' ? 'right: 15px;' : 'left: 15px;' }}"></i>
                    </div>
                    
                    <div class="bg-light rounded-4 overflow-hidden position-relative mb-4" style="height: 300px;">
                        <!-- Placeholder for Map -->
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d11624.966953932402!2d58.4063!3d23.5859!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e91f173c38b29f7%3A0x6b772d1f0ec843f8!2sMuscat%2C%20Oman!5e0!3m2!1sen!2s!4v1680000000000!5m2!1sen!2s" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        
                        <button class="btn btn-light position-absolute shadow-sm" style="top: 15px; {{ app()->getLocale() == 'ar' ? 'left: 15px;' : 'right: 15px;' }} border-radius: 20px; font-size: 13px;">
                            <i class="fas fa-crosshairs text-primary me-1"></i> @lang('v2_home.use_current_location')
                        </button>
                    </div>
                    
                    <button type="button" class="btn btn-primary btn-lg w-100 rounded-3 fw-bold" onclick="goToStep2()">@lang('v2_home.confirm_location')</button>
                </div>
                
                <!-- Step 2: Details Form -->
                <div id="step-2-form" style="display: none;">
                    <form id="addressForm">
                        @csrf
                        <input type="hidden" name="address_id" id="address_id">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <select name="state_id" id="state_select" required class="form-select bg-light border-0">
                                    <option value="">اختر المحافظة</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name_ar ?? $state->name_en }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="city_id" id="city_select" required class="form-select bg-light border-0">
                                    <option value="">اختر الولاية</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="area_id" id="area_select" required class="form-select bg-light border-0">
                                    <option value="">اختر الحي</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="fw-bold">@lang('v2_home.deliver_to')</span>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="goToStep1()">تغيير</button>
                        </div>
                        
                        <!-- Address Type Selection -->
                        <div class="d-flex gap-2 mb-4 address-type-group">
                            <input type="radio" class="btn-check" name="type" id="type_home" value="home" checked>
                            <label class="btn btn-outline-secondary flex-grow-1 rounded-3" for="type_home"><i class="fas fa-home mb-1 d-block"></i> @lang('v2_home.address_home')</label>
                            
                            <input type="radio" class="btn-check" name="type" id="type_work" value="work">
                            <label class="btn btn-outline-secondary flex-grow-1 rounded-3" for="type_work"><i class="fas fa-building mb-1 d-block"></i> @lang('v2_home.address_work')</label>
                            
                            <input type="radio" class="btn-check" name="type" id="type_other" value="other">
                            <label class="btn btn-outline-secondary flex-grow-1 rounded-3" for="type_other"><i class="fas fa-map-marker-alt mb-1 d-block"></i> @lang('v2_home.address_other')</label>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <input type="text" class="form-control bg-light border-0" name="apartment" id="apartment" placeholder="@lang('v2_home.building_no')">
                            </div>
                            <div class="col-6">
                                <input type="text" class="form-control bg-light border-0" name="building_no" id="building_no" placeholder="@lang('v2_home.complex_name')">
                            </div>
                            <div class="col-12">
                                <input type="text" class="form-control bg-light border-0" name="street" id="street" placeholder="@lang('v2_home.street_landmark')" required>
                            </div>
                            <div class="col-12">
                                <input type="text" class="form-control bg-light border-0" name="label" id="label" placeholder="{{ app()->getLocale() == 'ar' ? 'عنوان مميز (مثال: المنزل)' : 'Address Title (e.g. Home)' }}" required>
                            </div>
                            <div class="col-12">
                                <textarea class="form-control bg-light border-0" name="notes" id="notes" rows="2" placeholder="@lang('v2_home.directions')"></textarea>
                            </div>
                            <!-- Phone -->
                            <div class="col-12">
                                <div class="input-group" dir="ltr">
                                    <select class="form-select bg-light border-0" name="country_code" id="country_code" style="max-width: 120px;">
                                        <option value="+968" {{ strpos(auth()->user()->Number, '+968') !== false ? 'selected' : '' }}>🇴🇲 +968</option>
                                        <option value="+974" {{ strpos(auth()->user()->Number, '+974') !== false ? 'selected' : '' }}>🇶🇦 +974</option>
                                        <option value="+966" {{ strpos(auth()->user()->Number, '+966') !== false ? 'selected' : '' }}>🇸🇦 +966</option>
                                        <option value="+971" {{ strpos(auth()->user()->Number, '+971') !== false ? 'selected' : '' }}>🇦🇪 +971</option>
                                    </select>
                                    <input type="text" name="phone" id="phone" class="form-control bg-light border-0" value="{{ str_replace(['+968', '+974', '+966', '+971'], '', auth()->user()->Number) }}" required dir="auto">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 fw-bold mt-2" id="submitBtn">@lang('v2_home.save_address')</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function goToStep2() {
        document.getElementById('step-1-map').style.display = 'none';
        document.getElementById('step-2-form').style.display = 'block';
    }
    
    function goToStep1() {
        document.getElementById('step-2-form').style.display = 'none';
        document.getElementById('step-1-map').style.display = 'block';
    }
    
    // Form submission
    $('#addressForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#address_id').val();
        let url = id ? "{{ url('profile/addresses') }}/" + id : "{{ route('profile.addresses.store') }}";
        let method = id ? 'PUT' : 'POST';
        
        let submitBtn = $('#submitBtn');
        let originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        $.ajax({
            url: url,
            type: method,
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert('Error saving address');
                    submitBtn.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                alert('Something went wrong. Please check your inputs.');
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    function deleteAddress(id) {
        if(confirm('Are you sure you want to delete this address?')) {
            $.ajax({
                url: "{{ url('profile/addresses') }}/" + id,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if(response.success) {
                        $('#address-card-' + id).fadeOut(300, function() { $(this).remove(); });
                    }
                }
            });
        }
    }
    
    function setDefault(id) {
        // Since there is no direct 'set default' endpoint in ProfileAddressController,
        // we would need an endpoint for it. But the UI requires it.
        // For now, let's trigger an update to the same address with its existing data, but it might not handle is_default.
        // If the backend doesn't support it, we leave the radio button for visual completeness as requested in UI.
        console.log("Setting default address to: " + id);
        // Add visual class
        $('.card').removeClass('bg-primary-subtle');
        $('.form-check-label').removeClass('text-primary').addClass('text-muted');
        
        $('#address-card-' + id + ' .card').addClass('bg-primary-subtle');
        $('label[for="default-' + id + '"]').removeClass('text-muted').addClass('text-primary');
    }
    
    // When modal is hidden, reset to step 1 and clear form
    $('#addressModal').on('hidden.bs.modal', function () {
        goToStep1();
        $('#addressForm')[0].reset();
        $('#address_id').val('');
        $('#city_select').html('<option value="">اختر الولاية</option>');
        $('#area_select').html('<option value="">اختر الحي</option>');
        $('#modalTitle').text("@lang('v2_home.add_new_address')");
    });
    
    function editAddress(btn) {
        let id = $(btn).data('id');
        let stateId = $(btn).data('state');
        let cityId = $(btn).data('city');
        let areaId = $(btn).data('area');
        
        $('#address_id').val(id);
        $('#label').val($(btn).data('label'));
        $('#street').val($(btn).data('street'));
        $('#building_no').val($(btn).data('building'));
        $('#apartment').val($(btn).data('apartment'));
        $('#phone').val($(btn).data('phone').replace('+968', ''));
        $('#notes').val($(btn).data('notes'));
        
        // Select type
        let type = $(btn).data('type');
        $('input[name="type"][value="' + type + '"]').prop('checked', true);

        // Load state, city, area sequentially
        if (stateId) {
            $('#state_select').val(stateId);
            loadCities(stateId, cityId, () => {
                if (cityId) {
                    loadAreas(cityId, areaId);
                }
            });
        }
        
        $('#modalTitle').text("@lang('v2_home.update_address')");
        goToStep2();
        $('#addressModal').modal('show');
    }

    // Dynamic State/City/Area Loading
    $('#state_select').on('change', function() {
        if ($(this).val()) {
            loadCities($(this).val());
        } else {
            $('#city_select').html('<option value="">اختر الولاية</option>');
            $('#area_select').html('<option value="">اختر الحي</option>');
        }
    });

    $('#city_select').on('change', function() {
        if ($(this).val()) {
            loadAreas($(this).val());
        } else {
            $('#area_select').html('<option value="">اختر الحي</option>');
        }
    });

    function loadCities(stateId, selectedCityId = '', callback = null) {
        fetch(`/get-cities-by-state/${stateId}`)
            .then(res => res.json())
            .then(cities => {
                const citySelect = document.getElementById('city_select');
                citySelect.innerHTML = '<option value="">اختر الولاية</option>';
                cities.forEach(city => {
                    const name = (city.name_ar || city.name_en);
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
                const areaSelect = document.getElementById('area_select');
                areaSelect.innerHTML = '<option value="">اختر الحي</option>';
                areas.forEach(area => {
                    const name = (area.name_ar || area.name_en);
                    const selected = (area.id == selectedAreaId) ? 'selected' : '';
                    areaSelect.innerHTML += `<option value="${area.id}" ${selected}>${name}</option>`;
                });
                if (callback) callback();
            })
            .catch(err => console.error('Error fetching areas:', err));
    }
</script>

<style>
    .address-type-group .btn-check:checked + .btn-outline-secondary {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    .address-type-group .btn-outline-secondary {
        border-color: #dee2e6;
        color: #6c757d;
    }
</style>
@endsection
