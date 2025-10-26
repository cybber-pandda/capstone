@extends('layouts.manualorder')

@section('content')
<div class="page-content container-xxl p-2">

    <div class="d-flex justify-content-between">
        <div>
            <h3 class="mb-5 fw-bold">{{ $page }}</h3>
        </div>
        <div>
            @auth
            @if(Auth::user()->role === 'salesofficer')
            <a href="{{ url('/salesofficer/email-manual-order') }}" class="btn btn-secondary"><i class="link-icon" data-lucide="arrow-big-left-dash"></i> Back</a>
            @endif
            @endauth
        </div>

    </div>

    @php
    $type = request('type') === 'walkin' ? 'walkin' : 'manualorder';
    @endphp


    <form id="manualOrderForm" class="p-2">
        <input type="hidden" name="customer_type" value="{{ $type }}" required>
        {{-- Top Inputs --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Customer Name</label>
                <input type="text" name="customer_name" class="form-control" required
                    pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed">
            </div>
            <div class="col-md-4">
                <label>Customer Address</label>
                <input type="text" name="customer_address" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Customer Phone Number</label>
                <input type="text" name="customer_phone_number" class="form-control" required
                    pattern="\d{1,11}" maxlength="11" title="Only numbers are allowed, up to 11 digits">
            </div>
        </div>

        <div class="row mb-3">
            @if($type != 'walkin')
            <div class="col-md-4">
                <label>Customer Email</label>
                <input type="email" name="customer_email" class="form-control" required value="{{ !empty($_GET['email']) ? $_GET['email'] : '' }}">
            </div>
            @endif
            <div class="{{ $type != 'walkin' ? 'col-md-4' : 'col-md-6' }}">
                <label>Order Date</label>
                <input type="date" name="order_date" id="orderDate" class="form-control" required> {{-- ITO --}}
            </div>
        </div>

        {{-- Dynamic Table --}}
        <table class="table table-bordered table-2" id="productTable">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Image</th>
                    <th width="15%">Qty</th>
                    <th width="15%">Price</th>
                    <th width="15%">Total</th>
                    <th width="10%">Action</th>
                </tr>
            </thead>
            <tbody>
                {{-- Initial row --}}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                    <td><input type="text" id="grandTotal" class="form-control" readonly></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <button type="button" class="btn btn-primary btn-sm mt-3" id="addRow"><i class="link-icon" data-lucide="plus"></i> Add Product</button>
        <hr>
        <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-info"><i class="link-icon" data-lucide="send"></i> Submit Order</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {


        // Disable past dates for order_date DITO
        let today = new Date();
        let yyyy = today.getFullYear();
        let mm = String(today.getMonth() + 1).padStart(2, '0');
        let dd = String(today.getDate()).padStart(2, '0');
        let minDate = `${yyyy}-${mm}-${dd}`;
        $('#orderDate').attr('min', minDate);
        // Prevent manual typing
        $('#orderDate').on('keydown paste', function(e) {
            e.preventDefault();
        });
        // Hanggang dito


        let rowIndex = 0;
        let categories = @json($categories);
        let orderid = <?php echo isset($_GET['id']) ? $_GET['id'] : '""' ?>;

        // Define newRow function first
        function newRow(index) {
            let categoryOptions = `<option value="">-- Select Category --</option>`;
            categories.forEach(cat => {
                categoryOptions += `<option value="${cat.id}">${cat.name}</option>`;
            });

            return `
        <tr>
            <td>
                <select class="form-control category" name="products[${index}][category_id]">
                    ${categoryOptions}
                </select>
            </td>
            <td>
                <select class="form-control product select2" name="products[${index}][product_id]" disabled>
                    <option value="">-- Select Product --</option>
                </select>
            </td>
            <td class="text-center">
                <img src="" class="product-image" style="max-width:60px; display:none;">
            </td>
            <td>
                <div class="input-group">
                    <button type="button" class="btn btn-danger btn-sm qty-minus"><i class="link-icon" data-lucide="minus"></i></button>
                    <input type="text" class="form-control qty text-center" name="products[${index}][qty]" value="1" min="1">
                    <button type="button" class="btn btn-dark btn-sm qty-plus"><i class="link-icon" data-lucide="plus"></i></button>
                </div>
            </td>
            <td><input type="number" class="form-control price" name="products[${index}][price]" value="0" min="0" step="any" readonly></td>
            <td><input type="text" class="form-control total" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="link-icon" data-lucide="x"></i> Remove</button></td>
        </tr>`;
        }

        // Function to update grand total
        function updateGrandTotal() {
            let grandTotal = 0;
            $(".total").each(function() {
                grandTotal += parseFloat($(this).val()) || 0;
            });
            $("#grandTotal").val(grandTotal.toFixed(2));
        }

        function initSelect2() {
            $(".select2").select2({
                width: "100%",
                placeholder: "-- Select Product --",
                allowClear: true
            });
        }

        // Add initial row on page load
        $("#productTable tbody").append(newRow(rowIndex));
        rowIndex++;
        initSelect2();

        if (typeof lucide !== "undefined") {
            lucide.createIcons();
        }

        // Add new row on button click
        $("#addRow").click(function() {
            $("#productTable tbody").append(newRow(rowIndex));
            rowIndex++;
            initSelect2();
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }
        });

        // Remove row handler
        $(document).on("click", ".removeRow", function() {
            $(this).closest("tr").remove();
            updateGrandTotal();
        });

        // Fetch products by category change
        $(document).on("change", ".category", function() {
            let row = $(this).closest("tr");
            let categoryId = $(this).val();
            let productSelect = row.find(".product");
            productSelect.prop("disabled", true).html('<option value="">Loading...</option>');

            if (categoryId) {
                $.get(`{{ url('/manual-order/products') }}/${categoryId}`, function(products) {
                    let options = `<option value="">-- Select Product --</option>`;
                    products.forEach(p => {
                        options += `<option data-price="${p.price}" data-image="${p.image || ''}" value="${p.id}">${p.name}</option>`;
                    });
                    productSelect.html(options).prop("disabled", false);
                });
            } else {
                productSelect.html('<option value="">-- Select Product --</option>').prop("disabled", true);
            }
        });

        // On product change: set price and image
        $(document).on("change", ".product", function() {
            let row = $(this).closest("tr");
            let selected = $(this).find(":selected");
            let price = selected.data("price") || 0;
            let image = selected.data("image");

            row.find(".price").val(price);
            if (image) {
                row.find(".product-image").attr("src", image).show();
            } else {
                row.find(".product-image").hide();
            }
            row.find(".qty").trigger("input");
        });

        // Qty plus button
        $(document).on("click", ".qty-plus", function() {
            let input = $(this).closest(".input-group").find(".qty");
            input.val((parseInt(input.val()) || 0) + 1).trigger("input");
        });

        // Qty minus button
        $(document).on("click", ".qty-minus", function() {
            let input = $(this).closest(".input-group").find(".qty");
            let val = parseInt(input.val()) || 0;
            if (val > 1) {
                input.val(val - 1).trigger("input");
            }
        });

        // Calculate total when qty or price changes
        $(document).on("input", ".qty, .price", function() {
            let row = $(this).closest("tr");
            let qty = parseFloat(row.find(".qty").val()) || 0;
            let price = parseFloat(row.find(".price").val()) || 0;
            row.find(".total").val((qty * price).toFixed(2));
            updateGrandTotal();
        });

        // Submit form handler
        $("#manualOrderForm").submit(function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            if (orderid) {
                formData += "&order_id=" + orderid;
            }

            $.ajax({
                url: "{{ route('manualorder.store') }}",
                method: "POST",
                data: formData,
                success: function(response) {
                    Swal.fire({
                        title: "Request Submitted!",
                        text: "Your purchase request has been submitted and is now waiting for processing. You will receive an email confirmation, and your item will be delivered to the address you specified.",
                        icon: "success",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        confirmButtonText: "OK"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ url('/') }}";
                        }
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        Swal.fire({
                            title: "Validation Error",
                            html: Object.values(xhr.responseJSON.errors)
                                .map(err => `<div>${err[0]}</div>`)
                                .join(""),
                            icon: "error"
                        });
                    } else if (xhr.status === 400) {
                        // Insufficient stock
                        Swal.fire({
                            title: "Stock Error",
                            text: xhr.responseJSON.message || "Insufficient stock for some products.",
                            icon: "error"
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: "Something went wrong. Please try again.",
                            icon: "error"
                        });
                    }
                }
            });
        });

        $(document).on('input', 'input[name="customer_name"]', function() {
            this.value = this.value.replace(/[^A-Za-z\s]/g, '');
        });
        $(document).on('input', 'input[name="customer_phone_number"]', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 11);
        });

    });
</script>
@endpush