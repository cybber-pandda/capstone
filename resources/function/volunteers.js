$(document).ready(function () {
    let tableId = 'dynamic-volunteers-table';
    let volunteerId;
    let volunteerUserId;

    // Set headers dynamically
    let headers = ['Volunteer Name', 'Event Title', 'Event Location', 'Volunteer Status', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    var volunteerDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/volunteer/create',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'volunteername',
                class: 'px-5'
            },
            {
                data: 'event',
                class: 'px-5'
            },
            {
                data: 'location',
                class: 'px-5'
            },
            {
                data: 'volunteerStatus',
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

    $(document).on('click', '.view-btn', function () {
        let rowData = volunteerDataTable.row($(this).closest('tr')).data();

        // Clear the existing rows in the modal
        $('#questionsTable tbody').empty();

        // Populate the modal with questions and answers
        if (rowData.questions && rowData.questions.length > 0) {
            rowData.questions.forEach(function (q) {
                q.answers.forEach(function (a) {
                    $('#questionsTable tbody').append(`
                        <tr>
                            <td>${q.question}</td>
                            <td>${a.answer}</td>
                        </tr>
                    `);
                });
            });
        } else {
            $('#questionsTable tbody').append(`
                <tr>
                    <td colspan="2">No questions and answers available.</td>
                </tr>
            `);
        }

        $('#qaModal').modal('show');

        $('#taskFormModal').data('eventVolunteerId', rowData.evId).data('eventUVId', rowData.eventUVId);

    });

    $(document).on('click', '#assignTask', function () {
        volunteerId = $('#taskFormModal').data('eventVolunteerId');
        volunteerUserId = $('#taskFormModal').data('eventUVId');

        $('#qaModal').modal('hide');

        $('#assign-task-form').attr('action', '/volunteer');
        $('#assign-task-form').attr('method', 'POST');
        $('#assign-task-form')[0].reset();

        $('#taskFormModal').modal('show');
    });

    $(document).on('click', '.task-list-btn', function () {

        let tasks = $(this).data('tasks');

        $('#taskTable tbody').empty();

        if (tasks && tasks.length > 0) {
            tasks.forEach(function (task) {
                $('#taskTable tbody').append(`
                    <tr>
                       <td>${task.task}</td>
                       <td>${task.taskStatus == 0 ? 'Pending' : 'Completed'}</td>
                       <td>${task.actions}</td>
                    </tr>
                `);
            });
        } else {
            $('#taskTable tbody').append(`
                <tr>
                    <td colspan="3">No tasks available.</td>
                </tr>
            `);
        }

        $('#taskListModal').modal('show');
    });

    $(document).on('click', '.edit-task-btn', function () {
        taskId = $(this).data('id');
        $('#task').val($(this).data('task'))

        $('#taskListModal').modal('hide');
        $('#singleTaskModal').modal('show');
        $('#single-edit-task-form').attr('action', `/volunteer/${taskId}`);
        $('#single-edit-task-form').attr('method', 'POST');
        $('#single-edit-task-form').find('input[name="_method"]').remove();
        $('#single-edit-task-form').append('<input type="hidden" name="_method" value="PUT">');

    });

    $(document).on('click', '.delete-task-btn', function () {

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
                $.ajax({
                    type: 'DELETE',
                    url: `/volunteer/${id}`,
                    dataType: 'json',
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    }
                }).done(function (response) {
                    toast(response.type, response.message)
                    $('#taskListModal').modal('hide');
                    volunteerDataTable .ajax.reload();
                }).fail(function (data) {
                    console.log(data)
                });
            }
        })
    });

    $('#saveSingleTask').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveSingleTask');

        let form = $('#single-edit-task-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        $('#saveSingleTask').prop('disabled', true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                hideLoader('.saveSingleTask');
                $('#single-edit-task-form')[0].reset();
                $('#saveSingleTask').prop('disabled', false);
                toast(response.type, response.message);
                $('#singleTaskModal').modal('hide');
                volunteerDataTable.ajax.reload();
            },
            error: function (response) {
                hideLoader('.saveSingleTask');
                $('#saveSingleTask').prop('disabled', false);

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



    $('#saveAssignTask').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveAssignTask');

        let form = $('#assign-task-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        if (volunteerId) {
            formData.append('volunteer_id', volunteerId);
        }

        if (volunteerUserId) {
            formData.append('volunteer_user_id', volunteerUserId);
        }

        // Collecting multiple questions into an object
        var formDataMultiple = {
            tasks: []
        };

        $('input[name="task[]"]').each(function (index) {
            var task = $(this).val();
            formDataMultiple.tasks.push(task);
        });

        // Append the questions array to formData
        formData.append('tasks', JSON.stringify(formDataMultiple.tasks));

        $('#saveAssignTask').prop('disabled', true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                hideLoader('.saveAssignTask');
                $('#assign-task-form')[0].reset();
                $('#saveAssignTask').prop('disabled', false);
                toast(response.type, response.message);
                $('#taskFormModal').modal('hide');
                volunteerDataTable.ajax.reload();
            },
            error: function (response) {
                hideLoader('.saveAssignTask');
                $('#saveAssignTask').prop('disabled', false);

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



});