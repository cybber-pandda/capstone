function loadCompanyData() {
    $.ajax({
        url: '/company/settings',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            const companySetting = response.companySetting;

            const tableHtml = `
                <tr>
                    <td><img src="${companySetting.logo_url}" width="60"></td>
                    <td>${companySetting.company_email || '-'}</td>
                    <td>${companySetting.company_phone || '-'}</td>
                    <td>${companySetting.company_address || '-'}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info" id="editCompanyBtn">
                            <i class="link-icon" data-lucide="edit-3"></i>
                        </button>
                    </td>
                </tr>
            `;

            // Insert the table HTML
            $('#companyTable tbody').html(tableHtml);

            // Initialize Lucide icons AFTER DOM update
            if (typeof lucide !== "undefined") {
                lucide.createIcons();
            }

            // Bind click event for the edit button
            $('#editCompanyBtn').on('click', function () {
                $('#editCompanyModal').modal('show');
                $('#company-logo-img').attr('src', '/' + (companySetting.company_logo || 'assets/dashboard/images/noimage.png'));
                $('#company_email').val(companySetting.company_email || '');
                $('#company_phone').val(companySetting.company_phone || '');
                $('#company_address').val(companySetting.company_address || '');
            });
        },
        error: function () {
            toast('error', 'Failed to load company data');
        }
    });
}

$(document).ready(function () {
    loadCompanyData();

    $('#companyForm').on('submit', function (e) {
        e.preventDefault();
        $('.invalid-feedback').html('');
        $('.form-control').removeClass('is-invalid');

        var formData = new FormData(this);

        $.ajax({
            url: '/company/update',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    companySettingJson = JSON.stringify(response.companySetting);
                    loadCompanyData();
                    $('#editCompanyModal').modal('hide');
                    toast('success', response.success);
                }
            },
            error: function (response) {
                if (response.status === 422) {
                    var errors = response.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $('#' + key).addClass('is-invalid');
                        $('#' + key + '_error').html(value[0]);
                    });
                } else {
                    toast('error', 'Something went wrong!');
                }
            }
        });
    });
});
