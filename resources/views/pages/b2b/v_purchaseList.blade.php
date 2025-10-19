@extends('layouts.shop')

@section('content')
<div class="section section-scrollable" style="margin-bottom: 20px;">
    <div class="container">

        @php
        $hasItems = $purchaseRequests->filter(fn($pr) => $pr->status === null)->sum(fn($pr) => $pr->items->count()) > 0;
        $hasPending = $purchaseRequests->contains(fn($pr) => $pr->status === 'pending');

        // Store all pending purchase request IDs in an array
        $pendingPrIds = $purchaseRequests->filter(fn($pr) => $pr->status === null)->pluck('id')->toArray();
        @endphp

        <div class="section-title text-center">
            <h3 class="title">{{ $page }}</h3><br>
        </div>

        @if ($hasItems)

        {{-- Convert the array to JSON for JavaScript usage --}}
        @php $prIdsJson = json_encode($pendingPrIds); @endphp

        <div
            style="display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
            <div>
                <label style="font-weight: normal;">Schedule Delivery (optional)</label>
                <input class="form-control" type="date" id="expectedDeliveryDate" name="expectedDeliveryDate" .
                    style="max-width: 300px;">
            </div>
            <div>
                <a href="{{ route('home') }}" class="btn btn-sm btn-add-item">
                    <i class="fa fa-plus"></i> Add Item
                </a>

                <button class="btn btn-sm btn-submit-request" id="submitPR" data-prids="{{ $prIdsJson }}">
                    Submit Request
                </button>
            </div>
        </div>

        <table class="table-2" style="font-size: 11px;">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseRequests as $pr)
                @foreach($pr->items as $item)
                @php
                $product = $item->product;
                $image = optional($product->productImages->first())->image_path ?? '/assets/shop/img/noimage.png';
                @endphp
                <tr data-id="{{ $item->id }}">
                    <td data-label="Image:">
                        <img src="{{ asset($image) }}" width="50" height="50" alt="Image">
                    </td>
                    <td data-label="SKU:">{{ $product->sku }}</td>
                    <td data-label="Product:">{{ $product->name }}</td>
                    <td data-label="Price:"
                        data-price="{{ $product->discount > 0 && $product->discounted_price ? $product->discounted_price : $product->price }}"
                        data-stock="{{ $product->current_stock }}">
                        ₱{{ number_format($product->discount > 0 && $product->discounted_price ? $product->discounted_price : $product->price, 2) }}
                    </td>
                    <td>
                        <center>
                            <div class="input-group" style="max-width: 130px; display: flex; align-items: center;">
                                <button class="btn btn-sm btn-outline-secondary qty-decrease">−</button>
                                <input type="number" class="form-control form-control-sm text-center item-qty"
                                    value="{{ $item->quantity }}" min="1">
                                <button class="btn btn-sm btn-outline-secondary qty-increase">+</button>
                            </div>
                        </center>
                    </td>
                    </td>
                    <td data-label="Subtotal:">₱{{ number_format($item->subtotal, 2) }}</td>
                    <td data-label="Date:">{{ $item->created_at->toDateTimeString() }}</td>
                    <td>
                        <center>
                            <button class="btn btn-sm btn-remove-item"
                                style="background-color:#08101e; border-color:#08101e; color:#fff;">
                                Remove
                            </button>

                        </center>
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
        <div style="height: 60px;"></div>
        @elseif ($hasPending)
        <div class="d-flex flex-column align-items-center justify-content-center text-center" style="margin: 40px 0;">
            <i class="fa fa-spinner fa-spin text-primary" style="font-size:50px;margin-bottom:20px;"></i>
            <p>Waiting for approval of your previous request...</p>
        </div>
        @else
        <div class="d-flex flex-column align-items-center justify-content-center text-center" style="margin: 40px 0;">
            <p class="mb-3">No items found in your purchase requests.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Purchase Item</a>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        const checkAddress = '<?php echo $hasAddress ? 'true' : 'false'; ?>';

        if (checkAddress === 'false') {
            Swal.fire({
                title: 'No Address Found',
                text: 'Please add a shipping address before proceeding.',
                icon: 'warning',
                confirmButtonText: 'Add Address',
                showCancelButton: false,
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/b2b/address';
                }
            });
        }

        // Quantity Increase
        $('.qty-increase').click(function(e) {
            e.preventDefault();
            let row = $(this).closest('tr');
            let itemId = row.data('id');
            let qtyInput = row.find('.item-qty');
            let quantity = parseInt(qtyInput.val()) + 1;

            updateQuantity(itemId, quantity, qtyInput);
        });

        // Quantity Decrease
        $('.qty-decrease').click(function(e) {
            e.preventDefault();
            let row = $(this).closest('tr');
            let itemId = row.data('id');
            let qtyInput = row.find('.item-qty');
            let quantity = Math.max(1, parseInt(qtyInput.val()) - 1);

            updateQuantity(itemId, quantity, qtyInput);
        });
        // ✅ Allow manual typing in the quantity box
        $('.item-qty').on('change', function() {
            let row = $(this).closest('tr');
            let itemId = row.data('id');
            let qtyInput = $(this);
            let quantity = parseInt(qtyInput.val());

            if (isNaN(quantity) || quantity < 1) {
                quantity = 1;
                qtyInput.val(quantity);
            }

            updateQuantity(itemId, quantity, qtyInput);
        });
        // ✅ Prevent invalid characters (e, E, +, -, .) in number input
        $(document).on('keydown', '.item-qty', function(e) {
            if (e.key === 'e' || e.key === 'E' || e.key === '+' || e.key === '-' || e.key === '.') {
                e.preventDefault();
            }
        });
        $(document).on('input', '.item-qty', function() {
            let value = $(this).val();

            // Remove non-digit characters
            value = value.replace(/\D/g, '');

            // Limit to 5 digits
            if (value.length > 5) {
                value = value.slice(0, 5);
            }

            // Ensure minimum of 1
            if (value === '' || parseInt(value) < 1) {
                value = 1;
            }

            $(this).val(value);
        });

        // Remove Item
        $('.btn-remove-item').click(function() {
            let row = $(this).closest('tr');
            let itemId = row.data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you really want to remove this item?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6571ff',
                cancelButtonColor: '#000a7a',
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/b2b/purchase-requests/items/' + itemId,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            toast('success', response.message);

                            if (response.purchase_request_deleted) {
                                setTimeout(() => {
                                    location.reload();
                                }, 1000);
                            } else {
                                row.remove();
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 403) {
                                toast('error', xhr.responseJSON.message);
                            } else {
                                toast('error', 'Something went wrong.');
                            }
                        }
                    });
                }
            });
        });


        $(document).on('click', '#submitPR', function() {
            const prIds = JSON.parse(this.getAttribute('data-prids'));
            const $submitBtn = $(this);

            // 1️⃣ Gather all quantities and product IDs from the table
            let valid = true;
            let itemsData = [];

            prIds.forEach(prId => {
                $(`tr[data-id]`).each(function() {
                    const row = $(this);
                    const itemId = row.data('id');
                    const qty = parseInt(row.find('.item-qty').val());
                    const available = parseInt(row.find('td[data-label="Price:"]').data('stock') || 0); // assume we add data-stock

                    if (qty > available) {
                        toast('error', `Not enough stock available. Requested: ${qty}, Available: ${available}`);
                        valid = false;
                        return false; // break out of each
                    }

                    itemsData.push({
                        itemId: itemId,
                        quantity: qty
                    });
                });
            });

            if (!valid) return; // stop submission if stock insufficient

            // 2️⃣ Disable button and submit
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Submitting...');

            $.ajax({
                url: `/b2b/purchase-requests/submit`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    prids: prIds,
                    expected_delivery_date: $('#expectedDeliveryDate').val() || null
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: "Success, Purchase Request Submitted",
                            text: response.message,
                            icon: "info",
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "Okay"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        toast('error', 'Submission failed. Please try again.');
                        $submitBtn.prop('disabled', false).text('Submit Request');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Something went wrong. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    toast('error', errorMessage);
                    $submitBtn.prop('disabled', false).text('Submit Request');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text('Submit Request');
                }
            });
        });


        // Update quantity function
        function updateQuantity(itemId, quantity, input) {
            $.ajax({
                url: '/b2b/purchase-requests/item/' + itemId,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    quantity: quantity
                },
                success: function(response) {
                    input.val(quantity);
                    toast('success', 'Quantity updated.');


                    // Update subtotal using data-price attribute
                    let price = parseFloat(input.closest('tr').find('td[data-label="Price:"]').data('price'));
                    let subtotal = quantity * price;
                    input.closest('tr').find('td[data-label="Subtotal:"]').text('₱' + subtotal.toFixed(2));


                    updateCartDropdown()

                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        toast('error', xhr.responseJSON.message);
                    } else {
                        toast('error', 'Something went wrong.');
                    }
                }
            });
        }

        // Update quantity function
        function updateQuantity(itemId, quantity, input) {
            $.ajax({
                url: '/b2b/purchase-requests/item/' + itemId,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    quantity: quantity
                },
                success: function(response) {
                    input.val(quantity);
                    toast('success', 'Quantity updated.');

                    // Update subtotal
                    let price = parseFloat(input.closest('tr').find('td:nth-child(4)').text().replace(/[^\d.]/g, ''));
                    let subtotal = quantity * price;
                    input.closest('tr').find('td:nth-child(6)').text('₱' + subtotal.toFixed(2));

                    updateCartDropdown()

                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        toast('error', xhr.responseJSON.message);
                    } else {
                        toast('error', 'Something went wrong.');
                    }
                }
            });
        }

        $('#expectedDeliveryDate').on('change', function() {
            let selectedDate = new Date(this.value);
            let today = new Date();
            today.setHours(0, 0, 0, 0); // normalize to midnight
            let diffDays = Math.ceil((selectedDate - today) / (1000 * 60 * 60 * 24));

            if (diffDays < 4) { // Example: less than 4 days warning
                Swal.fire({
                    icon: 'info',
                    title: 'Notice',
                    text: 'Please be informed that delivery on the selected date cannot be guaranteed. Our team will make every effort to deliver your order at the earliest possible time, subject to order volume.',
                    confirmButtonText: 'Understood'
                });
            }
        });
    });

    $(document).ready(function() {
        // Truncate SKU and Product Name to 2 words
        $('td[data-label="SKU:"], td[data-label="Product:"]').each(function() {
            const fullText = $(this).text().trim();
            const words = fullText.split(' ');

            if (words.length > 1) {
                const truncated = words.slice(0, 1).join(' ') + '...';
                $(this).text(truncated);
            }

            // Save full text as data attribute for alert
            $(this).attr('data-fulltext', fullText);

            // Make it clickable
            $(this).css('cursor', 'pointer');
        });

        // Click to view full text
        $('td[data-label="SKU:"], td[data-label="Product:"]').click(function() {
            const fullText = $(this).data('fulltext');
            alert(fullText);
        });
    });

    $(document).ready(function() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-based
        const dd = String(today.getDate()).padStart(2, '0');
        const minDate = `${yyyy}-${mm}-${dd}`;

        $('#expectedDeliveryDate').attr('min', minDate);
    });
</script>
@endpush