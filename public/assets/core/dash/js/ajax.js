function ajax_response_handler(response) {
  if (response.validationErrors) {
    let error_text = '';
    Object.keys(response.validationErrors).forEach((key) => {
      response.validationErrors[key] = response.validationErrors[key].replace(
        'The ',
        ''
      );
      error_text += response.validationErrors[key] + ' <br>';
    });
    $('#form-error').html(error_text);
    $('#form-error').show();
  }
  if (response.reFocus) {
    $ajaxForm.find('input:visible:first').focus();
  }
  if (response.resetForm) {
    $ajaxForm[0].reset();
  }

  if (response.hideFooter) {
    footer_pop_toggle('hide');
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
      asset_url + '/core/dash/audio/' + response.playSound + '.mp3'
    );
    audio.play();
  }

  if (response.closeModal) {
    $ajaxForm.parents('.modal').hide();
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    $('.modal').each(function () {
      $(this).hide();
    });
  }

  if (response.redirectUrl) {
    setTimeout(function () {
      window.location.href = response.redirectUrl;
    }, 1500);
  }

  if (response.refreshPage) {
    setTimeout(function () {
      location.reload();
    }, 1500);
  }
}

$('.ajax-form').submit(function () {
  $ajaxForm = $(this);

  var formData = new FormData($ajaxForm[0]);
  $.ajax({
    type: 'post',
    url: $(this).attr('action'),
    data: formData,
    dataType: 'text',
    cache: false,
    processData: false,
    contentType: false,
    beforeSend: function () {
      $ajaxForm.find('button[type="submit"]').attr('data-indicator', 'on');
      $ajaxForm.find('*:not(.disabled)').prop('disabled', true);
    },
    error: function () {
      $ajaxForm.find('button[type="submit"]').removeAttr('data-indicator');
      $ajaxForm.find('*:not(.disabled)').prop('disabled', false);

      create_toast(
        'danger',
        'Error',
        'Something went wrong. Please try again.'
      );
    },
    success: function (response) {
      $ajaxForm.find('button[type="submit"]').removeAttr('data-indicator');
      $ajaxForm.find('*:not(.disabled)').prop('disabled', false);
      $('#form-error').html('');
      $('#form-error').hide();
      response = JSON.parse(response);
      ajax_response_handler(response);
    },
  });

  return false;
});

function get_action_data(action, id) {
  let action_data = new FormData();
  if (id != '') {
    action_data.append('id', id);
  }
  action_data.append('action', action);
  return action_data;
}

function post_action(action_data, action_button = false) {
  $.ajax({
    type: 'post',
    url: ajax_url,
    data: action_data,
    dataType: 'text',
    cache: false,
    processData: false,
    contentType: false,
    beforeSend: function () {
      if (action_button) {
        action_button.attr('data-indicator', 'on');
        action_button.prop('disabled', true);
      }
    },
    error: function () {
      if (action_button) {
        action_button.removeAttr('data-indicator');
        action_button.prop('disabled', false);
      }

      create_toast(
        'danger',
        'Error',
        'Something went wrong. Please try again.'
      );
    },
    success: function (response) {
      if (action_button) {
        action_button.removeAttr('data-indicator');
        action_button.prop('disabled', false);
      }
      response = JSON.parse(response);

      ajax_response_handler(response);
    },
  });
}

$('a[data-action], button[data-action]').on('click', function (e) {
  e.preventDefault();
  action_button = $(this);
  action = action_button.attr('data-action');
  let post_id = action_button.attr('data-id');
  if (typeof post_id !== 'undefined' && post_id !== false) {
    action_data = get_action_data(action, post_id);
    post_action(action_data, action_button);
  } else {
    action_data = get_action_data(action);
    post_action(action_data, action_button);
  }
});
