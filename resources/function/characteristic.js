$(document).ready(function() {
    let tableId = 'dynamic-characteristic-table';
    let characteristicId;

    let headers = ['Name', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var characteristicDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/characteristic/create',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'name',
                class: 'px-5'
            },
            {
                data: 'actions',
                class: 'px-5',
                render: function(data) {
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

    $(document).on('click', '#add-btn', function() {
        $('#characteristicModal').modal('show');
        $('#characteristic-form').attr('action', '/characteristic');
        $('#characteristic-form').attr('method', 'POST');
        $('#characteristic-form')[0].reset(); 

        showModal($(this).data('modaltitle'));
    });

    // Handle editing an existing animal type
    $(document).on('click', '.edit-btn', function() {
        characteristicId = $(this).data('id');
        $('#name').val($(this).data('name'))

        $('#characteristicModal').modal('show');
        $('#characteristic-form').attr('action', `/characteristic/${characteristicId}`);
        $('#characteristic-form').attr('method', 'PUT');

        showModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.delete-btn', function() {
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
                removeCharacteristic(id)
            }
        })
       
    });

    // Handle form submission
    $(document).on('click', '#saveCharacteristic', function() {
        let form = $('#characteristic-form');

        showLoader('.saveCharacteristic');

        $('#saveCharacteristic').prop('disabled', true)

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize(),
            success: function(data) {

                hideLoader('.saveCharacteristic');
                $('#saveCharacteristic').prop('disabled', false)
                toast(data.type, data.message)
                $('#characteristicModal').modal('hide');
                characteristicDataTable.ajax.reload(); 
            },
            error: function(response) {
                if (response.status === 422) {

                    hideLoader('.saveCharacteristic');
                    $('#saveCharacteristic').prop('disabled', false)
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

    function showModal(modalTitle) {
        $('#characteristicModal').modal('show');
        $('#characteristicModal .modal-title').text(modalTitle);
    }

    function removeCharacteristic(id){

        $.ajax({
            type: 'DELETE',
            url: `/characteristic/${id}`, 
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (data) {
            toast(data.type, data.message)
            $('#characteristicModal').modal('hide');
            characteristicDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });

    }

});
