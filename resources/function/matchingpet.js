$(document).ready(function () {
    let tableId = 'dynamic-matchingpet-table';

    let headers = ['Photo', 'Breed', 'Color', 'Matching %', 'Preference', 'Action'];

    headers.forEach(header => {
        $(`#${tableId}-headers`).append(`<th class="px-5">${header}</th>`);
    });

    // Initialize DataTable
    var matchingPetDataTable = $(`#${tableId}`).DataTable({
        ajax: {
            url: '/adopter-matchingpet-api',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'petphoto',
                render: function (data, type, row) {
                    if (data) {
                        return `
                        <div class="d-flex align-items-center">
                            <img src="${data}" alt="${row.petname}" class="avatar avatar-lg rounded-circle">
                            <div class="ms-2">
                              <h5 class="mb-0"><a href="#!" class="text-inherit">${row.petname}</a></h5>
                            </div>
                        </div>`;
                    } else {
                        return `
                        <picture>
                            <source srcset="assets/back/images/brand/logo/noimage.jpg" type="image/webp">
                            <img src="assets/back/images/brand/logo/noimage.jpg" style="width:100px;">
                        </picture>`;
                    }
                }
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
                data: 'matchcount',
                render: function (data) {
                    const percent = Math.min(data, 100); // Cap the percentage at 100%
                    return `
                    <div class="progress" style="height: 20px; position: relative;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: ${percent}%;" aria-valuenow="${percent}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                        <span style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; color: black; font-weight: bold; display: flex; align-items: center; justify-content: center;">
                            ${percent}%
                        </span>
                    </div>`;
                },
                class: 'px-5'
            },
            {
                data: 'matchcount',
                render: function (data) {
                    if (data >= 75) {
                        return '<span class="badge bg-success">Like</span>';
                    } else if (data >= 50) {
                        return '<span class="badge bg-warning">Average</span>';
                    } else {
                        return '<span class="badge bg-danger">Dislike</span>';
                    }
                },
                class: 'px-5'
            },
            {
                data: 'actions',
                class: 'px-5',
                render: function (data, row) {
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
});
