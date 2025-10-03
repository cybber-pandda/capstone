$(document).ready(function () {
  const otp_inputs = document.querySelectorAll(".otp__digit");
  const allowedKeys = "0123456789".split("");

  otp_inputs.forEach((input, index) => {
    input.addEventListener("keydown", function (event) {
      // Allow navigation keys
      if (["Backspace", "ArrowLeft", "ArrowRight", "Tab"].includes(event.key)) {
        return;
      }

      // Block non-numeric input
      if (!allowedKeys.includes(event.key)) {
        event.preventDefault();
        return;
      }

      // Replace value immediately
      input.value = "";
    });

    input.addEventListener("keyup", function (event) {
      if (allowedKeys.includes(event.key)) {
        // Move to next field if exists
        if (index < otp_inputs.length - 1) {
          otp_inputs[index + 1].focus();
        }
      } else if (event.key === "Backspace" && index > 0) {
        // Move back on delete
        otp_inputs[index - 1].focus();
      }
    });
  });

  // ---- TIMER ----
  function updateTimer() {
    var now = new Date().getTime();
    var timeLeft = expireTimestamp - now;
    var minutes = Math.floor((timeLeft % (1000 * 3600)) / (1000 * 60));
    var seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

    if (timeLeft <= 0) {
      $("#timer").text("Time's up!");
      $("#sendRequest").removeClass("d-none");
      $("#sendRequest button").prop("disabled", false);
      $("#verifyAccount").prop("disabled", true).css("cursor", "not-allowed");
      otp_inputs.forEach((input) => (input.value = "")); // clear input
      clearInterval(timerInterval);
      $("#code_error").addClass("d-none");
    } else {
      $("#timer").text(`${minutes} minutes ${seconds} seconds remaining`);
      $("#sendRequest").addClass("d-none");
      $("#sendRequest button").prop("disabled", true);
      $("#verifyAccount").prop("disabled", false).css("cursor", "pointer");
    }
  }

  function restartTimer() {
    expireTimestamp = new Date().getTime() + 10 * 60 * 1000; // 10 mins
    clearInterval(timerInterval);
    timerInterval = setInterval(updateTimer, 1000);
    updateTimer();
  }

  // Initial timer setup
  var expireTimestamp = new Date(otpExpireTime).getTime();
  var timerInterval = setInterval(updateTimer, 1000);
  updateTimer();

  // ---- RESEND REQUEST ----
  $("#sendRequest").submit(function (event) {
    event.preventDefault();
    $(this).find("button").addClass("d-none");
    $("#loading-container").removeClass("d-none");

    $.post($(this).attr("action"), $(this).serialize(), function () {
      setTimeout(function () {
        location.reload();
        restartTimer();
      }, 2000);
    }).always(function () {
      $("#loading-container").addClass("d-none");
      $("#sendRequest button").removeClass("d-none");
    });
  });

  // ---- VERIFY BUTTON ----
  $("#verifyAccount").on("click", function () {
    const otpValues = Array.from(otp_inputs).map((input) => input.value).join("");

    $.post({
      url: "/verify/code",
      data: { code: otpValues },
      dataType: "json",
      beforeSend: function (xhr) {
        xhr.setRequestHeader(
          "X-CSRF-TOKEN",
          $('meta[name="csrf-token"]').attr("content")
        );
      },
    })
      .done(function (data) {
        $("#verifyAccount").prop("disabled", true);
        $("#loading-container").removeClass("d-none");

        if (data.redirect) {
          window.location.href = data.redirect;
        }
      })
      .fail(function (data) {
        if (data.status === 422) {
          let errors = data.responseJSON.errors;
          let message = data.responseJSON.message;
          if (errors && errors.code) {
            $("#code_error").html("<strong>" + errors.code[0] + "</strong>");
          } else if (message) {
            $("#code_error").html("<strong>" + message + "</strong>");
          }
        } else {
          console.log(data);
        }
      });
  });
});
