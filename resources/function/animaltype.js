$(document).ready(function() {
    let tableId = 'dynamic-animaltype-table';
    let animalTypeId;
  
    // Set headers dynamically
    let headers = ['Name', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var animalTypeDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/animaltype/create',
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

    // Handle adding a new animal type
    $(document).on('click', '#add-btn', function() {
        $('#animalTypeModal').modal('show');
        $('#animaltype-form').attr('action', '/animaltype');
        $('#animaltype-form').attr('method', 'POST');
        $('#animaltype-form')[0].reset(); // Reset the form fields

        showModal($(this).data('modaltitle'));
    });

    // Handle editing an existing animal type
    $(document).on('click', '.edit-btn', function() {
        animalTypeId = $(this).data('id');
        $('#name').val($(this).data('name'))

        $('#animalTypeModal').modal('show');
        $('#animaltype-form').attr('action', `/animaltype/${animalTypeId}`);
        $('#animaltype-form').attr('method', 'POST');
        $('#animaltype-form').find('input[name="_method"]').remove();
        $('#animaltype-form').append('<input type="hidden" name="_method" value="PUT">');

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
                removeAnimalType(id)
            }
        })
       
    });

    // Handle form submission
    $(document).on('click', '#saveAnimalType', function() {
        let form = $('#animaltype-form');

        showLoader('.saveAnimalType');

        $('#saveAnimalType').prop('disabled', true)

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize(),
            success: function(data) {

                hideLoader('.saveAnimalType');
                $('#saveAnimalType').prop('disabled', false)
                toast(data.type, data.message)
                $('#animalTypeModal').modal('hide');
                animalTypeDataTable.ajax.reload(); // Refresh DataTable
            },
            error: function(response) {
                if (response.status === 422) {

                    hideLoader('.saveAnimalType');
                    $('#saveAnimalType').prop('disabled', false)
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
        $('#animalTypeModal').modal('show');
        $('#animalTypeModal .modal-title').text(modalTitle);
    }

    function removeAnimalType(id){

        $.ajax({
            type: 'DELETE',
            url: `/animaltype/${id}`, 
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (data) {
            toast(data.type, data.message)
            $('#animalTypeModal').modal('hide');
            animalTypeDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });

    }

});
