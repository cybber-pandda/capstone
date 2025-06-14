$(document).ready(function () {

    $('#username, #email, #password, #password-confirm').on('keyup', function (e) {
        e.preventDefault();
        if (e.key === 'Enter' || e.keyCode === 13) {
            $('#loading-container').removeClass('d-none');
            registerAccount();
        }
    });

    $('#registerAccount').on('click', function () {

        if (!$('#agree').is(':checked')) {
            Swal.fire({
                text: 'You must agree to the Terms and Conditions before registering.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: false,
                customClass: {
                    popup: 'swal2-no-animation',
                    icon: 'swal2-icon-info-inline'
                },
            });
            return;
        }
        
        // $(this).attr('disabled', 'disabled');
        $('#loading-container').removeClass('d-none');
        registerAccount();
    });

    function registerAccount() {

        var formData = $('#registerForm').serialize();

        $.post({
            url: $('#registerForm').attr('action'),
            data: formData,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            }
        }).done(function (data) {
            
             if (data.redirect) {
                window.location.href = data.redirect;
            }
    
            $(this).find('#registerAccount').attr('disabled', 'disabled');
            $('#loading-container').removeClass('d-none');

          
        })
        
        .fail(function (data) {

            $('#loading-container').addClass('d-none');

            if (data.status === 422) {
                var errors = data.responseJSON.errors;
                $.each(errors, function (key, value) {
                    $('#' + key).addClass('border-danger is-invalid');
                    $('#' + key + '_error').html('<strong>' + value[0] + '</strong>');
                    $('#' + key + '_prepend').addClass('border-danger')
                });
            } else {
                console.log("Error:", data);
            }
        });
    }

});
