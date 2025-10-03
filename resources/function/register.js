$(document).ready(function () {
    // Trigger submit on Enter
    $("#username, #email, #password, #password-confirm").on("keyup", function (e) {
        e.preventDefault();
        if (e.key === "Enter" || e.keyCode === 13) {
            registerAccount();
        }
    });

    // Trigger submit on button click
    $("#registerAccount").on("click", function () {
        registerAccount();
    });

    function registerAccount() {
        var formData = $("#registerForm").serialize();

        // 🔹 Sleek Swal loader
        Swal.fire({
            title: 'Creating account...',
            width: '350px',
            padding: '1rem',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
            customClass: {
                popup: 'swal-custom-popup',
                title: 'swal-custom-title'
            }
        });

        $.post({
            url: $("#registerForm").attr("action"),
            data: formData,
            dataType: "json",
            beforeSend: function (xhr) {
                xhr.setRequestHeader(
                    "X-CSRF-TOKEN",
                    $('meta[name="csrf-token"]').attr("content")
                );
            },
        })
        .done(function (data) {
            Swal.close();

            if (data.redirect) {
                // ✅ Success → redirect to verification.notice
                window.location.href = data.redirect;
            }

            $("#registerAccount").attr("disabled", "disabled");
        })
        .fail(function (data) {
            Swal.close(); // hide loader on error

            if (data.status === 422) {
                var errors = data.responseJSON.errors;
                $.each(errors, function (key, value) {
                    $("#" + key).addClass("border-danger is-invalid");
                    $("#" + key + "_error").html("<strong>" + value[0] + "</strong>");
                    $("#" + key + "_prepend").addClass("border-danger");
                    $("#" + key + "_prepend_left").addClass("border-danger-left");
                    $("#password").addClass("border-right-0");
                });

                // ❌ Minimal error alert (blue button)
                Swal.fire({
                    title: 'Please fix the highlighted fields.',
                    width: '350px',
                    padding: '1rem',
                    confirmButtonColor: '#6571ff',
                    customClass: {
                        popup: 'swal-custom-popup',
                        title: 'swal-custom-title',
                        confirmButton: 'swal-custom-button'
                    }
                });

            } else {
                // ❌ Fallback error (blue button)
                Swal.fire({
                    icon: 'error',
                    title: 'Something went wrong',
                    text: 'Please try again later.',
                    width: '350px',
                    padding: '1rem',
                    confirmButtonColor: '#6571ff',
                    customClass: {
                        popup: 'swal-custom-popup',
                        title: 'swal-custom-title',
                        confirmButton: 'swal-custom-button'
                    }
                });
                console.log("Error:", data);
            }
        });
    }
});
