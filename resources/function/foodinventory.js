$(document).ready(function () {
    let tableId = 'dynamic-foodinventory-table';
    let foodinventoryId;

    // Set headers dynamically
    let headers = ['Name', 'Category', 'Stock In', 'Stock Out', 'Remaining Stock', 'Date Created', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var foodInventoryDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/foodinventory/create',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'name',
                class: 'px-5'
            },
            {
                data: 'category',
                class: 'px-5'
            },
            {
                data: 'stockin',
                class: 'px-5'
            },
            {
                data: 'stockout',
                class: 'px-5',
                render: function (data, type, row) {
                    return data;
                }
            },
            {
                data: 'remainingstock',
                class: 'px-5'
            },
            {
                data: 'daterange',
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
            search: 'Search',
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

    $('#start-date, #end-date').on('change', function () {
        foodInventoryDataTable .ajax.url(`/foodinventory/create?start=${$('#start-date').val()}&end=${$('#end-date').val()}`).load();
    });

    $('#reset_date').on('click', function () { 
       $('#start-date').val('');
       $('#end-date').val('');

       foodInventoryDataTable.ajax.url('/foodinventory/create').load();
    });

    $(document).on('click', '#generate-pdf', function() {
        $.ajax({
            url: '/generate-pdf', // Update this to the correct route if needed
            type: 'POST',
            data: {
                start_date: $('#start-date').val(),
                end_date: $('#end-date').val(),
                table_value: 'food_inventories',
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            xhrFields: {
                responseType: 'blob' // Expect a binary response (PDF)
            },
            success: function(response) {
                // Create a Blob from the PDF Stream
                const blob = new Blob([response], {
                    type: 'application/pdf'
                });
                const url = window.URL.createObjectURL(blob);

                // Create a link to download the file
                const a = document.createElement('a');
                a.href = url;
                a.download = 'generated_report.pdf'; // Define the download name
                document.body.appendChild(a);
                a.click();

                // Cleanup
                a.remove();
                window.URL.revokeObjectURL(url);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ', status, error);
                alert('An error occurred while generating the report.');
            }
        });
    });



    $(document).on('click', '#generate-excel', function() {
        $.ajax({
            url: '/generate-excel', // Update this to the correct route if needed
            type: 'POST',
            data: {
                start_date: $('#start-date').val(),
                end_date: $('#end-date').val(),
                table_value: 'food_inventories',
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            xhrFields: {
                responseType: 'blob' // Expect a binary response (Excel)
            },
            success: function(response) {
                // Create a Blob from the Excel Stream
                const blob = new Blob([response], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' // MIME type for Excel files
                });
                const url = window.URL.createObjectURL(blob);

                // Create a link to download the file
                const a = document.createElement('a');
                a.href = url;
                a.download = 'generated_report.xlsx'; // Define the download name
                document.body.appendChild(a);
                a.click();

                // Cleanup
                a.remove();
                window.URL.revokeObjectURL(url);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ', status, error);
                alert('An error occurred while generating the report.');
            }
        });
    });

    $(document).on('click', '#add-btn', function () {
        $('#foodInventoryModal').modal('show');
        $('#food-inventory-form').attr('action', '/foodinventory');
        $('#food-inventory-form').attr('method', 'POST');
        $('#food-inventory-form')[0].reset();

        $('#data .delete-cell').show();
        $('.add-row').show();


        showFoodInventoryModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.edit-btn', function () {
        expenseId = $(this).data('id');
        $('#name').val($(this).data('name'))
        $('#stockin').val($(this).data('stockin'))
        $('#stockout').val($(this).data('stockout'))
        $('#category').val($(this).data('category'))

        $('#data .delete-cell').hide();
        $('.add-row').hide();

        $('#foodInventoryModal').modal('show');
        $('#food-inventory-form').attr('action', `/foodinventory/${expenseId}`);
        $('#food-inventory-form').attr('method', 'POST');
        $('#food-inventory-form').find('input[name="_method"]').remove();
        $('#food-inventory-form').append('<input type="hidden" name="_method" value="PUT">');

        showFoodInventoryModal($(this).data('modaltitle'));
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
                removeExpense(id)
            }
        })

    });

    $('#saveFoodInventory').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveFoodInventory');

        let form = $('#food-inventory-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        $('#saveFoodInventory').prop('disabled', true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                hideLoader('.saveFoodInventory');
                $('#food-inventory-form')[0].reset();
                $('#saveFoodInventory').prop('disabled', false);
                toast(response.type, response.message);
                $('#foodInventoryModal').modal('hide');
                foodInventoryDataTable.ajax.reload();
            },
            error: function (response) {
                hideLoader('.saveFoodInventory');
                $('#saveFoodInventory').prop('disabled', false);

                if (response.status === 422) {
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


    function showFoodInventoryModal(modalTitle) {
        $('#foodInventoryModal').modal('show');
        $('#foodInventoryModal .modal-title').text(modalTitle);
    }

    function removeExpense(id) {
        $.ajax({
            type: 'DELETE',
            url: `/foodinventory/${id}`,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (response) {
            toast(response.type, response.message)
            $('#foodInventoryModal').modal('hide');
            foodInventoryDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }


});
