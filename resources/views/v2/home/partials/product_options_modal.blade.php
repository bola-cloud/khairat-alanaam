<div class="modal fade" id="productOptionsModal" tabindex="-1" aria-labelledby="productOptionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productOptionsModalLabel" style="font-weight: bold; font-size: 18px;">{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fr' ? $product->ar_Product_Name : $product->en_Product_Name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('add.to.cart') }}" method="POST" id="modal-add-to-cart-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="price" value="{{ $product->Discount_Price ?: $product->Price }}">
                    
                    @php
                        $validSizes = $product->sizes ? $product->sizes->filter(function($s) use ($lang) {
                            $name = $lang == 'fr' || $lang == 'ar' ? $s->name_ar : $s->name;
                            return !empty(trim($name));
                        })->values() : collect();
                    @endphp

                    @if($validSizes->count() > 0)
                    <!-- Sizes -->
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong style="font-size: 15px; color: #333;">{{ $lang == 'fr' || $lang == 'ar' ? 'الخيارات' : 'Options' }}</strong>
                        </div>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        @foreach($validSizes as $index => $size)
                            @php
                                $sizePrice = $size->pivot->price > 0 ? $size->pivot->price : ($product->Discount_Price ?: $product->Price);
                            @endphp
                            <label style="cursor: pointer;">
                                <input type="radio" name="size" value="{{ $size->id }}" data-price="{{ $sizePrice }}" style="display: none;" {{ $index == 0 ? 'checked' : '' }} onchange="updateModalOptions(this)">
                                <div class="option-box modal-option-box" style="padding: 10px 20px; border: {{ $index == 0 ? '2px solid var(--primary-color)' : '1px solid var(--border-color)' }}; border-radius: 8px; color: {{ $index == 0 ? 'var(--primary-color)' : 'var(--text-light)' }}; font-size: 14px; font-weight: 600; background: {{ $index == 0 ? '#fff5f5' : 'transparent' }}; transition: 0.2s;">
                                    {{ $lang == 'fr' || $lang == 'ar' ? $size->name_ar : $size->name }}
                                    @if($size->pivot->price > 0)
                                        ({{ number_format($size->pivot->price, 3) }})
                                    @endif
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
                                <input type="radio" name="addition" value="{{ $addition->id }}" data-price="{{ $addition->price ?? 0 }}" style="display: none;" {{ $index == 0 ? 'checked' : '' }} onchange="updateModalOptions(this)">
                                <div class="option-box modal-option-box" style="padding: 10px 20px; border: {{ $index == 0 ? '2px solid var(--primary-color)' : '1px solid var(--border-color)' }}; border-radius: 8px; color: {{ $index == 0 ? 'var(--primary-color)' : 'var(--text-light)' }}; font-size: 14px; font-weight: 600; background: {{ $index == 0 ? '#fff5f5' : 'transparent' }}; transition: 0.2s;">
                                    {{ $lang == 'fr' || $lang == 'ar' ? $addition->name_ar : $addition->name }}
                                    @if($addition->price > 0)
                                        (+{{ number_format($addition->price, 3) }})
                                    @endif
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Quantity -->
                    <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                        <strong style="font-size: 15px; color: #333;">{{ $lang == 'fr' || $lang == 'ar' ? 'الكمية' : 'Quantity' }}</strong>
                        <div style="display: flex; align-items: center; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; height: 40px;">
                            <button type="button" onclick="updateModalQty(1)" style="background: #f5f5f5; border: none; padding: 0 15px; height: 100%; cursor: pointer; font-size: 18px; color: #333;">+</button>
                            <input type="number" id="modal-qty-input" name="quantity" value="1" min="1" style="width: 50px; text-align: center; border: none; font-size: 16px; outline: none; height: 100%; -moz-appearance: textfield;">
                            <button type="button" onclick="updateModalQty(-1)" style="background: #f5f5f5; border: none; padding: 0 15px; height: 100%; cursor: pointer; font-size: 18px; color: #333;">-</button>
                        </div>
                    </div>

                    <!-- Customer Note -->
                    <div style="margin-bottom: 25px;">
                        <strong style="font-size: 15px; color: #333; display: block; margin-bottom: 8px;">{{ $lang == 'fr' || $lang == 'ar' ? 'ملاحظة على الطلب (اختياري)' : 'Order Note (Optional)' }}</strong>
                        <textarea name="note" rows="2" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; outline: none; resize: none;" placeholder="{{ $lang == 'fr' || $lang == 'ar' ? 'اكتب ملاحظتك هنا...' : 'Write your note here...' }}"></textarea>
                    </div>
                    
                    <button type="button" onclick="submitModalAddToCart()" class="btn-primary" style="width: 100%; padding: 15px; font-size: 16px; border-radius: 8px; border: none; font-weight: bold;">
                        <i class="fas fa-shopping-cart" style="margin-inline-end: 8px;"></i>
                        @lang('v2_home.add_to_cart')
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function updateModalOptions(input) {
        // Reset all siblings
        const name = input.name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(el => {
            let box = el.nextElementSibling;
            box.style.border = '1px solid var(--border-color)';
            box.style.color = 'var(--text-light)';
            box.style.background = 'transparent';
        });
        
        // Highlight selected
        let selectedBox = input.nextElementSibling;
        selectedBox.style.border = '2px solid var(--primary-color)';
        selectedBox.style.color = 'var(--primary-color)';
        selectedBox.style.background = '#fff5f5';

        // Calculate price
        let basePrice = parseFloat("{{ $product->Discount_Price ?: $product->Price }}");
        
        let sizeInput = document.querySelector('input[name="size"]:checked');
        if (sizeInput && sizeInput.dataset.price && parseFloat(sizeInput.dataset.price) > 0) {
            basePrice = parseFloat(sizeInput.dataset.price);
        }

        let additionInput = document.querySelector('input[name="addition"]:checked');
        if (additionInput && additionInput.dataset.price) {
            basePrice += parseFloat(additionInput.dataset.price);
        }

        document.querySelector('input[name="price"]').value = basePrice;
    }

    function updateModalQty(change) {
        let input = document.getElementById('modal-qty-input');
        let current = parseInt(input.value) || 1;
        let newValue = current + change;
        if (newValue >= 1) {
            input.value = newValue;
        }
    }

    function submitModalAddToCart() {
        let formData = $('#modal-add-to-cart-form').serialize();
        
        // Close modal
        var modalEl = document.getElementById('productOptionsModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }

        $.ajax({
            url: "{{ route('add.to.cart') }}",
            type: "POST",
            data: formData,
            success: function(data) {
                // If it returns cart modal HTML
                if(typeof openCartModal === 'function') {
                    openCartModal(data[0]);
                } else if(data && data.success) {
                    toastr.success(data.success);
                    // update cart counts
                    setTimeout(function() { location.reload(); }, 1000); 
                }
            },
            error: function(xhr) {
                if(xhr.responseJSON && xhr.responseJSON.error) {
                    toastr.error(xhr.responseJSON.error);
                } else {
                    toastr.error('حدث خطأ، يرجى المحاولة مرة أخرى');
                }
            }
        });
    }
</script>
