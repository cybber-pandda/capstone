$(document).ready(function () {
    let tableId = 'dynamic-pets-table';
    let animalId;
    let petdata;


    // Set headers dynamically
    let headers = ['Photo', 'Name', 'Species', 'Breed', 'Age', 'Gender', 'Color', 'Size', 'Character', 'Created At', 'Status', 'Action'];
    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    if (window.location.pathname === '/rescued-pet') {
        petdata = 'rescueddata'
    } else if (window.location.pathname === '/surrendered-pet') {
        petdata = 'surrendereddata'
    } else if (window.location.pathname === '/incare-pet') {
        petdata = 'incaredata'
    } else if (window.location.pathname === '/adopted-pet') {
        petdata = 'adopteddata'
    } else {
        petdata = 'alldata';
    }

    // Initialize DataTable
    var animalDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: `/pet/create?data=${petdata}`, // add here the array selected filter
            dataSrc: 'data'
        },
        columns: [
            // {
            //     data: 'photo',
            //     render: function (data, type, row) {
            //         if (data) {
            //             return `
            //                 <div class="d-flex align-items-center">
            //                     <img src="${data}" alt="${row.menu}" class="avatar avatar-lg rounded-circle">
            //                     <div class="ms-2">
            //                       <h5 class="mb-0"><a href="#!" class="text-inherit">${row.name}</a></h5>
            //                     </div>
            //                 </div>
            //                     `;
            //         } else {
            //             return `
            //             <picture>
            //                 <source srcset="assets/back/images/brand/logo/noimage.jpg" type="image/webp">
            //                 <img src="assets/back/images/brand/logo/noimage.jpg" style="width:100px;">
            //             </picture>
            //             `;
            //         }
            //     }
            // },


            {
                data: 'photos',
            },

            {
                data: 'name',
            },

            {
                data: 'species',
                class: 'px-5'
            },
            {
                data: 'breed',
                class: 'px-5'
            },
            {
                data: 'age',
                class: 'px-5',
                render: function (data) {
                    return data + (data === 1 ? ' year old' : ' years old');
                }
            },
            {
                data: 'gender',
                class: 'px-5'
            },
            {
                data: 'color',
                class: 'px-5'
            },
            {
                data: 'size',
                class: 'px-5'
            },
            {
                data: 'characteristics',
                class: 'px-5'
            },
            {
                data: 'datecreated',
                class: 'px-5'
            },
            {
                data: 'status',
                class: 'px-5'
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

    // Filter functionality
    $('#searchpet').on('change', function () {
        const selectedOptions = $(this).val(); // Get selected options
        const queryParams = selectedOptions ? selectedOptions.join(',') : ''; // Convert to comma-separated string

        // Reload DataTable with selected filters
        animalDataTable.ajax.url(`/pet/create?data=${petdata}&filters=${queryParams}`).load();
    });

    $('.js-example-placeholder-multiple').select2({
        placeholder: "Filter",
        allowClear: true
    });


    $('#start-date, #end-date').on('change', function () {
        animalDataTable.ajax.url(`/pet/create?data=${petdata}&start=${$('#start-date').val()}&end=${$('#end-date').val()}`).load();
    });

    $(document).on('click', '#add-btn', function () {
        $('#animalModal').modal('show');
        $('.showNote').addClass('d-none')
        $('#animal-form').attr('action', '/pet');
        $('#animal-form').attr('method', 'POST');
        $('#animal-form')[0].reset();

        showAnimalModal($(this).data('modaltitle'));
    });

    // Handle editing an existing animal type
    $(document).on('click', '.edit-btn', function () {
        animalId = $(this).data('id');
        $('#video_url').val($(this).data('video'))
        $('#name').val($(this).data('name'))
        $('#species').val($(this).data('species'))
        $('#breed').val($(this).data('breed'))
        $('#age').val($(this).data('age'))
        $('#gender').val($(this).data('gender'))
        $('#color').val($(this).data('color'))
        $('#size').val($(this).data('size'))
        $('#characteristics').val($(this).data('characteristics'))
        tinymce.get('rescued_note').setContent($(this).data('renote'));
        tinymce.get('surrendered_note').setContent($(this).data('sunote'));
        tinymce.get('description').setContent($(this).data('medicalhistory'));
        tinymce.get('ps_description').setContent($(this).data('petstory'));
        $('#shelter').val($(this).data('shelter'))
        $('#video_url').val($(this).data('video_url'))
        $('#status').val($(this).data('status'))


        var status = $(this).data('status');
        if (status === 'rescued') {
            $('#rescued-box').removeClass('d-none');
            $('#surrendered-box').addClass('d-none');
        } else if (status === 'surrendered') {
            $('#surrendered-box').removeClass('d-none');
            $('#rescued-box').addClass('d-none');
        } else {
            // Hide both notes if neither 'rescued' nor 'surrendered'
            $('#rescued-box').addClass('d-none');
            $('#surrendered-box').addClass('d-none');
        }

        $('#animalModal').modal('show');
        $('.showNote').removeClass('d-none')
        $('#animal-form').attr('action', `/pet/${animalId}`);
        $('#animal-form').attr('method', 'POST');
        $('#animal-form').find('input[name="_method"]').remove(); // Remove existing _method input if present
        $('#animal-form').append('<input type="hidden" name="_method" value="PUT">');

        showAnimalModal($(this).data('modaltitle'));
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
                removeAnimal(id)
            }
        })

    });


    $('#status').on('change', function () {
        var selectedStatus = $(this).val();

        if (selectedStatus === 'rescued') {
            $('#rescued-box').removeClass('d-none');
            $('#surrendered-box').addClass('d-none');
            tinymce.get('surrendered_note').setContent('');
        } else if (selectedStatus === 'surrendered') {
            $('#surrendered-box').removeClass('d-none');
            $('#rescued-box').addClass('d-none');
            tinymce.get('rescued_note').setContent('');
        } else {
            $('#rescued-box').addClass('d-none');
            $('#surrendered-box').addClass('d-none');
            tinymce.get('surrendered_note').setContent('');
            tinymce.get('rescued_note').setContent('');
        }
    });

    // Trigger the change event on page load to handle preselected values
    $('#status').trigger('change');

    // $(document).on('click', '.view-btn', function () {
    //     $('#animalViewModal').modal('show');

    //     const data = {
    //         photo: $(this).data('photo'),
    //         video: $(this).data('video'),
    //         name: $(this).data('name'),
    //         species: $(this).data('species'),
    //         breed: $(this).data('breed'),
    //         age: $(this).data('age'),
    //         gender: $(this).data('gender'),
    //         color: $(this).data('color'),
    //         size: $(this).data('size'),
    //         characteristics: $(this).data('characteristics'),
    //         medicalhistory: $(this).data('medicalhistory'),
    //         sheltername: $(this).data('sheltername'),
    //         shelteraddress: $(this).data('shelteraddress'),
    //         regdate: $(this).data('regdate'),
    //         qrcodeurl: $(this).data('qrcodeurl')
    //     };

    //     // Prepare the items array for the loop
    //     const items = [
    //         { label: 'Image', value: data.photo ? `<img src="${data.photo}" alt="Image" class="img-thumbnail" width="100">` : 'N/A', class: 'w-25' },
    //         { label: 'Name', value: data.name, class: 'w-25' },
    //         { label: 'Species', value: data.species, class: 'w-25' },
    //         { label: 'Breed', value: data.breed, class: 'w-25' },
    //         { label: 'Age', value: data.age, class: 'w-25' },
    //         { label: 'Gender', value: data.gender, class: 'w-25' },
    //         { label: 'Color', value: data.color, class: 'w-25' },
    //         { label: 'Size', value: data.size, class: 'w-25' },
    //         { label: 'Characteristics', value: data.characteristics, class: 'w-25' },
    //         { label: 'Medical History', value: data.medicalhistory, class: 'w-25' },
    //         { label: 'Shelter Name', value: data.sheltername, class: 'w-25' },
    //         { label: 'Shelter Address', value: data.shelteraddress, class: 'w-25' },
    //         { label: 'Reg. Date', value: data.regdate, class: 'w-25' },
    //         {
    //             label: 'QR Code',
    //             value: data.qrcodeurl ? `<canvas id="qrcodeCanvas" width="250" height="250" style="margin-left:-20px;"></canvas>` : 'N/A',
    //             class: 'w-25'
    //         },
    //         { label: 'Video', value: data.video ? `<video src="${data.video}"  autoplay loop playsinline muted></video>` : 'N/A', class: 'w-25' }

    //     ];

    //     // Dynamically update the modal content with the items array
    //     const list = $('#animal-details');
    //     list.empty();

    //     items.forEach(item => {
    //         list.append(`
    //             <div class="d-flex ${item.class} w-100">
    //                 <div class="fw-bold text-uppercase p-2" style="width:20%;border-bottom:1px solid rgba(128, 128, 128, 0.3);"><span style="font-size:10px;">${item.label}:</span></div>
    //                 <div class="p-2" style="width:80%;border-bottom:1px solid rgba(128, 128, 128, 0.3);"><span  style="font-size:11px;">${item.value}<span></div>
    //             </div>
    //         `);
    //     });

    //     // Generate QR code and draw on the canvas
    //     if (data.qrcodeurl) {
    //         QRCode.toCanvas(document.getElementById('qrcodeCanvas'), data.qrcodeurl, function (error) {
    //             if (error) console.error(error);
    //             console.log('QR code generated!');
    //         });
    //     } else {
    //         // Clear the canvas if no QR code URL
    //         const qrcodeCanvas = document.getElementById('qrcodeCanvas');
    //         if (qrcodeCanvas) {
    //             qrcodeCanvas.getContext('2d').clearRect(0, 0, qrcodeCanvas.width, qrcodeCanvas.height);
    //         }
    //     }


    // });



    $(document).on('click', '.view-btn', function () {
        $('#animalViewModal').modal('show');

        const data = {
            photo: $(this).data('photo'),
            video: $(this).data('video'),
            name: $(this).data('name'),
            species: $(this).data('species'),
            breed: $(this).data('breed'),
            age: $(this).data('age'),
            gender: $(this).data('gender'),
            color: $(this).data('color'),
            size: $(this).data('size'),
            characteristics: $(this).data('characteristics'),
            medicalhistory: $(this).data('medicalhistory'),
            petstory: $(this).data('petstory'),
            sheltername: $(this).data('sheltername'),
            shelteraddress: $(this).data('shelteraddress'),
            regdate: $(this).data('regdate'),
            qrcodeurl: $(this).data('qrcodeurl')
        };

        // Prepare the items array for the loop
        const photos = data.photo ? data.photo.split(',') : [];
        const photoHtml = photos.length > 0
            ? photos.map(photo => `<img src="${photo.trim()}" alt="Image" class="img-thumbnail m-1" width="100">`).join(' ')
            : 'N/A';

        const items = [
            { label: 'Images', value: photoHtml, class: 'w-100' },
            { label: 'Name', value: data.name, class: 'w-50' },
            { label: 'Species', value: data.species, class: 'w-50' },
            { label: 'Breed', value: data.breed, class: 'w-50' },
            { label: 'Age', value: data.age, class: 'w-50' },
            { label: 'Gender', value: data.gender, class: 'w-50' },
            { label: 'Color', value: data.color, class: 'w-50' },
            { label: 'Size', value: data.size, class: 'w-50' },
            { label: 'Characteristics', value: data.characteristics, class: 'w-50' },
            { label: 'Medical History', value: data.medicalhistory, class: 'w-50' },
            { label: 'Pet Story', value: data.petstory, class: 'w-50' },
            { label: 'Shelter Name', value: data.sheltername, class: 'w-50' },
            { label: 'Shelter Address', value: data.shelteraddress, class: 'w-50' },
            { label: 'Reg. Date', value: data.regdate, class: 'w-50' },
            {
                label: 'QR Code',
                value: data.qrcodeurl ? `<canvas id="qrcodeCanvas" width="250" height="250" style="margin-left:-20px;"></canvas>` : 'N/A',
                class: 'w-50'
            },
            { label: 'Video', value: data.video ? `<video src="${data.video}" autoplay loop playsinline muted class="w-100"></video>` : 'N/A', class: 'w-100' }
        ];

        // Dynamically update the modal content with the items array
        const list = $('#animal-details');
        list.empty();

        items.forEach(item => {
            list.append(`
                <div class="d-flex ${item.class} w-100">
                    <div class="fw-bold text-uppercase p-2" style="width:20%;border-bottom:1px solid rgba(128, 128, 128, 0.3);"><span style="font-size:10px;">${item.label}:</span></div>
                    <div class="p-2" style="width:80%;border-bottom:1px solid rgba(128, 128, 128, 0.3);"><span style="font-size:11px;">${item.value}<span></div>
                </div>
            `);
        });

        // Generate QR code and draw on the canvas
        if (data.qrcodeurl) {
            QRCode.toCanvas(document.getElementById('qrcodeCanvas'), data.qrcodeurl, function (error) {
                if (error) console.error(error);
                console.log('QR code generated!');
            });
        } else {
            // Clear the canvas if no QR code URL
            const qrcodeCanvas = document.getElementById('qrcodeCanvas');
            if (qrcodeCanvas) {
                qrcodeCanvas.getContext('2d').clearRect(0, 0, qrcodeCanvas.width, qrcodeCanvas.height);
            }
        }
    });



    // Handle form submission
    $(document).on('click', '#saveAnimal', function () {
        let form = $('#animal-form')[0];
        let formData = new FormData(form);

        const rescuedNote = tinymce.get('rescued_note').getContent();
        formData.append('rescued_note', rescuedNote);

        const surrenderedNote = tinymce.get('surrendered_note').getContent();
        formData.append('surrendered_note', surrenderedNote);

        const descriptionContent = tinymce.get('description').getContent();
        formData.append('description', descriptionContent);

        const petStoryDescContent = tinymce.get('ps_description').getContent();
        formData.append('pet_story', petStoryDescContent);

        showLoader('.saveAnimal');

        $('#saveAnimal').prop('disabled', true)

        $.ajax({
            url: $(form).attr('action'),
            method: $(form).attr('method'),
            data: formData,
            contentType: false,
            processData: false,
            success: function (data) {

                hideLoader('.saveAnimal');
                $('#saveAnimal').prop('disabled', false)
                toast(data.type, data.message)
                $('#animalModal').modal('hide');
                animalDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveAnimal');
                    $('#saveAnimal').prop('disabled', false)

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


    // $('#generate-pdf-btn').on('click', function () {
    //     let startDate = $('#start-date').val();
    //     let endDate = $('#end-date').val();

    //     // Fetch filtered data
    //     $.ajax({
    //         url: `/pet/create?data=${petdata}&start=${startDate}&end=${endDate}`,
    //         method: 'GET',
    //         success: function (response) {
    //             generatePDF(response.data, startDate, endDate);
    //         },
    //         error: function (xhr, status, error) {
    //             console.error('Error fetching data:', error);
    //         }
    //     });
    // });

    $('#generate-pdf-btn').on('click', function () {
        let startDate = $('#start-date').val();
        let endDate = $('#end-date').val();

        // Generate PDF by calling the server-side route
        window.location.href = `/pets-report/pdf?start=${startDate}&end=${endDate}&pettype=${petdata}`;
    });

    // function generatePDF(data, startDate, endDate) {

    //     console.log('selected data', data)
    //     const { jsPDF } = window.jspdf;
    //     const doc = new jsPDF();

    //     doc.text(`Pets Report from ${startDate} to ${endDate}`, 10, 10);

    //     // Adding table header
    //     doc.text('Species', 10, 20);
    //     doc.text('Breed', 50, 20);
    //     doc.text('Age', 90, 20);
    //     doc.text('Gender', 130, 20);
    //     doc.text('Color', 170, 20);
    //     doc.text('Status', 210, 20);

    //     let y = 30;
    //     data.forEach((pet) => {
    //         doc.text(pet.species, 10, y);
    //         doc.text(pet.breed, 50, y);
    //         doc.text(`${pet.age} years`, 90, y);
    //         doc.text(pet.gender, 130, y);
    //         doc.text(pet.color, 170, y);
    //         doc.text(pet.status, 210, y);
    //         y += 10;
    //     });

    //     doc.save(`pets_report_${startDate}_to_${endDate}.pdf`);
    // }

    function showAnimalModal(modalTitle) {
        $('#animalModal').modal('show');
        $('#animalModal .modal-title').text(modalTitle);
    }

    function removeAnimal(id) {

        $.ajax({
            type: 'DELETE',
            url: `/pet/${id}`,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (data) {
            toast(data.type, data.message)
            $('#animalModal').modal('hide');
            animalDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });

    }

});
