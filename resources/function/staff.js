$(document).ready(function () {
    let tableId = 'dynamic-staff-table';
    let staffId;

    // Set headers dynamically
    let headers = ['Profile', 'Birthday', 'Email', 'Address', 'Phone', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var staffDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/staff/create',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'profile',
                render: function (data, type, row) {
                    if (data) {
                        return `
                        <div class="d-flex align-items-center">
                            <img src="${data}" alt="${row.firstname} ${row.lastname}" class="avatar avatar-lg rounded-circle">
                            <div class="ms-2">
                              <h5 class="mb-0"><a href="#!" class="text-inherit">${row.firstname} ${row.lastname}</a></h5>
                            </div>
                        </div>
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
                data: 'bday',
                class: 'px-5'
            },
            {
                data: 'email',
                class: 'px-5'
            },
            {
                data: 'city',
                class: 'px-5',
                render: function (data, type, row) {
                    return data + ', ' + (row.state || '') + ', ' +  (row.zipcode || '');
                }
            },
            {
                data: 'phone',
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
        $('#staffModal').modal('show');
        $('.showNote').addClass('d-none')
        $('#staff-form').attr('action', '/staff');
        $('#staff-form').attr('method', 'POST');
        $('#staff-form')[0].reset();

        showStaffModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.edit-btn', function () {
        staffId = $(this).data('id');
        $('#firstname').val($(this).data('firstname'))
        $('#lastname').val($(this).data('lastname'))
        $('#bday').val($(this).data('bday'))
        $('#email').val($(this).data('email'))
        $('#city').val($(this).data('city'))
        $('#state').val($(this).data('state'))
        $('#zipcode').val($(this).data('zipcode'))
        $('#phone').val($(this).data('phone'))

        $('#staffModal').modal('show');
        $('.showNote').removeClass('d-none')
        $('#staff-form').attr('action', `/staff/${staffId}`);
        $('#staff-form').attr('method', 'POST');
        $('#staff-form').find('input[name="_method"]').remove();
        $('#staff-form').append('<input type="hidden" name="_method" value="PUT">');

        showStaffModal($(this).data('modaltitle'));
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
                removeStaff(id)
            }
        })

    });

    $('#saveStaff').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveStaff');

        let form = $('#staff-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        // if (staffId) {
        //     formData.append('staff_id', staffId);
        // }

        $('#saveStaff').prop('disabled', true)

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveStaff');
                $('#staff-form')[0].reset();
                $('#saveStaff').prop('disabled', false)
                toast(response.type, response.message);
                $('#staffModal').modal('hide');
                staffDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveStaff');
                    $('#saveStaff').prop('disabled', false)

                    var errors = response.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $('#' + key).addClass('border-danger is-invalid');
                        $('#' + key + '_error').html('<strong>' + value[0] + '</strong>');
                    });
                } else if (response.status === 400) { 
                    alert(response.responseJSON.message);
                } else {
                    console.log(response);
                }
            }
        });
    });

    function showStaffModal(modalTitle) {
        $('#staffModal').modal('show');
        $('#staffModal .modal-title').text(modalTitle);
    }

    function removeStaff(id) {
        $.ajax({
            type: 'DELETE',
            url: `/staff/${id}`,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (response) {
            toast(response.type, response.message)
            $('#staffModal').modal('hide');
            staffDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }


});
