$(document).ready(function () {
    let tableId = 'dynamic-donations-table';
    let donationId;

    // Set headers dynamically
    let headers = ['Shelter', 'Donation Amount', 'Proof of Donation', 'Status', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var donationDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/donation/create',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'sheltername',
                class: 'px-5'
            },
            {
                data: 'donationamount',
                class: 'px-5'
            },
            {
                data: 'donationproof',
                render: function (data, type, row) {
                    if (data) {
                        return `
                        <picture>
                            <source srcset="${data}" type="image/webp">
                            <img src="${data}" style="width:100px;">
                        </picture>

                            `;
                    } else {
                        return `
                        <picture>
                            <source srcset="assets/back/images/brand/logo/noimage.jpg" type="image/webp">
                            <img src="assets/back/images/brand/logo/noimage.jpg" style="width:100px;">
                        </picture>
                        `;
                    }
                }
            },
            {
                data: 'donationstatus',
                class: 'px-5'
            },
            {
                data: 'actions',
                class: 'px-5',
                render: function (data) {
                    return data;
                }
            }
        ],
        autoWidth: false,
        responsive: {
            breakpoints: [
                { name: 'desktop', width: Infinity },
                { name: 'tablet', width: 1024 },
                { name: 'phone', width: 768 }
            ]
        },
        paging: true,
        searching: true,
        ordering: false,
        info: true,
        pageLength: 10,
        dom: '<lf<t>ip>',
        language: {
            search: 'Filter',
            paginate: {
                first: '<i class="bi bi-chevron-double-left"></i>',
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>',
                last: '<i class="bi bi-chevron-double-right"></i>'
            }
        },
        fixedHeader: {
            header: true,
        },
        scrollCollapse: true,
        scrollX: true,
        scrollY: 600,
    });


    $(document).on('click', '#add-btn', function () {
        $('#donationModal').modal('show');
        $('.showNote').addClass('d-none')
        $('#donation-form').attr('action', '/donation');
        $('#donation-form').attr('method', 'POST');
        $('#donation-form')[0].reset();

        showDonationModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.edit-btn', function () {
        donationId = $(this).data('id');
        $('#shelter').val($(this).data('shelter'))

        if ($('#shelter').val()) {
            var selectedShelterId = $(this).data('shelter');
            
            $.ajax({
                type: 'POST',
                url: '/show-gcash',
                dataType: 'json',
                data: { id: selectedShelterId },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                },
                success: function (response) {
                    if (response.success) {
                        $('#gcashDetails').removeClass('d-none');
                        $('#gcash_id').val(response.data.gcash_id);
                        $('#gcash_number_display').text(response.data.gcash_number); 
                        $('#gcash_qr_display').html('<img src="' + response.data.gcash_qr + '" alt="GCash QR Code" width="200" height="200">');
                    } else {
                        $('#gcashDetails').addClass('d-none');
                        $('#gcash_id').val('');
                    
                    }
                },
                error: function (data) {
                    if (data.status === 400) {
                        $('#gcashDetails').addClass('d-none');
                        $('#gcash_id').val('');
                        toast('error', data.responseJSON.message);
                    } else {
                        console.log(data);
                    }
                }
            });
        }

        
        $('#gcash_id').val($(this).data('gcash'))
        $('#amount').val($(this).data('amount'))
        $('#status').val($(this).data('status'))

        $('#donationModal').modal('show');
        $('.showNote').removeClass('d-none')
        $('#donation-form').attr('action', `/donation/${donationId}`);
        $('#donation-form').attr('method', 'POST');
        $('#donation-form').find('input[name="_method"]').remove();
        $('#donation-form').append('<input type="hidden" name="_method" value="PUT">');

        showDonationModal($(this).data('modaltitle'));
    });


    $(document).on('click', '.delete-btn', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then((result) => {
            if (result.isConfirmed) {
                removeDonation(id)
            }
        })

    });

    $(document).on('change', '#shelter', function () {
        var selectedValue = $(this).val();
    
        $.ajax({
            type: 'POST',
            url: '/show-gcash',
            dataType: 'json',
            data: { id: selectedValue },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            success: function (response) {
                if (response.success) {
                    
                    $('#gcashDetails').removeClass('d-none');
                    $('#gcash_id').val(response.data.gcash_id);
                    $('#gcash_number_display').text(response.data.gcash_number); 
                    $('#gcash_qr_display').html('<img src="' + response.data.gcash_qr + '" alt="GCash QR Code" width="200" height="200">');
                    
                    $('#shelteraddress').text(response.data.shelter_address);
                    $('#shelterphone').text(response.data.shelter_number);
               
                } else {
                    $('#gcashDetails').addClass('d-none');
                    $('#gcash_id').val('');
                
                }
            },
            error: function (data) {
                if (data.status === 400) {
                    $('#gcashDetails').addClass('d-none');
                    $('#gcash_id').val('');
                    toast('error', data.responseJSON.message);
                } else {
                    console.log(data);
                }
            }
        });
    });
    
    $('#saveDonation').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveDonation');

        let form = $('#donation-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        $('#saveDonation').prop('disabled', true)

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveDonation');
                $('#donation-form')[0].reset();
                $('#saveDonation').prop('disabled', false)
                toast(response.type, response.message);
                $('#donationModal').modal('hide');
                donationDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveDonation');
                    $('#saveDonation').prop('disabled', false)

                    var errors = response.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $('#' + key).addClass('border-danger is-invalid');
                        $('#' + key + '_error').html('<strong>' + value[0] + '</strong>');
                    });
                } else if (response.status === 400) {  // Handling 400 Bad Request
                    alert(response.responseJSON.message);
                } else {
                    console.log(response);
                }
            }
        });
    });

    function showDonationModal(modalTitle) {
        $('#donationModal').modal('show');
        $('#donationModal .modal-title').text(modalTitle);
    }

    function removeDonation(id) {
        $.ajax({
            type: 'DELETE',
            url: `/donation/${id}`,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (response) {
            toast(response.type, response.message)
            $('#donationModal').modal('hide');
            donationDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }


});
