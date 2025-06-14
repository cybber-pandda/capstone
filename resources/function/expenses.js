$(document).ready(function () {
    let tableId = 'dynamic-expenses-table';
    let expenseId;

    // Set headers dynamically
    let headers = ['Name', 'Qty', 'Price', 'Proof Receipt', 'Date Created', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var expenseDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/expense/create',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'name',
                class: 'px-5'
            },
            {
                data: 'qty',
                class: 'px-5'
            },
            {
                data: 'price',
                class: 'px-5',
                render: function (data, type, row) {
                    return data;
                }
            },
            {
                data: 'proofreceipt',
                render: function (data, type, row) {
                    if (data) {
                        return `
                            <img src="${data}" alt="Proof Receipt" class="img img-thumbnail" width="150" height="150">
                            `;
                    } else {
                        return `
                            <img src="assets/back/images/brand/logo/noimage.jpg" class="img img-thumbnail"  width="150" height="150">
                       
                        `;
                    }
                }
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
        expenseDataTable.ajax.url(`/expense/create?start=${$('#start-date').val()}&end=${$('#end-date').val()}`).load();
    });

    $('#reset_date').on('click', function () { 
        $('#start-date').val('');
        $('#end-date').val('');

        expenseDataTable.ajax.url('/expense/create').load();
     });

     $(document).on('click', '#generate-pdf', function() {
        $.ajax({
            url: '/generate-pdf', // Update this to the correct route if needed
            type: 'POST',
            data: {
                start_date: $('#start-date').val(),
                end_date: $('#end-date').val(),
                table_value: 'expenses',
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
                table_value: 'expenses',
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
        $('#expenseModal').modal('show');
        $('#expense-form').attr('action', '/expense');
        $('#expense-form').attr('method', 'POST');
        $('#expense-form')[0].reset();

        $('#data .delete-cell').show();
        $('.add-row').show();
        

        showExpenseModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.edit-btn', function () {
        expenseId = $(this).data('id');
        $('#name').val($(this).data('name'))
        $('#qty').val($(this).data('qty'))
        $('#price').val($(this).data('price'))
        
        $('#data .delete-cell').hide();
        $('.add-row').hide();

        $('#expenseModal').modal('show');
        $('#expense-form').attr('action', `/expense/${expenseId}`);
        $('#expense-form').attr('method', 'POST');
        $('#expense-form').find('input[name="_method"]').remove();
        $('#expense-form').append('<input type="hidden" name="_method" value="PUT">');

        showExpenseModal($(this).data('modaltitle'));
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

    $('#saveExpense').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveExpense');

        let form = $('#expense-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        $('#saveExpense').prop('disabled', true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                hideLoader('.saveExpense');
                $('#expense-form')[0].reset();
                $('#saveExpense').prop('disabled', false);
                toast(response.type, response.message);
                $('#expenseModal').modal('hide');
                expenseDataTable.ajax.reload(); // Assuming `expenseDataTable` is initialized elsewhere
            },
            error: function (response) {
                hideLoader('.saveExpense');
                $('#saveExpense').prop('disabled', false);

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


    function showExpenseModal(modalTitle) {
        $('#expenseModal').modal('show');
        $('#expenseModal .modal-title').text(modalTitle);
    }

    function removeExpense(id) {
        $.ajax({
            type: 'DELETE',
            url: `/expense/${id}`,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (response) {
            toast(response.type, response.message)
            $('#expenseModal').modal('hide');
            expenseDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }


});
