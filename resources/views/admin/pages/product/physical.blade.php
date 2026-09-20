@extends('admin.master', ['menu' => 'products', 'submenu' => 'product'])
@section('title', isset($title) ? $title : '')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="breadcrumb__content">
                <div class="breadcrumb__content__left">
                    <div class="breadcrumb__title">
                        <h2>{{ __('Add Product') }}</h2>
                    </div>
                </div>
                <div class="breadcrumb__content__right">
                    <nav aria-label="breadcrumb">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Product') }}</li>
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
                            <form enctype="multipart/form-data" method="POST" action="{{ route('admin.product.store') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-xxl-6">
                                        <div class="form-vertical__item bg-style">
                                            <div class="item-top mb-30">
                                                <h2>{{ langString('en', false) . ':' }}</h2>
                                            </div>
                                            <input type="hidden" name="product_type" value="{{ PRODUCT_PHYSICAL }}">
                                            <div class="input__group mb-25">
                                                <label for="en-product-name">{{ __('Product Name') }} *</label>
                                                <input type="text" class="form-control" id="en-product-name"
                                                    name="en_product_name" value="{{ old('en_product_name') }}"
                                                    placeholder="Name">
                                                @error('en_product_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="en-product-slug">{{ __('Product Slug') }} *</label>
                                                <input type="text" class="form-control" id="en-product-slug"
                                                    name="en_product_slug" value="{{ old('en_product_slug') }}"
                                                    placeholder="Slug" readonly>
                                                @error('en_product_slug')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="exampleInputEmail1">{{ __('Category Name') }} *</label>
                                                <select class="form-control" id="en_category_name" name="en_category_name">
                                                    @php
                                                        $selectedCategoryId = null;
                                                        if (request()->has('category_slug')) {
                                                            $slugCategory = \App\Models\Admin\Category::where('en_Category_Slug', request()->get('category_slug'))
                                                                ->orWhere('fr_Category_Slug', request()->get('category_slug'))
                                                                ->first();
                                                            if ($slugCategory) {
                                                                $selectedCategoryId = $slugCategory->id;
                                                            }
                                                        }
                                                    @endphp
                                                    @foreach ($category as $item)
                                                        <option value="{{ $item->id }}" {{ (old('en_category_name') == $item->id || $selectedCategoryId == $item->id) ? 'selected' : '' }}>
                                                            {{ $item?->fr_Category_Name ?? "--" }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('en_category_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="input__group mb-25">
                                                <label for="subcategory_id">{{__("Subcategory")}}</label>
                                                <select class="form-control" id="subcategory_id" name="subcategory_id">
                                                    <option value="">{{ __('Select Subcategory') }}</option>
                                                </select>
                                                @error('subcategory_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- <div class="input__group mb-25">
                                                <label for="exampleInputEmail1">{{ __('Item Tag') }}</label>
                                                <select class="form-control" id="item_teg" name="item_teg">
                                                    <option value="">{{ __('---Select item---') }}</option>
                                                    @foreach ($item_tags as $it)
                                                    <option value="{{ $it->name }}">{{ $it->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('item_teg')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div> --}}

                                            <div class="input__group mb-25">
                                                <label for="select2Multiple">{{ __('Product Tag') }}</label>
                                                <select class="select2-multiple form-control tag_two" name="product_tag[]"
                                                    multiple="multiple">
                                                    @foreach ($tags as $tag)
                                                        <option value="{{ $tag->name }}_{{$tag->name_ar}}">{{ $tag->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('product_tag')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="input__group mb-25" style="display: flex; gap: 20px; align-items: flex-start; border: 1px solid #eee; padding: 10px; border-radius: 8px;">
                                                <div>
                                                    <input type="checkbox" id="is_package" name="is_package" value="1" {{ old('is_package') ? 'checked' : '' }}>
                                                    <label for="is_package" style="display: inline-block; margin-bottom: 0; margin-left: 5px; font-weight: bold;">{{ __('Show as Package on Homepage') }}</label>
                                                    <p style="font-size: 11px; color: #777; margin-top: 5px;">{{ __('Displays this product in the "Featured Bundles" slider on the homepage.') }}</p>
                                                </div>
                                            </div>

                                            {{-- <div class="input__group mb-25">
                                                <label for="select2Multiple">{{ __('Product Color') }}</label>
                                                <select class="select2-multiple form-control tag_two" name="color[]"
                                                    multiple="multiple">
                                                    @foreach (productColor() as $item)
                                                    <option value="{{ $item->id }}">{{ $item->Name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('color')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div> --}}

                                            <div class="input__group mb-25">
                                                <label for="select2Multiple">{{ __('Product Options') }}</label>
                                                <select class="select2-multiple form-control tag_one" name="size[]"
                                                    multiple="multiple">
                                                    @foreach (productSize() as $item)
                                                    <option value="{{ $item->id }}">{{ $item->Size }}</option>
                                                    @endforeach
                                                </select>
                                                @error('size')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="input__group mb-25">
                                                <label>{{ __('Product Option') }}</label>
                                                <div id="size-container">
                                                    <!-- Size rows will be added here dynamically -->
                                                </div>
                                                <button type="button" class="btn btn-primary" id="add-size-btn">اضافة
                                                    خيار</button>
                                                <small class="text-info d-block mt-2"><i class="fas fa-info-circle"></i> يرجى التأكد من إدخال السعر الخاص بكل خيار/حجم تقوم بإضافته لتجنب أي أخطاء أثناء الحفظ.</small>
                                            </div>


                                            <div class="input__group mb-25">
                                                <label for="qty">{{ __('Quantity') }}</label>
                                                <input type="text" class="form-control" id="qty" name="qty"
                                                    value="{{ old('qty') }}" placeholder="Quantity">
                                                @error('qty')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="price">{{ __('Price') }} *</label>
                                                <input type="number" class="form-control" id="price" name="price"
                                                    value="{{ old('price') }}" placeholder="Price" step="0.001" min="0"
                                                    required>
                                                @error('price')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="input__group mb-25">
                                                <label for="discount">{{ __('Discount (in Percentage)') }}</label>
                                                <input type="number" class="form-control" id="discount" name="discount"
                                                    value="{{ old('discount') ?? 0 }}" placeholder="Discount">
                                                @error('discount')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="discount_price">{{ __('Discount Price') }}</label>
                                                <input type="number" class="form-control" id="discount_price"
                                                    name="discount_price" value="{{ old('discount_price') }}" readonly
                                                    step="0.001" min="0">
                                                @error('discount_price')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="input__group mb-25">
                                                <label for="discount_price">{{ __('Points to add') }}</label>
                                                <input type="number" class="form-control" id="points" name="points"
                                                    value="{{ old('points') }}" placeholder="Points">
                                                @error('points')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>


                                            {{-- <div class="input__group mb-25">
                                                <label for="en_about">{{ __('About') }}</label>
                                                <textarea name="en_about" id="en_about" class="form-control"
                                                    placeholder="About">{{ old('en_about') }}</textarea>
                                                @error('en_about')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div> --}}

                                            <div class="input__group mb-25">
                                                <label for="en_description">{{ __('Description') }} *</label>
                                                <textarea name="en_description" id="summernote" class="form-control"
                                                    placeholder="Description">{{ old('en_description') }}</textarea>
                                                @error('en_description')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- <div class="input__group mb-25">
                                                <label for="en_shippingreturn">{{ __('Shipping Return') }}</label>
                                                <textarea name="en_shippingreturn" id="summernote2" class="form-control"
                                                    placeholder="Shipping Return">{{ old('en_shippingreturn') }}</textarea>
                                                @error('en_shippingreturn')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div> --}}
                                            <div class="input__group mb-25">
                                                <label
                                                    for="en_additionalinformation">{{ __('Additional Information') }}</label>
                                                <textarea name="en_additionalinformation" id="summernote3"
                                                    class="form-control">{{ old('en_additionalinformation') }}</textarea>
                                                @error('en_additionalinformation')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="primary_image">{{ __('Primary Image') }} (320x250) *</label>
                                                <input type="file" class="form-control putImage1" name="primary_image"
                                                    id="primary_image">
                                                <img src="" id="target1" />
                                                @error('primary_image')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="image_two">{{ __('Image 2') }} (320x250)</label>
                                                <input type="file" class="form-control putImage2" name="image_two"
                                                    id="image_two">
                                                <img src="" id="target2" />
                                                @error('image_two')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="image_three">{{ __('Image 3') }} (320x250)</label>
                                                <input type="file" class="form-control putImage3" name="image_three"
                                                    id="image_three">
                                                <img src="" id="target3" />
                                                @error('image_three')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="image_four">{{ __('Image 4') }} (320x250)</label>
                                                <input type="file" class="form-control putImage4" name="image_four"
                                                    id="image_four">
                                                <img src="" id="target4" />
                                                @error('image_four')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="image_five">{{ __('Image 5') }} (320x250)</label>
                                                <input type="file" class="form-control putImage5" name="image_five"
                                                    id="image_five">
                                                <img src="" id="target5" />
                                                @error('image_five')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="input__group mb-25">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" value="1"
                                                        {{ old('status', 1) == 1 ? 'checked' : '' }} name="status"
                                                        class="custom-control-input" id="customSwitch1">
                                                    <label class="custom-control-label"
                                                        for="customSwitch1">{{ __('Active') }}</label>
                                                </div>
                                            </div>
                                            <div class="input__group mb-25">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" value="1"
                                                        {{ old('today_special') == 1 ? 'checked' : '' }}
                                                        name="today_special" class="custom-control-input"
                                                        id="customSwitchTodaySpecial">
                                                    <label class="custom-control-label"
                                                        for="customSwitchTodaySpecial">{{ __('Today Special') }}</label>
                                                </div>
                                            </div>                                        </div>
                                    </div>
                                    <div class="col-xxl-6">
                                        <div class="form-vertical__item bg-style">
                                            <div class="item-top mb-30">
                                                <h2>{{ langString('fr', false) . ':' }}</h2>
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="fr-product-name">{{ __('Product Name') }} *</label>
                                                <input type="text" class="form-control" id="fr-product-name"
                                                    name="fr_product_name" value="{{ old('fr_product_name') }}"
                                                    placeholder="{{ __('Name') }}">
                                                @error('fr_product_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="input__group mb-25">
                                                <label for="fr-product-slug">{{ __('Product Slug') }} *</label>
                                                <input type="text" class="form-control" id="fr-product-slug"
                                                    name="fr_product_slug" value="{{ old('fr_product_slug') }}"
                                                    placeholder="{{ __('Slug') }}" readonly>
                                                @error('fr_product_slug')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            {{-- <div class="input__group mb-25">
                                                <label for="fr_about">{{ __('About') }}</label>
                                                <textarea name="fr_about" id="fr_about" class="form-control"
                                                    placeholder="About">{{ old('fr_about') }}</textarea>
                                                @error('fr_about')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div> --}}
                                            <div class="input__group mb-25">
                                                <label for="fr_description">{{ __('Description') }} *</label>
                                                <textarea name="fr_description" id="summernote4"
                                                    class="form-control">{{ old('fr_description') }}</textarea>
                                                @error('fr_description')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            {{--
                                            <div class="input__group mb-25">
                                                <label for="fr_shippingreturn">{{ __('Shipping Return') }}</label>
                                                <textarea name="fr_shippingreturn" id="summernote5"
                                                    class="form-control">{{ old('fr_shippingreturn') }}</textarea>
                                                @error('fr_shippingreturn')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div> --}}
                                            <div class="input__group mb-25">
                                                <label
                                                    for="fr_additionalinformation">{{ __('Additional Information') }}</label>
                                                <textarea name="fr_additionalinformation" id="summernote6"
                                                    class="form-control">{{ old('fr_additionalinformation') }}</textarea>
                                                @error('fr_additionalinformation')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                        </div>
                                    </div>
                                    <div class="input__button">
                                        <button type="submit" class="btn btn-blue">{{ __('Add') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
@push('post_scripts')
    <script src="{{ asset('backend/js/admin/products/physical-add.js') }}"></script>
    <script>
        "use strict";
        $(document).ready(function () {
            $("#summernote").summernote({
                placeholder: 'Description',
                height: 300
            });
            $('.dropdown-toggle').dropdown();
        });

        $(document).ready(function () {
            $("#summernote2").summernote({
                placeholder: 'Shipping Return',
                height: 300
            });
            $('.dropdown-toggle').dropdown();
        });
        $(document).ready(function () {
            $("#summernote3").summernote({
                placeholder: 'Additional Information',
                height: 300
            });
            $('.dropdown-toggle').dropdown();
        });
        $(document).ready(function () {
            $("#summernote4").summernote({
                placeholder: 'Description',
                height: 300
            });
            $('.dropdown-toggle').dropdown();
        });

        $(document).ready(function () {
            $("#summernote5").summernote({
                placeholder: 'Shipping Return',
                height: 300
            });
            $('.dropdown-toggle').dropdown();
        });
        $(document).ready(function () {
            $("#summernote6").summernote({
                placeholder: 'Additional Information',
                height: 300
            });
            $('.dropdown-toggle').dropdown();
        });
    </script>

    <script>
        $(document).ready(function () {
            let sizeCounter = 0;

            // Function to create a new size row
            function createSizeRow(sizeId = null, price = null, weight = null) {
                sizeCounter++;
                const sizeRow = `
                                                                                                                                <div class="row mb-3" id="size-row-${sizeCounter}">
                                                                                                                                    <div class="col-md-5">
                                                                                                                                         <label for="size-${sizeCounter}"> الخيار:</label>
                                                        <select class="form-control" name="size[]" id="size-${sizeCounter}">
                                                                                                                                            @foreach (productSize() as $item)
                                                                                                                                                <option value="{{ $item->id }}" ${sizeId == {{ $item->id }} ? 'selected' : ''}>{{ $item->Size_ar }}</option>
                                                                                                                                            @endforeach
                                                                                                                                        </select>
                                                                                                                                    </div>
                                                                                                                                     <div class="col-md-6">
                                                                                                                                                     <label for="price-${sizeCounter}">السعر:</label>

                                                                                                                                        <input type="number" step="0.01" class="form-control" required name="size_price[]"  id="price-${sizeCounter}" placeholder="ادخل السعر" value="${price || ''}">
                                                                                                                                    </div>

                                                                                                                                    <div class="col-md-1">
                                                                                                                                        <label class="d-block">&nbsp;</label>
                                                                                                                                        <button type="button" class="btn btn-danger remove-size-row" data-row-id="size-row-${sizeCounter}">
                                                                                                                                            <i class="fa fa-times"></i>
                                                                                                                                        </button>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                            `;

                $('#size-container').append(sizeRow);
            }

            // Add initial size row if no existing sizes
            createSizeRow();


            // Add event listener for the "Add Size" button
            $('#add-size-btn').click(function () {
                createSizeRow();
            });

            // Add event listener for removing size rows
            $(document).on('click', '.remove-size-row', function () {
                const rowId = $(this).data('row-id');
                $(`#${rowId}`).remove();
            });
        });
    </script>




    <script>
                                        $(document).ready(function () {
                                            $('#en_category_name').change(function () {
                                                var categoryId = $(this).val();
                                                if (categoryId) {
                                                    $.ajax({
                                                        url: '{{ route("admin.subcategory.all") }}',
                                                        type: 'GET',
                                                        data: { category_id: categoryId },
                                                        success: function (data) {
                                                            $('#subcategory_id').empty();
                                                            $('#subcategory_id').append('<option value="">{{ __("Select Subcategory") }}</option>');
                                                            $.each(data, function (key, value) {
                                                                $('#subcategory_id').append('<option value="' + value.id + '">' + value.name_ar + '</option>');
                                                            });
                                                        }
                                                    });
                                                } else {
                                                    $('#subcategory_id').empty();
                                                    $('#subcategory_id').append('<option value="">{{ __("Select Subcategory") }}</option>');
                                                }
                                            });

                                            // Trigger change on page load to fetch subcategories if category is preselected
                                            if ($('#en_category_name').val()) {
                                                $('#en_category_name').trigger('change');
                                            }
                                        });
                                    </script>


@endpush