$(document).ready(function () {
    let tableId = 'dynamic-mypet-table';


    let headers = ['Photo', 'Pet Name', 'Breed', 'Color', 'Application Date', 'Application Status', 'Note', 'Action'];

    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    let hashValue = window.location.hash.substring(1);

    // Initialize DataTable
    var mypetDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/adopter-pet-api',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'petphoto',
                class: 'px-5'
            },
            {
                data: 'petname',
                class: 'px-5'
            },
            {
                data: 'petbreed',
                class: 'px-5'
            },
            {
                data: 'petcolor',
                class: 'px-5'
            },
            {
                data: 'application_date',
                class: 'px-5'
            },
            {
                data: 'application_status',
                class: 'px-5',
                render: function (data, type, row) {
                    return (data == 'pending' ?
                        `<span class="badge badge-danger-soft showstatus" data-type="pending" data-animalid="${row.animalid}"
                          data-adoptername="${row.adoptername}" data-petname="${row.petname}" style="cursor:pointer">Pending</span>` :
                        (data == 'rejected' ?
                            `<span class="badge badge-warning-soft showstatus" data-type="rejected" data-animalid="${row.animalid}"
                          data-adoptername="${row.adoptername}" data-petname="${row.petname}" style="cursor:pointer">Rejected</span>` :
                            (data == 'under-review' ?
                                `<span class="badge badge-secondary-soft showstatus" data-type="under-review" data-animalid="${row.animalid}"
                          data-adoptername="${row.adoptername}" data-petname="${row.petname}" style="cursor:pointer">Under Review</span>` :
                                `<span class="badge badge-primary-soft showstatus"
                                
                                 data-type="approved"

                                 data-animalid="${row.animalid}"
                                 data-adoptername="${row.adoptername}" 
                                 data-petname="${row.petname}" style="cursor:pointer"

                                >Approved</span>`)));
                }
            },
            {
                data: 'application_notes',
                class: 'px-5',
                render: function (data, type, row) {
                    var plainText = data.replace(/<\/?[^>]+(>|$)/g, "");
                    var words = plainText.split(/\s+/);
                    var truncated = words.slice(0, 3).join(' ');

                    if (words.length > 3) {
                        truncated += '... <a href="#" class="show-full-description" data-servicedescription="' + plainText + '">Show more</a>';
                    }

                    return truncated;
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
        initComplete: function () {
            if (hashValue) {
                mypetDataTable.search(hashValue).draw();
            }
        }
    });


    $(document).on('click', '.reupload-btn', function () {
        
       const shelterId = $(this).data('shelterid');
   
       $('#application_id').val($(this).data('appid'));
       $('#shelter_id').val(shelterId);
   
        $('#adopterReuploadModal').modal('show');
        

        $.ajax({
            url: '/shelter-requirement-form',
            method: 'GET',
            data: {
                shelter_id: shelterId
            },
            success: function (response) {
                $('#requirement_container').empty();

                if (response.data.length > 0) {
                    response.data.forEach(function (requirement) {
                        let requirementNameCleaned = requirement.requirement_name.replace(/\s+/g, ''); // removes all spaces from the requirement name

                        $('#requirement_container').append(
                            `<div class="form-group mb-4">
                                    <label class="form-label">${requirement.requirement_name}</label>
                                    <input type="file" name="requirement_${requirementNameCleaned}" class="form-control  border-primary">
                                    <span class="invalid-feedback d-block" role="alert" id="requirement_${requirementNameCleaned}_error"></span>
                                </div>`
                        );

                    });
                } else {
                    $('#requirement_container').append('<p>No requirements found</p>');
                }
            },
            error: function (response) {
                console.log(response);
            }
        });

    });


    $('#saveAdopterReupload').on('click', function(e) {
        e.preventDefault();

        showLoader('.saveAdopterReupload');

        let form = $('#adopter-reupload-form')[0];

        let formData = new FormData(form);

        $('#saveAdopterReupload').prop('disabled', true)

        $.ajax({
            url: '/adopter-reupload-form',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            success: function(response) {

                hideLoader('.saveAdopterReupload');
                $('#adopterReuploadModal').modal('hide');
                $('#adopter-reupload-form')[0].reset();
                $('#saveAdopterReupload').prop('disabled', false)
                mypetDataTable.ajax.reload();
                toast(response.type, response.message);

            },
            error: function(response) {

                hideLoader('.saveAdopterReupload');
                $('#saveAdopterReupload').prop('disabled', false)

                if (response.status === 422) {
                    var errors = response.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $('#' + key).addClass('border-danger is-invalid');
                        $('#' + key + '_error').html('<strong>' + value[0] + '</strong>');
                    });
                } else if (response.status === 400) {

                    toast(response.responseJSON.type, response.responseJSON.message);

                } else {
                    console.log(response);
                }
            }
        });
    });


    $(document).on('click', '.view-btn', function () {

        $('#petDetailsViewModal').modal('show');

        const data = {
            petphoto: $(this).data('petphoto'),
            petname: $(this).data('petname'),
            petbreed: $(this).data('petbreed'),
            petage: $(this).data('petage'),
            petcolor: $(this).data('petcolor'),
            petmh: $(this).data('petmh'),
            petstatus: $(this).data('petstatus'),
            // preferedanimaltype: $(this).data('preferedanimaltype'),
            // preferedpetgender: $(this).data('preferedpetgender'),
            // preferedpetsize: $(this).data('preferedpetsize'),
            applicationdate: $(this).data('applicationdate'),
            applicationstatus: $(this).data('applicationstatus'),
            applicationnotes: $(this).data('applicationnotes'),
        };

        const items = [
            { label: 'Photo', value: data.petphoto ? `<img src="${data.petphoto}" alt="Image" class="img-thumbnail" width="100">` : 'N/A', class: 'w-25' },
            { label: 'Pet Name', value: data.petname, class: 'w-25' },
            { label: 'Pet Breed', value: data.petbreed, class: 'w-25' },
            { label: 'Pet Age', value: data.petage, class: 'w-25' },
            { label: 'Pet Color', value: data.petcolor, class: 'w-25' },
            { label: 'Pet Medical History', value: data.petmh, class: 'w-25' },
            { label: 'Pet Status', value: data.petstatus, class: 'w-25' },
            // { label: 'Prefered Animal Type', value: data.preferedanimaltype, class: 'w-25' },
            // { label: 'Prefered Pet Gender', value: data.preferedpetgender, class: 'w-25' },
            // { label: 'Prefered Pet Size', value: data.preferedpetsize, class: 'w-25' },
            { label: 'Application Date', value: data.applicationdate, class: 'w-25' },
            { label: 'Application Status', value: data.applicationstatus, class: 'w-25' },
            { label: 'Application Note', value: data.applicationnotes, class: 'w-25' },
        ];

        const list = $('#pet-details');
        list.empty();

        items.forEach(item => {
            // Append the item to the list
            list.append(`
                <div class="d-flex ${item.class} w-100">
                    <div class="fw-bold text-uppercase p-2" style="width:20%;border-bottom:1px solid black;"><span style="font-size:10px;">${item.label}:</span></div>
                    <div class="p-2" style="width:80%;border-bottom:1px solid black"><span style="font-size:11px;">${item.value}</span></div>
                </div>
            `);
        });

    });

    $(document).on('click', '.show-full-description', function (e) {
        e.preventDefault();
        var fullDescription = $(this).data('servicedescription');
        $('#rejectionDetailModal .modal-body').text(fullDescription);
        $('#rejectionDetailModal').modal('show');
    });

    $(document).on('click', '.on-update-review', function () {

        $('#petDetailsViewModal').modal('show');

        const data = {
            petphoto: $(this).data('petphoto'),
            petname: $(this).data('petname'),
            petbreed: $(this).data('petbreed'),
            petage: $(this).data('petage'),
            petcolor: $(this).data('petcolor'),
            petmh: $(this).data('petmh'),
            petstatus: $(this).data('petstatus'),
            preferedanimaltype: $(this).data('preferedanimaltype'),
            preferedpetgender: $(this).data('preferedpetgender'),
            preferedpetsize: $(this).data('preferedpetsize'),
            applicationdate: $(this).data('applicationdate'),
            applicationstatus: $(this).data('applicationstatus'),
            applicationnotes: $(this).data('applicationnotes'),
        };

        const items = [
            { label: 'Photo', value: data.petphoto ? `<img src="${data.petphoto}" alt="Image" class="img-thumbnail" width="100">` : 'N/A', class: 'w-25' },
            { label: 'Pet Name', value: data.petname, class: 'w-25' },
            { label: 'Pet Breed', value: data.petbreed, class: 'w-25' },
            { label: 'Pet Age', value: data.petage, class: 'w-25' },
            { label: 'Pet Color', value: data.petcolor, class: 'w-25' },
            { label: 'Pet Medical History', value: data.petmh, class: 'w-25' },
            { label: 'Pet Status', value: data.petstatus, class: 'w-25' },
            { label: 'Prefered Animal Type', value: data.preferedanimaltype, class: 'w-25' },
            { label: 'Prefered Pet Gender', value: data.preferedpetgender, class: 'w-25' },
            { label: 'Prefered Pet Size', value: data.preferedpetsize, class: 'w-25' },
            { label: 'Application Date', value: data.applicationdate, class: 'w-25' },
            { label: 'Application Status', value: data.applicationstatus, class: 'w-25' },
            { label: 'Application Note', value: data.applicationnotes, class: 'w-25' },
        ];

        const list = $('#pet-details');
        list.empty();

        items.forEach(item => {
            // Append the item to the list
            list.append(`
                <div class="d-flex ${item.class} w-100">
                    <div class="fw-bold text-uppercase p-2" style="width:20%;border-bottom:1px solid black;"><span style="font-size:10px;">${item.label}:</span></div>
                    <div class="p-2" style="width:80%;border-bottom:1px solid black"><span style="font-size:11px;">${item.value}</span></div>
                </div>
            `);
        });

        $.ajax({
            url: '/adoption-under-review-status',
            type: 'POST',
            data: {
                adoptionId: $(this).data('id'),
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            success: function (response) {
                alert(response.message)
                mypetDataTable.ajax.reload();
            },
            error: function (data) {
                console.log(data);

            }
        });

    });





    $(document).on('click', '.approve-btn', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This application will be approved.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, approve it!',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then((result) => {
            if (result.isConfirmed) {
                approveAdopter(id)
            }
        })

    });

    $(document).on('click', '.reject-btn', function () {
        let id = $(this).data('id');

        // Show SweetAlert with a textarea for the rejection reason
        Swal.fire({
            title: 'Are you sure?',
            text: "This application will be rejected.",
            icon: 'warning',
            input: 'textarea', // Use a textarea for the reason input
            inputPlaceholder: 'Enter rejection reason. For updates, provide your email for document submission.',
            inputAttributes: {
                'aria-label': 'Enter reason for rejection'
            },
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, reject it!',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then((result) => {
            if (result.isConfirmed) {
                let rejectionReason = result.value; // Get the value of the textarea
                rejectAdopter(id, rejectionReason); // Pass the reason to the reject function
            }
        });
    });


    $(document).on('click', '.showstatus', function () {
        // Get data attributes from the clicked badge
        const statusType = $(this).data('type');
        const animalId = $(this).data('animalid');
        const adoptername = $(this).data('adoptername');
        const petname = $(this).data('petname');

        $.ajax({
            url: '/adoption-application-status',
            type: 'GET',
            data: {
                statustype: statusType,
                animalId: animalId,
                adoptername: adoptername,
                petname: petname
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            success: function (response) {
                // Check if the response contains a message
                const message = response.message || 'No message available.';

                // Display SweetAlert with title and message
                Swal.fire({
                    title: 'Application Status',
                    text: message,
                    // icon: 'info',
                    confirmButtonText: 'Okay'
                });
            },
            error: function (data) {
                console.log(data);
            }
        });
    });


    mypetDataTable.ajax.reload(); // Ensure data is loaded first

    // Check for statuses that need to be displayed
    mypetDataTable.on('xhr', function () {
        const data = mypetDataTable.ajax.json().data; // Get the data from the DataTable
        const displayedStatuses = JSON.parse(localStorage.getItem('displayedStatuses')) || {}; // Load displayed statuses from localStorage

        data.forEach(item => {
            const { animalid, application_status, adoptername, petname } = item;

            // Check if the status has changed
            const previousStatus = displayedStatuses[animalid]?.status; // Get the previously stored status
            if (previousStatus !== application_status) {
                // Show SweetAlert for any status change
                $.ajax({
                    url: '/adoption-application-status',
                    type: 'GET',
                    data: {
                        statustype: application_status,
                        animalId: animalid,
                        adoptername: adoptername,
                        petname: petname
                    },
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                    },
                    success: function (response) {
                        // Check if the response contains a message
                        const message = response.message || 'No message available.';

                        // Display SweetAlert with title and message
                        Swal.fire({
                            title: 'Application Status',
                            text: message,
                            confirmButtonText: 'Okay'
                        }).then(() => {
                            // Update local storage after clicking okay
                            displayedStatuses[animalid] = { status: application_status, acknowledged: true }; // Save the current status
                            localStorage.setItem('displayedStatuses', JSON.stringify(displayedStatuses));
                        });
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });
            }
        });
    });


    function approveAdopter(id) {
        $.ajax({
            url: '/adopter-application-status',
            type: 'POST',
            data: {
                id: id,
                statustype: 'approve'
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            success: function (response) {
                toast(response.type, response.message)
                mypetDataTable.ajax.reload();
            },
            error: function (data) {
                console.log(data)
            }
        });
    }

    function rejectAdopter(id, rejectionReason) {
        $.ajax({
            url: '/adopter-application-status',
            type: 'POST',
            data: {
                id: id,
                reason: rejectionReason
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            success: function (response) {
                toast(response.type, response.message);
                mypetDataTable.ajax.reload();
            },
            error: function (data) {
                console.log(data);
            }
        });
    }

});