window.profileDashboardState = function(config) {
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || 'settings';
    
    return {
        activeTab: initialTab,
        ordersView: 'list',
        addressView: 'list',
        showOrderModal: false,
        selectedOrder: null,
        editingAddress: null,
        
        // Review Modal fields
        showReviewModal: false,
        reviewingItem: null,

        // Address Form fields
        formLabel: '',
        formStreet: '',
        formBuilding: '',
        formApartment: '',
        formCity: '',
        formPhone: '',
        formNotes: '',
        formId: null,

        addressesList: config.addresses || [],
        reviewsList: config.reviews || [],
        isRtl: config.isRtl || false,
        isAuthenticated: config.isAuthenticated || false,
        csrfToken: config.csrfToken || '',
        
        // Translation messages passed from blade
        messages: config.messages || {
            requiredFields: 'Please fill all required fields!',
            addressUpdated: 'Address updated successfully!',
            addressAdded: 'Address added successfully!',
            addressDeleted: 'Address deleted successfully!',
            confirmDelete: 'Are you sure you want to delete this address?',
            errorOccurred: 'Something went wrong!'
        },

        initAddressForm(addr = null) {
            if (addr) {
                this.formId = addr.id;
                this.formLabel = addr.title || '';
                this.formStreet = addr.street || '';
                this.formBuilding = addr.building_no || '';
                this.formApartment = addr.apartment || '';
                this.formCity = addr.city || '';
                this.formPhone = addr.phone || '';
                this.formNotes = addr.notes || '';
            } else {
                this.formId = null;
                this.formLabel = '';
                this.formStreet = '';
                this.formBuilding = '';
                this.formApartment = '';
                this.formCity = '';
                this.formPhone = '';
                this.formNotes = '';
            }
            this.addressView = 'form';
        },

        saveAddress() {
            if (!this.formLabel || !this.formStreet || !this.formCity || !this.formPhone) {
                this.triggerToast(this.messages.requiredFields, 'error');
                return;
            }

            const payload = {
                label: this.formLabel,
                street: this.formStreet,
                building_no: this.formBuilding,
                apartment: this.formApartment,
                city: this.formCity,
                phone: this.formPhone,
                notes: this.formNotes,
                type: this.formId ? (this.addressesList.find(a => a.id === this.formId)?.type || 'home') : 'home'
            };

            if (!this.isAuthenticated) {
                // Guest mode: fallback to in-memory CRUD
                if (this.formId) {
                    let index = this.addressesList.findIndex(a => a.id === this.formId);
                    if (index !== -1) {
                        this.addressesList[index].title = this.formLabel;
                        this.addressesList[index].street = this.formStreet;
                        this.addressesList[index].building_no = this.formBuilding;
                        this.addressesList[index].apartment = this.formApartment;
                        this.addressesList[index].city = this.formCity;
                        this.addressesList[index].phone = this.formPhone;
                        this.addressesList[index].notes = this.formNotes;
                    }
                    this.triggerToast(this.messages.addressUpdated + ' (Demo)', 'success');
                } else {
                    let newId = this.addressesList.length ? Math.max(...this.addressesList.map(a => a.id)) + 1 : 1;
                    this.addressesList.push({
                        id: newId,
                        title: this.formLabel,
                        type: 'home',
                        street: this.formStreet,
                        building_no: this.formBuilding,
                        apartment: this.formApartment,
                        city: this.formCity,
                        country: 'Oman',
                        phone: this.formPhone,
                        notes: this.formNotes,
                        is_default: false
                    });
                    this.triggerToast(this.messages.addressAdded + ' (Demo)', 'success');
                }
                this.addressView = 'list';
                return;
            }

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            };

            if (this.formId) {
                // Edit mode
                fetch(`/profile/addresses/${this.formId}`, {
                    method: 'PUT',
                    headers: headers,
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        let index = this.addressesList.findIndex(a => a.id === this.formId);
                        if (index !== -1) {
                            this.addressesList[index] = data.address;
                        }
                        this.triggerToast(this.messages.addressUpdated, 'success');
                        this.addressView = 'list';
                    } else {
                        this.triggerToast(data.message || this.messages.errorOccurred, 'error');
                    }
                })
                .catch(err => {
                    this.triggerToast(this.messages.errorOccurred, 'error');
                    console.error(err);
                });
            } else {
                // Add mode
                fetch('/profile/addresses', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.addressesList.push(data.address);
                        this.triggerToast(this.messages.addressAdded, 'success');
                        this.addressView = 'list';
                    } else {
                        this.triggerToast(data.message || this.messages.errorOccurred, 'error');
                    }
                })
                .catch(err => {
                    this.triggerToast(this.messages.errorOccurred, 'error');
                    console.error(err);
                });
            }
        },

        deleteAddress(id) {
            if (!confirm(this.messages.confirmDelete)) {
                return;
            }

            if (!this.isAuthenticated) {
                this.addressesList = this.addressesList.filter(a => a.id !== id);
                this.triggerToast(this.messages.addressDeleted + ' (Demo)', 'success');
                return;
            }

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            };

            fetch(`/profile/addresses/${id}`, {
                method: 'DELETE',
                headers: headers
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.addressesList = this.addressesList.filter(a => a.id !== id);
                    this.triggerToast(this.messages.addressDeleted, 'success');
                } else {
                    this.triggerToast(data.message || this.messages.errorOccurred, 'error');
                }
            })
            .catch(err => {
                this.triggerToast(this.messages.errorOccurred, 'error');
                console.error(err);
            });
        },

        reorderOrder(orderNumber) {
            if (!this.isAuthenticated) {
                this.triggerToast(this.isRtl ? 'إعادة الطلب ستتوفر بعد تسجيل الدخول!' : 'Reordering requires logging in!', 'error');
                return;
            }

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            };

            fetch(`/profile/orders/${orderNumber}/reorder`, {
                method: 'POST',
                headers: headers
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.triggerToast(this.isRtl ? 'تمت إضافة المنتجات بنجاح إلى السلة!' : 'Items added successfully to the cart!', 'success');
                    setTimeout(() => {
                        window.location.href = '/cart';
                    }, 1200);
                } else {
                    this.triggerToast(data.message || this.messages.errorOccurred, 'error');
                }
            })
            .catch(err => {
                this.triggerToast(this.messages.errorOccurred, 'error');
                console.error(err);
            });
        },

        openReviewModal(item) {
            this.reviewingItem = item;
            this.showReviewModal = true;
        },

        getProgressPercentage(status) {
            // ORDER_PENDING = 1, ORDER_PROCESSING = 2, ORDER_SHIPPED = 3, ORDER_DELIVERED = 4
            if (status == 1) return 0;
            if (status == 2) return 33;
            if (status == 3) return 66;
            if (status == 4) return 100;
            return 0;
        },

        isStepActive(order, step) {
            if (!order) return false;
            const status = order.status;
            // 5 is ORDER_CANCELLED, 8 is ORDER_DELIVERED_FAILED
            if (step === 'confirmed') {
                return status >= 1 && status != 5 && status != 8;
            }
            if (step === 'processing') {
                return status >= 2 && status != 5 && status != 8;
            }
            if (step === 'shipped') {
                return status >= 3 && status != 5 && status != 8;
            }
            if (step === 'delivered') {
                return status == 4;
            }
            return false;
        },

        previewImage: config.userImage || 'https://www.w3schools.com/howto/img_avatar.png',
        triggerToast(msg, type = 'success') {
            toastr.options = {
                'closeButton': true,
                'progressBar': true,
                'positionClass': this.isRtl ? 'toast-top-left' : 'toast-top-right',
                'timeOut': '3000'
            };
            if (type === 'success') {
                toastr.success(msg);
            } else {
                toastr.error(msg);
            }
        }
    };
};
