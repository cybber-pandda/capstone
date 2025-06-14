$(document).ready(function () {
    let tableId = 'dynamic-staffschedule-table';
    let scheduleId;

    // Set headers dynamically
    let headers = ['Name', 'Time IN', 'Time OUT', 'Schedule',  'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var staffScheduleDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/schedule/create',
            dataSrc: 'data'
        },
        columns: [
        
            {
                data: 'name',
                class: 'px-5'
            },
            {
                data: 'timein',
                class: 'px-5'
            },
            {
                data: 'timeout',
                class: 'px-5'
            },
            {
                data: 'scheduleday',
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
        $('#scheduleModal').modal('show');
        $('#schedule-form').attr('action', '/schedule');
        $('#schedule-form').attr('method', 'POST');
        $('#schedule-form')[0].reset();

        showScheduleModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.edit-btn', function () {
        scheduleId = $(this).data('id');
        $('#staff').val($(this).data('staffid'))
        $('#timein').val($(this).data('timein'))
        $('#timeout').val($(this).data('timeout'))

        let scheduleDay = $(this).data('scheduleday');

        let days = scheduleDay.split(','); 
        $('input[type="checkbox"][name="day"]').prop('checked', false);
        days.forEach(function (day) {
            $('input[type="checkbox"][value="' + day.trim() + '"]').prop('checked', true); 
        });
      
        $('#scheduleModal').modal('show');
        $('#schedule-form').attr('action', `/schedule/${scheduleId}`);
        $('#schedule-form').attr('method', 'POST');
        $('#schedule-form').find('input[name="_method"]').remove();
        $('#schedule-form').append('<input type="hidden" name="_method" value="PUT">');

        showScheduleModal($(this).data('modaltitle'));
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
                removeSchedule(id)
            }
        })

    });

    $('#saveSchedule').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveSchedule');

        let form = $('#schedule-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        let selectedDays = [];
        $('input[name="day"]:checked').each(function () {
            selectedDays.push($(this).val());
        });
        formData.append('schedule_day', selectedDays.join(','));

        $('#saveSchedule').prop('disabled', true)

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveSchedule');
                $('#schedule-form')[0].reset();

                $('#schedule-form').attr('action', '');
                $('#schedule-form').attr('method', '');

                $('#saveSchedule').prop('disabled', false)
                toast(response.type, response.message);
                $('#scheduleModal').modal('hide');
                staffScheduleDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveSchedule');
                    $('#saveSchedule').prop('disabled', false)

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

    function showScheduleModal(modalTitle) {
        $('#scheduleModal').modal('show');
        $('#scheduleModal .modal-title').text(modalTitle);
    }

    function removeSchedule(id) {
        $.ajax({
            type: 'DELETE',
            url: `/schedule/${id}`,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (response) {
            toast(response.type, response.message)
            $('#scheduleModal').modal('hide');
            staffScheduleDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }


});
