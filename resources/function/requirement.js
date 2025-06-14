$(document).ready(function () {
    let tableId = 'dynamic-requirements-table';
 
    // Set headers dynamically
    let headers = ['Name', 'Type', 'Status', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var requirementDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/requirement/create',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'name',
                class: 'px-5'
            },
            {
                data: 'type',
                class: 'px-5'
            },
            {
                data: 'status',
                class: 'px-5',
                render: function (data) {
                    return data == 'active' ? 'Active' : 'Inactive';
                }
            },
            {
                data: 'actions',
                class: 'px-5',
                render: function (data) {
                    return data; // Ensure 'actions' data is rendered correctly
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
        $('#requirementModal').modal('show');
        $('#requirement-form').attr('action', '/requirement');
        $('#requirement-form').attr('method', 'POST');
        $('#requirement-form')[0].reset();

        showRequirementModal($(this).data('modaltitle'));
    });

    // Handle editing an existing animal type
    $(document).on('click', '.edit-btn', function () {
        requirementId = $(this).data('id');
        $('#requirement_name').val($(this).data('name'))
        $('#requirement_type').val($(this).data('type'))
        $('#status').val($(this).data('status'))

        $('#requirementModal').modal('show');
        $('#requirement-form').attr('action', `/requirement/${requirementId}`);
        $('#requirement-form').attr('method', 'POST');
        $('#requirement-form').find('input[name="_method"]').remove(); // Remove existing _method input if present
        $('#requirement-form').append('<input type="hidden" name="_method" value="PUT">');

        showRequirementModal($(this).data('modaltitle'));
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
                removeRequirement(id)
            }
        })

    });


    // Handle form submission
    $(document).on('click', '#saveRequirement', function () {
        let form = $('#requirement-form')[0];
        let formData = new FormData(form);

        showLoader('.saveRequirement');

        $('#saveRequirement').prop('disabled', true)

        $.ajax({
            url: $(form).attr('action'),
            method: $(form).attr('method'),
            data: formData,
            contentType: false,
            processData: false,
            success: function (data) {

                hideLoader('.saveRequirement');
                $('#saveRequirement').prop('disabled', false)
                toast(data.type, data.message)
                $('#requirementModal').modal('hide');
                requirementDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveRequirement');
                    $('#saveRequirement').prop('disabled', false)

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

    function showRequirementModal(modalTitle) {
        $('#requirementModal').modal('show');
        $('#requirementModal .modal-title').text(modalTitle);
    }

    function  removeRequirement(id) {

        $.ajax({
            type: 'DELETE',
            url: `/requirement/${id}`,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (data) {
            toast(data.type, data.message)
            $('#requirementModal').modal('hide');
            requirementDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });

    }

});
