$(document).ready(function () {
    let tableId = 'dynamic-eventsettings-table';
    let eventId;
    

    // Set headers dynamically
    let headers = ['Event', 'Description', 'Start Date', 'End Date', 'Event Time', 'Location', 'Capacity', 'Status', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var eventSettingsDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/eventsettings/create',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'event_title',
                class: 'px-5'
            },
            // {
            //     data: 'question',
            //     class: 'px-5',
            //     render: function (data, type, row) {
            //         return data;
            //     }
            // },
            {
                data: 'description',
                class: 'px-5',
                render: function (data, type, row) {
                    var plainText = data.replace(/<\/?[^>]+(>|$)/g, "");
                    var words = plainText.split(/\s+/);
                    var truncated = words.slice(0, 7).join(' ');
    
                    if (words.length > 7) {
                        truncated += '... <a href="#" class="show-full-description" data-servicedescription="' + plainText + '">Read more</a>';
                    }

                    return truncated;
                }
            },
            {
                data: 'start_date',
                class: 'px-5',
                render: function (data, type, row) {
                    var date = new Date(data);
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }
            },
            {
                data: 'end_date',
                class: 'px-5',
                render: function (data, type, row) {
                    var date = new Date(data);
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }
            },
            {
                data: 'event_time',
                class: 'px-5'
            },
            {
                data: 'location',
                class: 'px-5'
            },
            {
                data: 'capacity',
                class: 'px-5'
            },
            {
                data: 'status',
                class: 'px-5',
                render: function (data, type, row) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
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
        $('#eventSettingsModal').modal('show');
        $('#event-setting-form').attr('action', '/eventsettings');
        $('#event-setting-form').attr('method', 'POST');
        $('#event-setting-form')[0].reset();

        showEventSettingsModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.edit-btn', function () {
        // Get data attributes from the clicked edit button
        eventId = $(this).data('id');
        $('#event').val($(this).data('eventtitle'));
        $('#start_date').val($(this).data('startdate'))
        $('#end_date').val($(this).data('enddate'))
        $('#event_time').val($(this).data('eventtime'))
        $('#location').val($(this).data('location'))
        $('#capacity').val($(this).data('capacity'))
        $('#status').val($(this).data('status'))
        tinymce.get('description').setContent($(this).data('description'));
        const questions = $(this).data('questions'); // Assume this is fetched with the edit button
    
        $('#eventSettingsModal').modal('show');
    
        $('#event-setting-form').attr('action', `/eventsettings/${eventId}`);
        $('#event-setting-form').attr('method', 'POST');
        $('#event-setting-form').find('input[name="_method"]').remove();
        $('#event-setting-form').append('<input type="hidden" name="_method" value="PUT">');
    
        // Clear existing rows in the table and populate with new data
        const tableBody = $('#data tbody');
        tableBody.empty();  // Clear existing rows
    
        if (questions && questions.length > 0) {
            questions.forEach(question => {
                tableBody.append(`
                    <tr>
                        <td><input type="text" class="form-control" name="question[]" value="${question.question}" /></td>
                        <td align="middle"><a type="button" value="Delete" onclick="deleteRow(this)"><i class="bi bi-trash3 fs-3"></i></a></td>
                    </tr>
                `);
            });
        }
    
        // Optional: Update modal title
        showEventSettingsModal($(this).data('modaltitle'));
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
                removeEventSetting(id)
            }
        })

    });

    // $(document).on('click', '.delete-question-btn', function() {
    //     const questionId = $(this).data('question-id');

    //     if (confirm('Are you sure you want to delete this question?')) {
    //         $.ajax({
    //             url: `/questions/${questionId}`,
    //             method: 'DELETE',
    //             success: function(response) {
    //                 alert(response.message);
    //                 eventSettingsDataTable.ajax.reload();  // Reload the DataTable to reflect the changes
    //             },
    //             error: function(error) {
    //                 alert('Failed to delete the question. Please try again.');
    //             }
    //         });
    //     }
    // });

    $(document).on('click', '.show-full-description', function (e) {
        e.preventDefault();
        var fullDescription = $(this).data('servicedescription');
        $('#descriptionModal .modal-body').text(fullDescription);
        $('#descriptionModal').modal('show');
    });

    $('#saveEventSettings').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveEventSettings');

        let form = $('#event-setting-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        if (eventId) {
            formData.append('event_id', eventId);
        }

        const descriptionContent = tinymce.get('description').getContent();
        formData.append('description', descriptionContent);

        // Collecting multiple questions into an object
        var formDataMultiple = {
            questions: []
        };

        $('input[name="question[]"]').each(function (index) {
            var question = $(this).val();
            formDataMultiple.questions.push(question);
        });

        // Append the questions array to formData
        formData.append('questions', JSON.stringify(formDataMultiple.questions));

        $('#saveEventSettings').prop('disabled', true);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                hideLoader('.saveEventSettings');
                $('#event-setting-form')[0].reset();
                $('#saveEventSettings').prop('disabled', false);
                toast(response.type, response.message);
                $('#eventSettingsModal').modal('hide');
                eventSettingsDataTable.ajax.reload();
            },
            error: function (response) {
                hideLoader('.saveEventSettings');
                $('#saveEventSettings').prop('disabled', false);

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


    function showEventSettingsModal(modalTitle) {
        $('#eventSettingsModal').modal('show');
        $('#eventSettingsModal .modal-title').text(modalTitle);
    }

    function removeEventSetting(id) {
        $.ajax({
            type: 'DELETE',
            url: `/eventsettings/${id}`,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (response) {
            toast(response.type, response.message)
            $('#eventSettingsModal').modal('hide');
            eventSettingsDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }
});


