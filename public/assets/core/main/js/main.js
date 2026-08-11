function util_price(price) {
  return (price / 100).toFixed(2);
}
function create_toast(type, title, message) {
  // bootstrap toast
  let toast_html =
    '<div class="toast slide-down toast-show bg-' +
    type +
    ' text-white fw-500 text-center fade show top-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="toast-body">' +
    message +
    "</div></div>";
  $(".toast-container").append(toast_html);
  setTimeout(function () {
    $(".toast-container .toast:last-child").removeClass("slide-down");
  }, 400);

  // remove toast after 5 seconds with opacity transition
  setTimeout(function () {
    $(".toast-container .toast:last-child").addClass("slide-up-negative");
    setTimeout(function () {
      $(".toast-container .toast:last-child").remove();
    }, 300);
  }, 5000);
}

function ajax_response_handler(response, form) {
  if (response.validationErrors) {
    let error_text = "";
    Object.keys(response.validationErrors).forEach((key) => {
      response.validationErrors[key] = response.validationErrors[key].replace(
        "The ",
        ""
      );
      error_text += response.validationErrors[key] + " <br>";
    });
    form.find(".form-error").html(error_text);
    form.find(".form-error").show();
  }
  if (response.reFocus) {
    $ajaxForm.find("input:visible:first").focus();
  }
  if (response.resetForm) {
    $ajaxForm[0].reset();
  }

  if (response.hideFooter) {
    footer_pop_toggle("hide");
  }

  if (response.sendToast) {
    create_toast(
      response.sendToast.type,
      response.sendToast.title,
      response.sendToast.message
    );
  }

  if (response.playSound) {
    var audio = new Audio(
      asset_url + "/core/dash/audio/" + response.playSound + ".mp3"
    );
    audio.play();
  }

  if (response.closeModal) {
    $ajaxForm.parents(".modal").hide();
    $(".modal-backdrop").remove();
    $("body").removeClass("modal-open");
    $(".modal").each(function () {
      $(this).hide();
    });
  }

  if (response.redirectUrl) {
    window.location.href = response.redirectUrl;
  }

  if (response.refreshPage) {
    setTimeout(function () {
      location.reload();
    }, 1500);
  }
}

$(".ajax-form").submit(function () {
  $ajaxForm = $(this);

  var formData = new FormData($ajaxForm[0]);
  $.ajax({
    type: "post",
    url: $(this).attr("action"),
    data: formData,
    dataType: "text",
    cache: false,
    processData: false,
    contentType: false,
    beforeSend: function () {
      $ajaxForm.find('button[type="submit"]').attr("data-indicator", "on");
      $ajaxForm.find("*:not(.disabled)").prop("disabled", true);
    },
    error: function () {
      $ajaxForm.find('button[type="submit"]').removeAttr("data-indicator");
      $ajaxForm.find("*:not(.disabled)").prop("disabled", false);

      create_toast(
        "danger",
        "Error",
        "Something went wrong. Please try again."
      );
    },
    success: function (response) {
      $ajaxForm.find('button[type="submit"]').removeAttr("data-indicator");
      $ajaxForm.find("*:not(.disabled)").prop("disabled", false);
      $ajaxForm.find(".form-error").html("");
      $ajaxForm.find(".form-error").hide();
      response = JSON.parse(response);
      ajax_response_handler(response, $ajaxForm);
    },
  });

  return false;
});

function fetch_api(action, post_data = {}) {
  post_data["action"] = action;
  let request_data = new FormData();
  for (var key in post_data) {
    request_data.append(key, post_data[key]);
  }
  return $.ajax({
    url: ajax_url,
    data: request_data,
    dataType: "text",
    async: true,
    method: "POST",
    cache: true,
    processData: false,
    contentType: false,
    error: function () {},
    success: function (response) {},
  });
}

$("#lol-trigger").on("mouseover", function () {
  $("#lol-trigger").addClass("text-primary");
  $("#lol-services")
    .removeClass("d-none")
    .addClass("fade-in")
    .one("animationend", function () {
      $(this).removeClass("fade-in");
    });
  $("#val-trigger").removeClass("text-primary");
  $("#val-services").addClass("d-none");
});

$("#val-trigger").on("mouseover", function () {
  $("#val-trigger").addClass("text-primary");
  $("#val-services")
    .removeClass("d-none")
    .addClass("fade-in")
    .one("animationend", function () {
      $(this).removeClass("fade-in");
    });
  $("#lol-trigger").removeClass("text-primary");
  $("#lol-services").addClass("d-none");
});

