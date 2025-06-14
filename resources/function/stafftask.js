$(document).ready(function () {
    let tableId = 'dynamic-stafftask-table';
    let taskId;
    let staffTaskId;

    // Set headers dynamically
    let headers = ['Staff', 'Task List', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var staffTaskDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/task/create',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'name',
                class: 'px-5'
            },
            {
                data: 'tasklist',
                class: 'px-5',
                render: function (data, type, row) {
                    return data;
                }
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
        $('#taskModal').modal('show');
        $('#task-form').attr('action', '/task');
        $('#task-form').attr('method', 'POST');
        $('#task-form')[0].reset();

        showTaskModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.edit-btn', function () {
        scheduleId = $(this).data('id');
        $('#staff').val($(this).data('staffid'))
        const tasklists = $(this).data('tasklist');

        $('#taskModal').modal('show');
        $('#task-form').attr('action', `/task/${scheduleId}`);
        $('#task-form').attr('method', 'POST');
        $('#task-form').find('input[name="_method"]').remove();
        $('#task-form').append('<input type="hidden" name="_method" value="PUT">');


        const tableBody = $('#data tbody');
        tableBody.empty();  // Clear existing rows

        if (tasklists && tasklists.length > 0) {
            tasklists.forEach(task => {
                tableBody.append(`
                    <tr>
                        <td><input type="text" class="form-control" name="task[]" value="${task.task}" /></td>
                        <td align="middle"><a type="button" value="Delete" onclick="deleteRow(this)"><i class="bi bi-trash3 fs-3"></i></a></td>
                    </tr>
                `);
            });
        }

        showTaskModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.delete-btn', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Remove this staff?',
            text: "The associated tasks will also be removed.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then((result) => {
            if (result.isConfirmed) {
                removeTask(id)
            }
        })

    });

    $(document).on('click', '.add-task-percent', function () {
        staffTaskId = $(this).data('id');

        $('#taskPercentageModal').modal('show');
        $('#task-percentage-form').attr('action', `/task-percentage/${staffTaskId}`);
        $('#task-percentage-form').attr('method', 'POST');
        $('#task-percentage-form')[0].reset();

        
    });

    $('#saveTask').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveTask');

        let form = $('#task-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        var formDataMultiple = {
            tasks: []
        };

        $('input[name="task[]"]').each(function (index) {
            var task = $(this).val();
            formDataMultiple.tasks.push(task);
        });

        // Append the questions array to formData
        formData.append('questions', JSON.stringify(formDataMultiple.tasks));

        $('#saveTask').prop('disabled', true)

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveTask');
                $('#task-form')[0].reset();
                $('#saveTask').prop('disabled', false)
                toast(response.type, response.message);
                $('#taskModal').modal('hide');
                staffTaskDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveTask');
                    $('#saveTask').prop('disabled', false)

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


    $('#saveTaskPercentage').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveTaskPercentage');

        let form = $('#task-percentage-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        $('#saveTaskPercentage').prop('disabled', true)

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveTaskPercentage');
                $('#task-percentage-form')[0].reset();
                $('#saveTaskPercentage').prop('disabled', false)
                toast(response.type, response.message);
                $('#taskPercentageModal').modal('hide');
                staffTaskDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveTaskPercentage');
                    $('#saveTaskPercentage').prop('disabled', false)

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

    function showTaskModal(modalTitle) {
        $('#taskModal').modal('show');
        $('#taskModal .modal-title').text(modalTitle);
    }

    function removeTask(id) {
        $.ajax({
            type: 'DELETE',
            url: `/task/${id}`,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (response) {
            toast(response.type, response.message)
            $('#taskModal').modal('hide');
            staffTaskDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }


});
