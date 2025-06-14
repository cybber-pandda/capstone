$(document).ready(function () {
    let tableId = 'dynamic-animalcare-table';
    let animalCareId;

    let headers = ['Photo', 'Animal Care Question', 'Animal Care Description', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var animalCareDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/animalcaresetting/create',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'photo',
                render: function (data, type, row) {
                    if (data) {
                        return `<img src="${data}" alt="${row.fullname}" class="avatar avatar-lg rounded-circle">`;
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
                data: 'tutorialquestion',
                class: 'px-5'
            },
            {
                data: 'tutorialdescription',
                class: 'px-5',
                render: function (data, type, row) {
                    var plainText = data.replace(/<\/?[^>]+(>|$)/g, "");
                    var words = plainText.split(/\s+/);
                    var truncated = words.slice(0, 7).join(' ');

                    if (words.length > 7) {
                        truncated += '... <a href="#" class="show-full-description" data-tutorialdescription="' + plainText + '">Read more</a>';
                    }

                    return truncated;
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
        $('#animalCareModal').modal('show');
        $('#animalcare-form').attr('action', '/animalcaresetting');
        $('#animalcare-form').attr('method', 'POST');
        $('#animalcare-form')[0].reset();

        showModal($(this).data('modaltitle'));
    });

    // Handle editing an existing animal type
    $(document).on('click', '.edit-btn', function () {
        animalCareId = $(this).data('id');
        $('#tutorial_question').val($(this).data('tutorialquestion'))
        tinymce.get('description').setContent($(this).data('tutorialdescription'));

        $('#animalCareModal').modal('show');
        $('#animalcare-form').attr('action', `/animalcaresetting/${animalCareId}`);
        $('#animalcare-form').attr('method', 'POST');
        $('#animalcare-form').find('input[name="_method"]').remove();
        $('#animalcare-form').append('<input type="hidden" name="_method" value="PUT">');

        showModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.show-full-description', function (e) {
        e.preventDefault();
        var fullDescription = $(this).data('tutorialdescription');
        $('#descriptionModal .modal-body').text(fullDescription);
        $('#descriptionModal').modal('show');
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
                removeAnimalCare(id)
            }
        })

    });

    // Handle form submission
    $(document).on('click', '#saveAnimalCare', function () {


        let form = $('#animalcare-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        const descriptionContent = tinymce.get('description').getContent();
        formData.append('description', descriptionContent);

        showLoader('.saveAnimalCare');

        $('#saveAnimalCare').prop('disabled', true)

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {

                hideLoader('.saveAnimalCare');
                $('#saveAnimalCare').prop('disabled', false)
                toast(data.type, data.message)
                $('#animalCareModal').modal('hide');
                animalCareDataTable.ajax.reload();
            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveAnimalCare');
                    $('#saveAnimalCare').prop('disabled', false)
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
        $('#animalCareModal').modal('show');
        $('#animalCareModal .modal-title').text(modalTitle);
    }

    function removeAnimalCare(id) {

        $.ajax({
            type: 'DELETE',
            url: `/animalcaresetting/${id}`,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (data) {
            toast(data.type, data.message)
            $('#animalCareModal').modal('hide');
            animalCareDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });

    }

});
