(function ($) {
    "use strict";
    function updateApplyButtonState() {
        var checkedBoxes = $('input[name="order_ids[]"]:checked').length;
        $("#bulk-apply-btn").prop("disabled", checkedBoxes === 0);
    }

    $(document).ready(function () {
        var table = $("#AdvertiseTable").DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: $("#table-url").data("url"),
            columns: [
                {
                    data: "id",
                    name: "id",
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        return (
                            '<input type="checkbox" name="order_ids[]" value="' +
                            data +
                            '">'
                        );
                    },
                },
                {
                    data: "DT_RowIndex",
                    name: "DT_RowIndex",
                    orderable: false,
                    searchable: false,
                },
                {
                    data: "Order_Number",
                    name: "Order_Number",
                },
                {
                    data: "order_date",
                    name: "order_date",
                },
                {
                    data: "order_time",
                    name: "order_time",
                },
                {
                    data: "User",
                    name: "User",
                },
                {
                    data: "admin",
                    name: "admin",
                },
                {
                    data: "phone_number",
                    name: "phone_number",
                    searchable: true,
                },
                // {
                //     data: "State",
                //     name: "State",
                // },
                {
                    data: "City",
                    name: "City",
                },
                // {
                //     data: 'Products',
                //     name: 'Products'
                // },
                {
                    data: "Subtotal",
                    name: "Subtotal",
                },
                {
                    data: "DeliveryCharge",
                    name: "DeliveryCharge",
                },
                {
                    data: "GrandTotal",
                    name: "GrandTotal",
                },
                {
                    data: "Coupon",
                    name: "Coupon",
                },
                {
                    data: "order_source",
                    name: "order_source",
                },
                {
                    data: "collection_method",
                    name: "collection_method",
                },
                {
                    data: "Payment_Method",
                    name: "Payment_Method",
                },
                {
                    data: "is_paid",
                    name: "is_paid",
                    render: function (data) {
                        return data == 1
                            ? '<span class="badge badge-success" style="background-color: #28a745; color: white; padding: 5px 10px; border-radius: 12px; font-weight: bold;">Paid</span>'
                            : '<span class="badge badge-danger" style="background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 12px; font-weight: bold;">Unpaid</span>';
                    }
                },
                // {
                //     data: 'digital_goods',
                //     name: 'digital_goods'
                // },
                {
                    data: "Status",
                    name: "Status",
                },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                },
            ],
            columnDefs: [
                { width: "40px", targets: 0 },   // Checkbox
                { width: "20px", targets: 1 },   // SL
                { width: "70px", targets: 2 },   // Id
                { width: "85px", targets: 3 },   // Date
                { width: "70px", targets: 4 },   // Time
                { width: "70px", targets: 5 },   // User
                { width: "100px", targets: 6 },  // Admin who created
                { width: "80px", targets: 7 },   // Phone Number
                { width: "80px", targets: 8 },   // City
                { width: "70px", targets: 9 },   // Subtotal
                { width: "80px", targets: 10 },  // Delivery Charge
                { width: "80px", targets: 11 },  // Total Amount
                { width: "60px", targets: 12 },  // Coupon Code
                { width: "80px", targets: 13 },  // Order source
                { width: "100px", targets: 14 }, // Collection Method
                { width: "80px", targets: 15 },  // Payment Method
                { width: "60px", targets: 16 },  // Is Paid
                { width: "80px", targets: 17 },  // Status
                { width: "120px", targets: 18 }, // Action
            ],
            scrollX: true,
            responsive: false,
        });

        // Select all checkbox functionality
        $("#select-all-checkbox").on("change", function () {
            $('input[name="order_ids[]"]').prop("checked", this.checked);
            updateApplyButtonState();
        });

        // Deselect "select all" if any checkbox is unchecked
        $("#AdvertiseTable").on(
            "change",
            'input[name="order_ids[]"]',
            function () {
                if (!this.checked) {
                    $("#select-all-checkbox").prop("checked", false);
                }

                updateApplyButtonState();
            }
        );

        // Bulk action form submission
        $("#bulk-action-form").on("submit", function (e) {
            e.preventDefault();
            if ($('input[name="order_ids[]"]:checked').length > 0) {
                if (
                    confirm(
                        "Are you sure you want to update the status of selected orders?"
                    )
                ) {
                    this.submit();
                }
            } else {
                alert("Please select at least one order.");
            }
        });
    });

    $("#print-now").on("click", function () {
        let w = window.open();
        w.document.write(document.getElementById("printDiv").innerHTML);
        w.print();
        w.close();
    });

    updateApplyButtonState();
})(jQuery);

function orderDetails(id) {
    $.post(
        ROUTE_ORDER_DETAILS,
        {
            _token: CSRF_TOKEN,
            id: id,
        },
        function (data) {
            $(".modal-dialog").addClass("modal-lg");
            $(".modal-dialog").removeClass("modal-sm");
            $(".modal-content").html(data);
            $("#dataModal").modal("show");
        }
    );
}

function orderStatusEdit(id) {
    $.post(
        ROUTE_ORDER_STATUS_EDIT,
        {
            _token: CSRF_TOKEN,
            id: id,
        },
        function (data) {
            $(".modal-dialog").addClass("modal-sm");
            $(".modal-dialog").removeClass("modal-lg");
            $(".modal-content").html(data);
            $("#dataModal").modal("show");
        }
    );
}
