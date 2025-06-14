$(document).ready(function () {
    let tableId = 'dynamic-shelter-table';
    let shelterId;

    // Set headers dynamically
    let headers = ['Profile', 'Owner Phone', 'Username', 'Email', 'Password', 'Shelter Name', 'Shelter Address', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var shelterDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/shelter/create',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'profile',
                render: function (data, type, row) {
                    if (data) {
                        return `
                        <div class="d-flex align-items-center">
                            <img src="${data}" alt="${row.ownername}" class="avatar avatar-lg rounded-circle">
                            <div class="ms-2">
                              <h5 class="mb-0"><a href="#!" class="text-inherit">${row.ownername}</a></h5>
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
                data: 'ownerphone',
                class: 'px-5'
            },
            {
                data: 'username',
                class: 'px-5'
            },
            {
                data: 'email',
                class: 'px-5'
            },
            {
                data: 'password',
                class: 'px-5',
                render: function (data, type, row) {
                    return data === '' ? '<span>No password set. <br>Update password for new partner.</span>' : '********';
                }
            },
            {
                data: 'sheltername',
                class: 'px-5'
            },
            {
                data: 'shelteraddress',
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
        $('#shelterModal').modal('show');
        $('.showNote').addClass('d-none')
        $('#shelter-form').attr('action', '/shelter');
        $('#shelter-form').attr('method', 'POST');
        $('#shelter-form')[0].reset();

        showShelterModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.edit-btn', function () {
        shelterId = $(this).data('id');
        $('#name').val($(this).data('name'))
        $('#phone').val($(this).data('phone'))
        $('#username').val($(this).data('username'))
        $('#email').val($(this).data('email'))
        $('#sheltername').val($(this).data('sheltername'))
        $('#shelteraddress').val($(this).data('shelteraddress'))

        $('#shelterModal').modal('show');
        $('.showNote').removeClass('d-none')
        $('#shelter-form').attr('action', `/shelter/${shelterId}`);
        $('#shelter-form').attr('method', 'POST');
        $('#shelter-form').find('input[name="_method"]').remove(); 
        $('#shelter-form').append('<input type="hidden" name="_method" value="PUT">');

        showShelterModal($(this).data('modaltitle'));
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
                removeShelter(id)
            }
        })

    });

    $('#saveShelter').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveShelter');

        let form = $('#shelter-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        $('#saveShelter').prop('disabled', true)

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveShelter');
                $('#shelter-form')[0].reset();
                $('#saveShelter').prop('disabled', false)
                toast(response.type, response.message);
                $('#shelterModal').modal('hide');
                shelterDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveShelter');
                    $('#saveShelter').prop('disabled', false)

                    var errors = response.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $('#' + key).addClass('border-danger is-invalid');
                        $('#' + key + '_error').html('<strong>' + value[0] + '</strong>');
                    });
                } else {
                    console.log(response);
                }
            }
        });
    });

    function showShelterModal(modalTitle) {
        $('#shelterModal').modal('show');
        $('#shelterModal .modal-title').text(modalTitle);
    }

    function removeShelter(id) {
        $.ajax({
            type: 'DELETE',
            url: `/shelter/${id}`,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (response) {
            toast(response.type, response.message)
            $('#shelterModal').modal('hide');
            shelterDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }

});