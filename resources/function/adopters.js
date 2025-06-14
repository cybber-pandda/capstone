$(document).ready(function () {
    let tableId = 'dynamic-adopter-table';
    let adopterId;
    let petId;

    // Set headers dynamically
    let headers = ['Profile', 'Username', 'Email', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var adopterDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/adopter/create',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'profile',
                render: function (data, type, row) {
                    if (data) {
                        return `
                        <div class="d-flex align-items-center">
                            <img src="${data}" alt="${row.fullname}" class="avatar avatar-lg rounded-circle">
                            <div class="ms-2">
                              <h5 class="mb-0"><a href="#!" class="text-inherit">${row.fullname}</a></h5>
                            </div>
                        </div>
                            `;
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
                data: 'username',
                class: 'px-5'
            },
            {
                data: 'email',
                class: 'px-5'
            },
            // {
            //     data: 'prefered_animaltype',
            //     class: 'px-5'
            // },
            // {
            //     data: 'prefered_petgender',
            //     class: 'px-5'
            // },
            // {
            //     data: 'prefered_petsize',
            //     class: 'px-5'
            // },
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
        $('#adopterModal').modal('show');
        $('.showNote').addClass('d-none')
        $('#adopter-form').attr('action', '/adopter');
        $('#adopter-form').attr('method', 'POST');
        $('#adopter-form')[0].reset();

        showAdopterModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.edit-btn', function () {
        adopterId = $(this).data('id');
        $('#name').val($(this).data('fullname'))
        $('#bday').val($(this).data('bday'))
        $('#city').val($(this).data('city'))
        $('#state').val($(this).data('state'))
        $('#zipcode').val($(this).data('zipcode'))
        $('#phone').val($(this).data('phone'))
        $('#username').val($(this).data('username'))
        $('#email').val($(this).data('email'))
        $('#residence').val($(this).data('residence'))


        $('input[name="ownership"], input[name="petProcedure"], input[name="vaccination"], input[name="petgender"] ').prop('checked', false);

        var ownershipValue = $(this).data('ownership');
        if (ownershipValue !== null && ownershipValue !== undefined) {
            $('input[name="ownership"][value="' + ownershipValue + '"]').prop('checked', true);
        }

        $('#petType').val($(this).data('pettype'))
        $('#petAge').val($(this).data('petage'))

        var procedureValue = $(this).data('procedure');
        if (procedureValue !== null && procedureValue !== undefined) {
            $('input[name="petProcedure"][value="' + procedureValue + '"]').prop('checked', true);
        }

        var vaccinationValue = $(this).data('vaccination');
        if (vaccinationValue !== null && vaccinationValue !== undefined) {
            $('input[name="vaccination"][value="' + vaccinationValue + '"]').prop('checked', true);
        }


        // $('#animaltype').val($(this).data('animaltype'))

        // var petgenderValue = $(this).data('petgender');
        // if (petgenderValue !== null && petgenderValue !== undefined) {
        //     $('input[name="petgender"][value="' + petgenderValue + '"]').prop('checked', true);
        // }

        // $('#size').val($(this).data('petsize'))
        // $('#activitylevel').val($(this).data('activitylevel'))

        $('#aboutShelter').val($(this).data('aboutshelter'))
        $('#reasonAdopting').val($(this).data('reasonadopting'))

        $('#adopterModal').modal('show');
        $('.showNote').removeClass('d-none')
        $('#adopter-form').attr('action', `/adopter/${adopterId}`);
        $('#adopter-form').attr('method', 'POST');
        $('#adopter-form').find('input[name="_method"]').remove();
        $('#adopter-form').append('<input type="hidden" name="_method" value="PUT">');

        showAdopterModal($(this).data('modaltitle'));
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
                removeAdopter(id)
            }
        })

    });

    $(document).on('click', '.view-btn', function () {
        $('#adopterViewModal').modal('show');

        const data = {
            profile: $(this).data('profile'),
            name: $(this).data('fullname'),
            bday: $(this).data('bday'),
            username: $(this).data('username'),
            email: $(this).data('email'),
            city: $(this).data('city'),
            state: $(this).data('state'),
            zipcode: $(this).data('zipcode'),
            phone: $(this).data('phone'),
            residence: $(this).data('residence'),
            ownership: $(this).data('ownership'),
            pettype: $(this).data('pettype'),
            petage: $(this).data('petage'),
            petprocedure: $(this).data('procedure'),
            vaccination: $(this).data('vaccination'),
            // animaltype: $(this).data('animaltype'),
            // petgender: $(this).data('petgender'),
            // petsize: $(this).data('petsize'),
            // activitylevel: $(this).data('activitylevel'),
            aboutshelter: $(this).data('aboutshelter'),
            reasonadopting: $(this).data('reasonadopting')
        };

        const items = [
            { label: 'Profile', value: data.profile ? `<img src="${data.profile}" alt="Image" class="img-thumbnail" width="100">` : 'N/A', class: 'w-25' },
            { label: 'Name', value: data.name, class: 'w-25' },
            { label: 'Birthday', value: data.bday, class: 'w-25' },
            { label: 'Username', value: data.username, class: 'w-25' },
            { label: 'Email', value: data.email, class: 'w-25' },
            { label: 'City', value: data.city, class: 'w-25' },
            { label: 'State', value: data.state, class: 'w-25' },
            { label: 'Zip Code', value: data.zipcode, class: 'w-25' },
            { label: 'Phone #', value: data.phone, class: 'w-25' },
            { label: 'Residence', value: data.residence, class: 'w-25' },
            { label: 'Ownership', value: data.ownership, class: 'w-25' },
            { label: 'Pet Type', value: data.pettype, class: 'w-25' },
            { label: 'Pet Age', value: data.petage, class: 'w-25' },
            { label: 'Pet Procedure', value: data.petprocedure, class: 'w-25' },
            { label: 'Vaccination', value: data.vaccination, class: 'w-25' },
            // { label: 'Animal Type', value: data.animaltype, class: 'w-25' },
            // { label: 'Pet Gender', value: data.petgender, class: 'w-25' },
            // { label: 'Pet Size', value: data.petsize, class: 'w-25' },
            // { label: 'Pet Activity Level', value: data.activitylevel, class: 'w-25' },
            { label: 'About Shleter', value: data.aboutshelter, class: 'w-25' },
            { label: 'Reason for Adopting', value: data.reasonadopting, class: 'w-25' }
        ];

        const list = $('#adopter-details');
        list.empty();

        let addedHouseholdHeader = false;
        let addedPetPreferenceHeader = false;

        items.forEach(item => {

            const isHouseholdSection = ['Pet Type', 'Pet Age', 'Pet Procedure', 'Vaccination'].includes(item.label);

            const isPetPreferenceSection = ['Animal Type', 'Pet Gender', 'Pet Size'].includes(item.label);

            if (isHouseholdSection && !addedHouseholdHeader) {
                list.append(`
                    <h5 class="mt-3 mb-2 text-uppercase">Pet in the Household</h5>
                `);
                addedHouseholdHeader = true;
            }

            if (isPetPreferenceSection && !addedPetPreferenceHeader) {
                list.append(`
                    <h5 class="mt-3 mb-2 text-uppercase">Pet Preference</h5>
                `);
                addedPetPreferenceHeader = true;
            }


            // Append the item to the list
            list.append(`
                <div class="d-flex ${item.class} w-100">
                    <div class="fw-bold text-uppercase p-2" style="width:20%;border-bottom:1px solid black;"><span style="font-size:10px;">${item.label}:</span></div>
                    <div class="p-2" style="width:80%;border-bottom:1px solid black"><span style="font-size:11px;">${item.value}</span></div>
                </div>
            `);
        });
    });

    $('#animaltype').change(function () {
        var selectedAnimalType = $(this).val();

        $.ajax({
            url: '/filter-pet-type',
            method: 'GET',
            data: { animaltype: selectedAnimalType },
            success: function (response) {
                // Assuming the response contains an array of animals
                var animals = response.data;

                // Initialize an empty string to hold the HTML for the inline list
                var animalListHtml = '<ul class="list-inline">';

                if (animals.length > 0) {
                    // Loop through each animal in the response
                    animals.forEach(function (animal) {
                        animalListHtml += `
                            <li class="list-inline-item grabAnimal" data-animalid="${animal.id}" >
                              <div class="card rounded-0 text-center card-pet">
                                <img src="${animal.photo}" alt="${animal.name}" class="card-img-top rounded-0"  style="height: 5rem; object-fit: cover;">
                                 <div class="p-3">
                                    <h6 class="fw-bold text-uppercase">${animal.name}</h6>
                                    <span class="fw-normal"><b>Breed:</b> ${animal.breed}</span>
                                 <div>
                               </div>
                            </li>
                        `;
                    });
                } else {
                    animalListHtml += `
                        <li class="list-inline-item">
                            No data found.
                        </li>
                    `;
                }

                animalListHtml += '</ul>';

                // Insert the animal list into the modal
                $('#animalListModal .modal-body').html(animalListHtml);

                // Show the modal
                $('#animalListModal').modal('show');
            },
            error: function (xhr) {
                console.error('An error occurred:', xhr.responseText);
            }
        });
    });

    $(document).on('click', '.grabAnimal', function () { 
        petId = $(this).attr('data-animalid');
        $('#animalListModal').modal('hide');
    });

    $('#saveAdopter').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveAdopter');

        let form = $('#adopter-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        // if (adopterId) {
        //     formData.append('adopter_id', adopterId);
        // }

        if (!petId) { 
            toast('error', 'Please reselect the animal type and choose a pet for this type.');
            hideLoader('.saveAdopter');
            return;
        }

        if (petId) {
            formData.append('animal_id', petId);
        }

        $('#saveAdopter').prop('disabled', true)

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveAdopter');
                $('#adopter-form')[0].reset();
                $('#saveAdopter').prop('disabled', false)
                toast(response.type, response.message);
                $('#adopterModal').modal('hide');
                adopterDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveAdopter');
                    $('#saveAdopter').prop('disabled', false)

                    var errors = response.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $('#' + key).addClass('border-danger is-invalid');
                        $('#' + key + '_error').html('<strong>' + value[0] + '</strong>');
                    });
                } else if (response.status === 400) {  // Handling 400 Bad Request
                    alert(response.responseJSON.message);
                } else {
                    console.log(response);
                }
            }
        });
    });

    function showAdopterModal(modalTitle) {
        $('#adopterModal').modal('show');
        $('#adopterModal .modal-title').text(modalTitle);
    }

    function removeAdopter(id) {
        $.ajax({
            type: 'DELETE',
            url: `/adopter/${id}`,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (response) {
            toast(response.type, response.message)
            $('#adopterModal').modal('hide');
            adopterDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }


});
