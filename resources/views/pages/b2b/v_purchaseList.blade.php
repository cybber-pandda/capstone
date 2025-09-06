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
                <label style="font-weight: normal;">Expected Delivery Date (optional)</label>
                <input class="form-control" type="date" id="expectedDeliveryDate" name="expectedDeliveryDate" .
                    style="max-width: 300px;">
            </div>
            <div>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Item
                </a>
                <button class="btn btn-info" id="submitPR" data-prids="{{ $prIdsJson }}">
                    Submit Request
                </button>
            </div>
        </div>

        <table class="table-2">
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
                    <td><img src="{{ asset($image) }}" width="50" height="50" alt="Image"></td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->name }}</td>
                    <td>₱{{ number_format($product->discount == 0 ? $product->price : $product->discounted_price , 2) }}</td>
                    <td>
                        <center>
                            <div class="input-group" style="max-width: 130px;display: flex; align-items: center;">
                                <button class="btn btn-sm btn-outline-secondary qty-decrease">−</button>
                                <input type="text" class="form-control form-control-sm text-center item-qty"
                                    value="{{ $item->quantity }}" readonly>
                                <button class="btn btn-sm btn-outline-secondary qty-increase">+</button>
                            </div>
                        </center>
                    </td>
                    <td>₱{{ $item->subtotal }}</td>
                    <td>{{ $item->created_at->toDateTimeString() }}</td>
                    <td>
                        <center>
                            <button class="btn btn-danger btn-sm btn-remove-item">Remove</button>
                        </center>
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>

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

        // Remove Item
        $('.btn-remove-item').click(function() {
            let row = $(this).closest('tr');
            let itemId = row.data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you really want to remove this item?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
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
                            showCancelButton: false,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#d33",
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


    });
</script>
@endpush