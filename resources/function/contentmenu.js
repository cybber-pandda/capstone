
$(document).ready(function () {

    let tableMenuId = 'dynamic-menu-table';
    let urlMenuId = '/contentsettings-menu-api';
    let tableRestoreId = 'restore-menu-table';
    let urlRestoreId = '/contentsettings-menu-restore-api';
    let headers = ['Name', 'URL', 'Banner Title', 'Banner Sub Title', 'Banner Photo', 'Action'];
    let menuId;
    let url = urlMenuId;
    let tableId = tableMenuId;

    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    var menuDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: url,
            dataSrc: 'menudata'
        },
        columns: [{
            data: 'name',
            class: 'px-5',
            render: function (data, type, row) {
                return data === 'Home' ? "<p>" + data + "</p><i><small>Default pages cannot be modified or deleted.</small><i>" : data;
            }
        },
        {
            data: 'url',
            class: 'px-5',
            render: function (data, type, row) {
                return row.name === 'Home' ? "<i><small>Default</i></small>" : data;
            }
        },
        {
            data: 'bannertitle',
            width: '200px',
            class: 'px-5',
            render: function (data, type, row) {
                return data == null ? '--' : data;
            }
        },
        {
            data: 'bannersubtitle',
            width: '200px',
            class: 'px-5',
            render: function (data, type, row) {
                return data == null ? '--' : data;
            }
        },
        {
            data: 'bannerphoto',
            render: function (data, type, row) {
                if (data) {
                    return `
                            <picture>
                                <source srcset="${data}" type="image/webp">
                                <img src="${data}" alt="${row.banner_title}" style="width:100px;">
                            </picture>
                            `;
                } else {
                    return '';
                }
            }
        },
        {
            data: 'actions',
            width: '100px',
            class: 'px-5',
            render: function (data, type, row) {
                // Show only the edit button if the menu item is "Home"
                if (row.name === 'Home') {
                    // Extract the edit button from the "actions" HTML
                    let editButton = $(data).filter('.edit-btn-menu').prop('outerHTML');
                    return editButton; // Return only the edit button
                } else {
                    return data; // Return both edit and delete buttons for other items
                }
            }
        }
        ],
        autoWidth: false,
        responsive: {
            breakpoints: [{
                name: 'desktop',
                width: Infinity
            },
            {
                name: 'tablet',
                width: 1024
            },
            {
                name: 'phone',
                width: 768
            }
            ]
        },
        paging: true,
        searching: true,
        ordering: false,
        info: true,
        pageLength: 10,
        dom: '<lf<t>ip>',
        language: {
            search: 'Filter Menu',
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

    $('#frontmenu_name').on('input', function () {
        let name = $(this).val();
        let url = name.toLowerCase().replace(/\s+/g, '-');
        $('#frontmenu_url').val('/' + url);
    });

    // Show modal for adding new item
    $('#add-btn-menu').on('click', function () {
        $('#frontmenu-form')[0].reset();
        $('.hide_formgroup_menu').show();
        $('.showNote').addClass('d-none')
        showMenuModal($(this).data('modaltitle'));
    });

    // Show modal for editing item
    $(document).on('click', '.edit-btn-menu', function () {
        menuId = $(this).data('id');
        var name = $(this).data('name');

        $('#frontmenu_name').val(name);
        $('#frontmenu_url').val($(this).data('url'));
        $('#banner_title').val($(this).data('bannertitle'));
        $('#banner_sub_title').val($(this).data('bannersubtitle'));

        if (name === 'Home') {
            $('.hide_formgroup_menu').hide();
        } else {
            $('.hide_formgroup_menu').show();
        }

        $('.showNote').removeClass('d-none')
        showMenuModal($(this).data('modaltitle'));
    });

    $(document).on('click', '.delete-btn-menu', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This content section of the menu can be removed. Restore it if needed.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then((result) => {
            if (result.isConfirmed) {
                removeMenu(id)
            }
        })

    });

    $(document).on('click', '#restore-btn-menu', function () {
        $('#frontMenuRestoreModal').modal('show');

        // Set the URL and table ID for the restore menu
        url = urlRestoreId;
        tableId = tableRestoreId;

        // Attach the event handlers only once
        if (!$('#frontMenuRestoreModal').data('events-attached')) {
            // When the modal is shown
            $('#frontMenuRestoreModal').on('shown.bs.modal', function () {
                // Clear previous headers to avoid duplicates
                $(`#${tableId}-headers`).empty();

                // Append headers
                headers.forEach(header => {
                    $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
                });

                // Destroy existing DataTable instance to avoid reinitialization issues
                if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
                    $(`#${tableId}`).DataTable().clear().destroy();
                }

                // Initialize DataTable
                $(`#${tableId}`).DataTable({
                    ajax: {
                        url: url,
                        dataSrc: 'menudata'
                    },
                    columns: [{
                        data: 'name',
                        class: 'px-5'
                    },
                    {
                        data: 'url',
                        class: 'px-5',
                        render: function (data, type, row) {
                            return row.name === 'Home' ? "<i><small>Default</small></i>" : data;
                        }
                    },
                    {
                        data: 'bannertitle',
                        class: 'px-5'
                    },
                    {
                        data: 'bannerphoto',
                        render: function (data, type, row) {
                            if (data) {
                                return `
                                        <picture>
                                            <source srcset="${data}" type="image/webp">
                                            <img src="${data}" alt="${row.banner_title}" style="width:100px;">
                                        </picture>
                                        `;
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        data: 'actions',
                        class: 'px-5',
                        render: function (data, type, row) {
                            return row.name === 'Home' ? "<i><small>Default pages cannot be modified or deleted.</small></i>" : data;
                        }
                    }],
                    destroy: true,  // Allow reinitialization of the DataTable
                    autoWidth: false,
                    responsive: true,
                    paging: true,
                    searching: true,
                    ordering: false,
                    info: true,
                    pageLength: 10,
                    dom: '<lf<t>ip>',
                    language: {
                        search: 'Filter Menu',
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
            });

            // When the modal is hidden
            $('#frontMenuRestoreModal').on('hidden.bs.modal', function () {
                // Destroy the DataTable instance
                if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
                    $(`#${tableId}`).DataTable().clear().destroy();
                }
            });

            // Mark that events have been attached
            $('#frontMenuRestoreModal').data('events-attached', true);
        }
    });

    $(document).on('click', '.restore-btn-menu', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This content section of the menu will be restored.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then((result) => {
            if (result.isConfirmed) {
                restoreMenu(id)
            }
        })

    });

    $('#saveMenu').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveMenu');

        let form = $('#frontmenu-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        if (menuId) {
            formData.append('menu_id', menuId);
        }

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveMenu');
                clearInput()
                toast('success', response.success);
                $('#frontMenuModal').modal('hide');
                menuDataTable.ajax.reload();
                sectionDataTable.ajax.reload();
                //location.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveMenu');

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

    function showMenuModal(modalTitle) {
        $('#frontMenuModal').modal('show');
        $('#frontMenuModal .modal-title').text(modalTitle);
        // $('#saveMenu').text(modalTitle === 'Add' ? 'Save' : 'Update');
    }

    function removeMenu(id) {
        $.ajax({
            type: 'POST',
            url: '/contentsettings-remove-frontmenu',
            data: {
                menu_id: id
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (data) {
            toast(data.type, data.message)
            $('#frontMenuModal').modal('hide');
            menuDataTable.ajax.reload();
            sectionDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }

    function restoreMenu(id) {
        $.ajax({
            type: 'POST',
            url: '/contentsettings-restore-frontmenu',
            data: {
                menu_id: id
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (data) {
            toast(data.type, data.message)
            $('#frontMenuRestoreModal').modal('hide');
            menuDataTable.ajax.reload();
            sectionDataTable.ajax.reload();
            // location.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }

    ///// Content Section /////

    let tableSectionId = 'dynamic-section-table';
    let sectionHeaders = ['Menu', 'Layout', 'Type', 'isImage', 'Title', 'Content', 'Action'];
    let sectionId;

    sectionHeaders.forEach(header => {
        $(`#${tableSectionId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    var sectionDataTable = $(`#${tableSectionId}`).DataTable({
        ajax: {
            url: '/contentsettings-section-api',
            dataSrc: 'sectiondata'
        },
        columns: [{
            data: 'menu',
            class: 'px-5'
        },
        {
            data: 'layout',
        },
        {
            data: 'object_type',
        },
        {
            data: 'image',
            render: function (data, type, row) {
                if (data) {
                    return `
                            <picture>
                                <source srcset="${data}" type="image/webp">
                                <img src="${data}" alt="${row.menu}" style="width:100px;">
                            </picture>
                            `;
                } else {
                    return '';
                }
            }
        },
        // {
        //     data: 'icon',
        //     render: function (data, type, row) {
        //         if (data) {
        //             return `
        //                     <picture>
        //                         <source srcset="${data}" type="image/webp">
        //                         <img src="${data}" alt="${row.menu}" width="50">
        //                     </picture>
        //                     `;
        //         } else {
        //             return '';
        //         }
        //     }
        // },
        {
            data: 'title',
        },
        {
            data: 'content',
            render: function (data, type, row) {
                // Strip HTML tags
                var plainText = data.replace(/<\/?[^>]+(>|$)/g, "");

                // Split the plain text into words
                var words = plainText.split(/\s+/);

                // Truncate to the first 3 words
                var truncated = words.slice(0, 3).join(' ');

                // Add ellipsis if there are more than 3 words
                if (words.length > 3) {
                    truncated += '...';
                }

                return truncated;
            }
        },
        // {
        //     data: 'object_position',
        //     width: "10",
        //     render: function (data, type, row) {
        //         return row.icon != null ? 'Fixed top position' : data; 
        //     }
        // },
        {
            data: 'actions',
            class: 'px-5',
            render: function (data, type, row) {
                return row.name === 'Home' ? "<i><small>Default pages cannot be modified or deleted.</small><i>" : data;
            }
        }
        ],
        autoWidth: false,
        responsive: {
            breakpoints: [{
                name: 'desktop',
                width: Infinity
            },
            {
                name: 'tablet',
                width: 1024
            },
            {
                name: 'phone',
                width: 768
            }
            ]
        },
        paging: true,
        searching: true,
        ordering: false,
        info: true,
        pageLength: 10,
        dom: '<lf<t>ip>',
        language: {
            search: 'Filter Content',
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

    $('#add-btn-section').on('click', function () {
        $('#frontsection-form')[0].reset();
        $('.hide_formgroup_menu').show()
        $('#hide_label').show()
        $('#menu').show()
        showModalSection($(this).data('modaltitle'));
    });

    $(document).on('click', '.view-btn-section', function () {
        $('#frontSectionViewModal').modal('show');

        const data = {
            title: $(this).data('title'),
            menu: $(this).data('menuname'),
            layout: $(this).data('layout'),
            objectType: $(this).data('objecttype'),
            image: $(this).data('image'),
            icon: $(this).data('icon'),
            content: $(this).data('content'),
            objectPosition: $(this).data('objectposition')
        };

        // Prepare the items array for the loop
        const items = [
            { label: 'Title', value: data.title, class: 'w-25' },
            { label: 'Menu', value: data.menu == null ? '' : data.menu, class: 'w-25' },
            { label: 'Layout', value: data.layout, class: 'w-25' },
            { label: 'Type', value: data.objectType, class: 'w-25' },
            { label: 'Image', value: data.image ? `<img src="${data.image}" alt="Image" class="img-thumbnail" width="100">` : 'N/A', class: 'w-25' },
            { label: 'Icon', value: data.icon ? `<img src="${data.icon}" alt="Icon" class="img-thumbnail" width="50">` : 'N/A', class: 'w-25' },
            { label: 'Content', value: data.content, class: 'w-25' },
            { label: 'Position', value: data.objectPosition, class: 'w-25' }
        ];

        // Dynamically update the modal content with the items array
        const list = $('#section-details');
        list.empty();

        items.forEach(item => {
            list.append(`
                <div class="d-flex ${item.class} w-100">
                    <div class="fw-bold text-uppercase p-2" style="width:20%;border-bottom:1px solid black;"><span style="font-size:10px;">${item.label}:</span></div>
                    <div class="p-2" style="width:80%;border-bottom:1px solid black"><span  style="font-size:11px;">${item.value}<span></div>
                </div>
            `);
        });
    });

    $(document).on('click', '.edit-btn-section', function () {

        sectionId = $(this).data('id');
        $('#menu').val($(this).data('menu')).change();
        $('.hide_formgroup_menu').hide()
        $('#layout').val($(this).data('layout')).change();
        $('#object').val($(this).data('objecttype')).change();
        $('#position').val($(this).data('objectposition')).change();
        $('#title').val($(this).data('title')).change();
        tinymce.get('description').setContent($(this).data('content'));

        showModalSection($(this).data('modaltitle'));
    });

    $(document).on('click', '.delete-btn-section', function () {
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
                removeSection(id)
            }
        })

    });

    $('#saveSection').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveSection');

        let form = $('#frontsection-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        const descriptionContent = tinymce.get('description').getContent();
        formData.append('description', descriptionContent);

        if (sectionId) {
            formData.append('section_id', sectionId);
        }

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveSection');
                clearInput()
                toast('success', response.success);
                $('#frontSectionModal').modal('hide');
                sectionDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveSection');

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

    $('#layout').on('change', function () {
        var selectedLayout = $(this).val();
        showLayout(selectedLayout);
    });

    $('#object').change(function () {
        var selectedValue = $(this).val();
        if (selectedValue === 'image') {
            $('#image-input').removeClass('d-none');
            $('#icon-input').addClass('d-none');
        } else if (selectedValue === 'icon') {
            $('#image-input').addClass('d-none');
            $('#icon-input').removeClass('d-none');
        } else {
            // If no valid option is selected, hide both inputs
            $('#image-input').addClass('d-none');
            $('#icon-input').addClass('d-none');
        }
    });

    $('#frontSectionModal').on('hidden.bs.modal', function () {
        //$('#layout-sample .row').addClass('d-none');
        $('#image-input').addClass('d-none');
        $('#icon-input').addClass('d-none');
    });

    function showModalSection(modalTitle) {
        $('#frontSectionModal').modal('show');
        $('#frontSectionModal .modal-title').text(modalTitle);
    }

    function removeSection(id) {
        $.ajax({
            type: 'POST',
            url: '/contentsettings-remove-section',
            data: {
                section_id: id
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (data) {
            toast(data.type, data.message)
            $('#frontSectionModal').modal('hide');
            sectionDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }

    // Function to show only the selected layout and hide others
    function showLayout(selectedLayout) {
        // Hide all layout samples
        $('#layout-sample .row').addClass('d-none'); // Hide all layout samples

        // Show the default layout (#showcol12) if no specific layout is selected
        if (!selectedLayout || selectedLayout === 'showcol12') {
            $('#showcol12').removeClass('d-none');
        } else {
            // Show the selected layout sample if it exists
            var layoutId = 'show' + selectedLayout.replace(/\s+/g, '');
            $('#' + layoutId).removeClass('d-none');
        }
    }

    // Initialize the default pre-selected layout visibility
    showLayout($('#layout').val());

    ///// Why Choose Us Section /////

    let tableWCUSId = 'dynamic-wcus-table';
    let wcusHeaders = ['Icon', 'Title', 'Content', 'Action'];
    let wcusId;

    wcusHeaders.forEach(header => {
        $(`#${tableWCUSId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    var wcuDataTable = $(`#${tableWCUSId}`).DataTable({
        ajax: {
            url: '/contentsettings-wcu-api',
            dataSrc: 'wcudata'
        },
        columns: [
            {
                data: 'icon',
                render: function (data, type, row) {
                    if (data) {
                        return `
                            <picture>
                                <source srcset="${data}" type="image/webp">
                                <img src="${data}" alt="${row.menu}" width="50">
                            </picture>
                            `;
                    } else {
                        return '';
                    }
                }
            },
            {
                data: 'title',
                class: 'px-5'
            },
            {
                data: 'content',
                class: 'px-5'
            },
            {
                data: 'actions',
                class: 'px-5',
            }
        ],
        autoWidth: false,
        responsive: {
            breakpoints: [{
                name: 'desktop',
                width: Infinity
            },
            {
                name: 'tablet',
                width: 1024
            },
            {
                name: 'phone',
                width: 768
            }
            ]
        },
        paging: true,
        searching: true,
        ordering: false,
        info: true,
        pageLength: 10,
        dom: '<lf<t>ip>',
        language: {
            search: 'Filter Content',
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

    $('#add-btn-wcu').on('click', function () {
        $('#wcu-form')[0].reset();

        $('.showNote').addClass('d-none')
        showModalWCU($(this).data('modaltitle'));
    });


    $(document).on('click', '.edit-btn-wcu', function () {

        wcusId = $(this).data('id');
        $('#wcu_title').val($(this).data('title'));
        tinymce.get('wcu_description').setContent($(this).data('content'));

        $('.showNote').removeClass('d-none')
        showModalWCU($(this).data('modaltitle'));
    });

    $(document).on('click', '.delete-btn-wcu', function () {
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
                removeWCU(id)
            }
        })

    });

    function showModalWCU(modalTitle) {
        $('#wcuSectionModal').modal('show');
        $('#wcuSectionModal .modal-title').text(modalTitle);
    }

    $('#saveWCU').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveWCU');

        let form = $('#wcu-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        const descriptionContent = tinymce.get('wcu_description').getContent();
        formData.append('wcu_description', descriptionContent);

        if (wcusId) {
            formData.append('wcu_id', wcusId);
        }

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveWCU');
                clearInput()
                toast('success', response.success);
                $('#wcuSectionModal').modal('hide');
                wcuDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveWCU');

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

    function removeWCU(id) {
        $.ajax({
            type: 'POST',
            url: '/contentsettings-remove-wcu',
            data: {
                wcu_id: id
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (data) {
            toast(data.type, data.message)
            $('#wcuSectionModal').modal('hide');
            wcuDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }

    ///// FAQ Section /////

    let tableFAQId = 'dynamic-faq-table';
    let faqHeaders = ['Question', 'Answer', 'Action'];
    let faqId;

    faqHeaders.forEach(header => {
        $(`#${tableFAQId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    var faqDataTable = $(`#${tableFAQId}`).DataTable({
        ajax: {
            url: '/contentsettings-faq-api',
            dataSrc: 'faqdata'
        },
        columns: [
            {
                data: 'question',
                class: 'px-5'
            },
            {
                data: 'answers',
                class: 'px-5'
            },
            {
                data: 'actions',
                class: 'px-5',
            }
        ],
        autoWidth: false,
        responsive: {
            breakpoints: [{
                name: 'desktop',
                width: Infinity
            },
            {
                name: 'tablet',
                width: 1024
            },
            {
                name: 'phone',
                width: 768
            }
            ]
        },
        paging: true,
        searching: true,
        ordering: false,
        info: true,
        pageLength: 10,
        dom: '<lf<t>ip>',
        language: {
            search: 'Filter Content',
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

    $('#add-btn-faq').on('click', function () {
        $('#faq-form')[0].reset();
        showModalFAQ($(this).data('modaltitle'));
    });

    $(document).on('click', '.edit-btn-faq', function () {

        faqId = $(this).data('id');
        $('#faq_question').val($(this).data('question'));
        tinymce.get('faq_answer').setContent($(this).data('answers'));

        showModalFAQ($(this).data('modaltitle'));
    });

    $(document).on('click', '.delete-btn-faq', function () {
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
                removeFAQ(id)
            }
        })

    });

    $('#saveFAQ').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveFAQ');

        let form = $('#faq-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        const answerContent = tinymce.get('faq_answer').getContent();
        formData.append('faq_answer', answerContent);

        if (faqId) {
            formData.append('faq_id', faqId);
        }

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveFAQ');
                clearInput()
                toast('success', response.success);
                $('#faqSectionModal').modal('hide');
                faqDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveFAQ');

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

    function showModalFAQ(modalTitle) {
        $('#faqSectionModal').modal('show');
        $('#faqSectionModal .modal-title').text(modalTitle);
    }

    function removeFAQ(id) {
        $.ajax({
            type: 'POST',
            url: '/contentsettings-remove-faq',
            data: {
                faq_id: id
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (data) {
            toast(data.type, data.message)
            $('#faqSectionModal').modal('hide');
            faqDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }


    ///// SocialMedia Section /////

    let tableSocialMediaId = 'dynamic-socialmedia-table';
    let socialmediaHeaders = ['Icon', 'URL', 'Action'];
    let socialmediaId;

    socialmediaHeaders.forEach(header => {
        $(`#${tableSocialMediaId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    var socialmediaDataTable = $(`#${tableSocialMediaId}`).DataTable({
        ajax: {
            url: '/contentsettings-socialmedia-api',
            dataSrc: 'socialmediadata'
        },
        columns: [
            {
                data: 'icon',
                class: 'px-5'
            },
            {
                data: 'url',
                class: 'px-5'
            },
            {
                data: 'actions',
                class: 'px-5',
            }
        ],
        autoWidth: false,
        responsive: {
            breakpoints: [{
                name: 'desktop',
                width: Infinity
            },
            {
                name: 'tablet',
                width: 1024
            },
            {
                name: 'phone',
                width: 768
            }
            ]
        },
        paging: true,
        searching: true,
        ordering: false,
        info: true,
        pageLength: 10,
        dom: '<lf<t>ip>',
        language: {
            search: 'Filter Social Media',
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



    $('#add-btn-socialmedia').on('click', function () {
        $('#socialmedia-form')[0].reset();
        showModalSocialMedia($(this).data('modaltitle'));
    });

    $(document).on('click', '.edit-btn-socialmedia', function () {

        socialmediaId = $(this).data('id');
        $('#socialmedia_icon').val($(this).data('icon'));
        $('#url').val($(this).data('url'));

        showModalSocialMedia($(this).data('modaltitle'));
    });

    $(document).on('click', '.delete-btn-socialmedia', function () {
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
                removeSocialMedia(id)
            }
        })

    });

    function showModalSocialMedia(modalTitle) {
        $('#socialmediaSectionModal').modal('show');
        $('#socialmediaSectionModal .modal-title').text(modalTitle);
    }

    function removeSocialMedia(id) {
        $.ajax({
            type: 'POST',
            url: '/contentsettings-remove-socialmedia',
            data: {
                socialmedia_id: id
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (data) {
            toast(data.type, data.message)
            $('#socialmediaSectionModal').modal('hide');
            socialmediaDataTable.ajax.reload();
        }).fail(function (data) {
            console.log(data)
        });
    }

    $('#saveSocialMedia').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveSocialMedia');

        let form = $('#socialmedia-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        if (socialmediaId) {
            formData.append('socialmedia_id', socialmediaId);
        }

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveSocialMedia');
                clearInput()
                toast('success', response.success);
                $('#socialmediaSectionModal').modal('hide');
                socialmediaDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveSocialMedia');

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

    ///// ContentMenu Section /////

    let tableContentMenuId = 'dynamic-terms-table';
    let termsHeaders = ['Content Type', 'Content', 'Action'];
    let termsId;

    termsHeaders.forEach(header => {
        $(`#${tableContentMenuId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    var termsDataTable = $(`#${tableContentMenuId}`).DataTable({
        ajax: {
            url: '/contentsettings-terms-api',
            dataSrc: 'termsdata'
        },
        columns: [
            {
                data: 'contenttype',
                class: 'px-5'
            },
            {
                data: 'content',
                class: 'px-5',
                render: function (data, type, row) {
                    var plainText = data.replace(/<\/?[^>]+(>|$)/g, "");
                    var words = plainText.split(/\s+/);
                    var truncated = words.slice(0, 7).join(' ');

                    if (words.length > 7) {
                        truncated += '... <a href="#" class="show-full-description" data-termsdescription="' + plainText + '">Read more</a>';
                    }

                    return truncated;
                }
            },
            {
                data: 'actions',
                class: 'px-5',
            }
        ],
        autoWidth: false,
        responsive: {
            breakpoints: [{
                name: 'desktop',
                width: Infinity
            },
            {
                name: 'tablet',
                width: 1024
            },
            {
                name: 'phone',
                width: 768
            }
            ]
        },
        paging: true,
        searching: false,
        ordering: false,
        info: true,
        pageLength: 10,
        dom: '<lf<t>ip>',
        language: {
            search: 'Filter Social Media',
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

    $(document).on('click', '.edit-btn-terms', function () {

        termsId = $(this).data('id');
        tinymce.get('terms_description').setContent($(this).data('content'));

        showModalTerms($(this).data('modaltitle'));
    });

    $(document).on('click', '.show-full-description', function (e) {
        e.preventDefault();
        var fullDescription = $(this).data('termsdescription');
        $('#descriptionModal .modal-body').text(fullDescription);
        $('#descriptionModal').modal('show');
    });

    function showModalTerms(modalTitle) {
        $('#termsSectionModal').modal('show');
        $('#termsSectionModal .modal-title').text(modalTitle);
    }

    $('#saveTerms').on('click', function (e) {
        e.preventDefault();

        showLoader('.saveTerms');

        let form = $('#terms-form')[0];
        let url = $(form).attr('action');
        let method = $(form).attr('method');

        let formData = new FormData(form);

        if (termsId) {
            formData.append('terms_id', termsId);
        }

        const descriptionContent = tinymce.get('terms_description').getContent();

        // Create a temporary DOM element to manipulate the HTML
        const tempElement = document.createElement('div');
        tempElement.innerHTML = descriptionContent;

        // Loop through all elements and remove the 'style' attribute
        const allElements = tempElement.querySelectorAll('*');
        allElements.forEach(function (el) {
            el.removeAttribute('style');
        });

        // Get the cleaned HTML content
        const cleanedDescription = tempElement.innerHTML;

        formData.append('terms_description', cleanedDescription);

        $.ajax({
            url: url,
            method: method,
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {

                hideLoader('.saveTerms');
                clearInput()
                toast('success', response.success);
                $('#termsSectionModal').modal('hide');
                termsDataTable.ajax.reload();

            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.saveTerms');

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



});
