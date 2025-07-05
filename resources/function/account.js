
function loadAccountData() {
    // Parse the JSON data into a JavaScript object
    const accountSetting = JSON.parse(accountSettingJson);
    


    if (accountSetting) {

        const avatarLogo = accountSetting.profile;
    
        $('#account_username').val(accountSetting.username);
        $('#account_email').val(accountSetting.email);
      
        if (avatarLogo) {
            $('#account-profile-img').attr('src', avatarLogo);
            $('#auth_user_profile').attr('src', avatarLogo);
        } else {
            $('#account-profile-img').attr('src', 'assets/back/images/avatar/noprofile.webp');
        }

    }
}

$(document).ready(function () {

    $('#profile').on('submit', function (e) {
        e.preventDefault();

        showLoader('.profile');

        // Create FormData object
        var formData = new FormData(this);

        $.ajax({
            url: '/generalsettings-profile',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                // Handle success
                if (response.success) {

                    hideLoader('.profile');

                    $('.form-control').val('');

                    toast('success', response.success);

                    // Update companySettingJson with new data from the response
                    accountSettingJson = JSON.stringify(response.accountSetting);
                    loadAccountData();
                }
            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.profile');

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

    $('#btnAccountUpdate').on('click', function (e) {
        e.preventDefault();
        $('#mySecAuthModal').modal('show');
    });

    $('#saveMySecurityAuth').on('click', function (e) {
        e.preventDefault();
        
        const sq = $('#my_security_question').val();
        const sa = $('#my_security_answer').val(); 

        processAccount(sq, sa)

    });

    function processAccount(sq, sa) {
        showLoader('.account');
    
        var formData = new FormData($('#account')[0]);
    
        formData.append('security_question', sq);
        formData.append('security_answer', sa);
    
        $.ajax({
            url: '/generalsettings-account',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    hideLoader('.account');
                    $('.form-control').val('');
                    toast('success', response.success);
    
                    // Update local settings with new account data
                    accountSettingJson = JSON.stringify(response.accountSetting);
                    loadAccountData();
    
                    // Optionally close the modal
                    $('#mySecAuthModal').modal('hide');
                }
            },
            error: function (response) {
                hideLoader('.account');
    
                if (response.status === 422) {
                    var errors = response.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $('#' + key).addClass('border-danger is-invalid');
                        $('#' + key + '_error').html('<strong>' + value[0] + '</strong>');
                    });
                } else if (response.status === 400) {
                    toast('error', response.responseJSON.message);
                    $('#sec-auth-validation-form')[0].reset();
                    $('#mySecAuthModal').modal('hide');
                } else {
                    toast('error', 'An error occurred. Please try again.');
                    console.log(response);
                }
            }
        });
    }

    
    // $('#account').on('submit', function (e) {
    //     e.preventDefault();

    // });

    $('#account_password').on('submit', function (e) {
        e.preventDefault();

        showLoader('.password');

        var formData = new FormData(this);

        $.ajax({
            url: '/generalsettings-password',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                // Handle success
                if (response.success) {

                    hideLoader('.password');

                    $('.form-control').val('');

                    toast('success', response.success);

                    // Update companySettingJson with new data from the response
                    accountSettingJson = JSON.stringify(response.accountSetting);
                    loadAccountData();
                }
            },
            error: function (response) {
                if (response.status === 422) {

                    hideLoader('.password');

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

    loadAccountData();

});