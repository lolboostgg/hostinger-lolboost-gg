var form = $('#lol_boost_form');
var type = $('input[name="type"]').val();
var form_id = $('input[name="form_id"]').val();
var start_tier, start_division, end_tier, end_division, is_priority, is_bonus_win, start_lp, end_lp, lp_gain, server, discount_code, matches, is_duo, hours;

function duo_change() {
  if (is_duo == 1) {
    $('.solo-option').addClass('d-none');
    $('.duo-option').removeClass('d-none');

    $('.solo-option input').each(function (index) {
      $(this).prop('checked', false);
    });
  } else {
    $('.duo-option').addClass('d-none');

    $('.duo-option input').each(function (index) {
      $(this).prop('checked', false);
    });

    $('.solo-option').removeClass('d-none');
  }
}

function disable_duo() {
  if (start_tier >= 8 && end_tier >= 8) {
    $('input:radio[name="is_duo"]').prop('checked', false);
    $('input:radio[name="is_duo"]').parent().hide();
    is_duo = false;
  } else {
    $('input:radio[name="is_duo"]').parent().show();
  }
  duo_change();
}

function update_pricing() {
  form.submit();
}

// function to conver tiers from number to text and vice versa
function convert_tier(tier) {
  var tiers = {
    0: 'unranked',
    1: 'iron',
    2: 'bronze',
    3: 'silver',
    4: 'gold',
    5: 'platinum',
    6: 'emerald',
    7: 'diamond',
    8: 'master',
    9: 'grandmaster',
    10: 'challenger',
  };
  if (typeof tier === 'string') {
    for (var key in tiers) {
      if (tiers[key] === tier) {
        return key;
      }
    }
  } else if (typeof tier === 'number') {
    return tiers[tier];
  }
}

// function to conver division from number to text and vice versa
function convert_division(division) {
  var divisions = {
    4: 'I',
    3: 'II',
    2: 'III',
    1: 'IV',
  };
  if (typeof division === 'string') {
    for (var key in divisions) {
      if (divisions[key] === division) {
        return key;
      }
    }
  } else if (typeof division === 'number') {
    return divisions[division];
  }
}

// init champions_roles_modal modal

$('input[name="currency"]').val($('#local_currency').val());
// on button name=buy_now click event
$('#start_boost, #sticky_start_boost').click(function (e) {
  e.preventDefault();
  $('.boost-form').append('<input type="hidden" name="buy_now" value="1">');
  $('.boost-form').submit();
});

$('.boost-form').on('keyup keypress', function (e) {
  var keyCode = e.keyCode || e.which;
  if (keyCode === 13) {
    e.preventDefault();
    return false;
  }
});
$('.boost-form').submit(function () {
  $boost_form = $(this);
  var formData = new FormData($boost_form[0]);

  $.ajax({
    type: 'post',
    url: $(this).attr('action'),
    data: formData,
    dataType: 'text',
    cache: false,
    processData: false,
    contentType: false,
    beforeSend: function () {
      $boost_form.find('button[type="submit"]').attr('data-kt-indicator', 'on');
      $boost_form.find('button[type="submit"]').prop('disabled', true);
    },
    error: function () {
      $boost_form.find('button[type="submit"]').removeAttr('data-kt-indicator');
      $boost_form.find('button[type="submit"]').prop('disabled', false);
    },
    success: function (response) {
      $boost_form.find('button[type="submit"]').removeAttr('data-kt-indicator');
      $boost_form.find('button[type="submit"]').prop('disabled', false);

      response = JSON.parse(response);
      if (response.redirectUrl) {
        window.location.href = response.redirectUrl;
      }

      if (response.refreshPage) {
        location.reload();
      }

      if (response.updatePricing) {
        if (response.price == 0 || response.price < 0) {
          $boost_form.find('button[type="submit"] span.indicator-label').text('Invalid Desired Rank');
          $boost_form.find('button[type="submit"]').attr('disabled', true);
        } else {
          $boost_form.find('button[type="submit"] span.indicator-label').text('Buy Boost Now');
          $boost_form.find('button[type="submit"]').attr('disabled', false);
        }
        $('#total-price').text(response.currency + util_price(response.price));
      }

      if (response.discount_status) {
        $('#discount_alert').text(response.discount_msg);
        $('#discount_alert').removeClass('text-danger');
        $('#discount_alert').addClass('text-success');
      } else if (response.discount_msg != null) {
        $('#discount_alert').text(response.discount_msg);
        $('#discount_alert').removeClass('text-success');
        $('#discount_alert').addClass('text-danger');
      } else {
        $('#discount_alert').text('');
      }

      if (response.completion_time != null) {
        $('#completion-time').text(response.completion_time);
      }
    },
  });

  return false;
});

// declare variables for the form and the inputs

$('input:radio, input:checkbox, select').on('change', function () {
  load_variables();
});
let timeout;
$("input:text, :input[type='number']").on('keyup change', function () {
  clearTimeout(timeout);
  timeout = setTimeout(function () {
    load_variables();
  }, 500);
});
// on start_tier change

$('.noUi-handle').on('click', function () {
  $(this).width(50);
});
var number_format = wNumb({
  decimals: 0,
});
var matches_slider = document.getElementById('matches_slider');
if (matches_slider != null) {
  noUiSlider.create(matches_slider, {
    start: 3,
    step: 1,
    tooltips: [true],
    connect: 'lower',
    range: {
      min: [1],
      max: [5],
    },
    format: number_format,
  });
  matches_slider.noUiSlider.on('update', function (values, handle) {
    $('input[name="matches0"]').val(number_format.from(values[0]));
  });
  matches_slider.noUiSlider.on('change.one', function (values) {
    load_variables();
    $('.win-count').text(number_format.from(values[0]));
  });
}

var matches_slider = document.getElementById('matches_slider1');
if (matches_slider != null) {
  noUiSlider.create(matches_slider, {
    start: 2,
    step: 1,
    tooltips: [true],
    connect: 'lower',
    range: {
      min: [1],
      max: form_id == 9 ? [5] : [3],
    },
    format: number_format,
  });
  matches_slider.noUiSlider.on('update', function (values, handle) {
    $('input[name="matches0"]').val(number_format.from(values[0]));
  });
  matches_slider.noUiSlider.on('change.one', function (values) {
    load_variables();
    $('.win-count').text(number_format.from(values[0]));
  });
}

var hours_slider = document.getElementById('hours_slider');
if (hours_slider != null) {
  noUiSlider.create(hours_slider, {
    start: 5,
    step: 1,
    tooltips: [true],
    connect: 'lower',
    range: {
      min: [1],
      max: [10],
    },
    format: wNumb({
      decimals: 0,
      suffix: ' Hrs',
    }),
  });
  hours_slider.noUiSlider.on('update', function (values, handle) {
    $('input[name="hours"]').val(number_format.from(values[0]));
  });
  hours_slider.noUiSlider.on('change.one', function (values) {
    load_variables();
    $('.hour-count').text(number_format.from(values[0]));
  });
}

var boosters_slider = document.getElementById('boosters_slider');
if (boosters_slider != null) {
  noUiSlider.create(boosters_slider, {
    start: 1,
    step: 1,
    tooltips: [true],
    connect: 'lower',
    range: {
      min: [1],
      max: [5],
    },
    format: number_format,
  });
  boosters_slider.noUiSlider.on('update', function (values, handle) {
    $('input[name="boosters"]').val(number_format.from(values[0]));
  });
  boosters_slider.noUiSlider.on('change.one', function (values) {
    load_variables();
    $('.booster-count').text(number_format.from(values[0]));
    processOrderSummaryIcons();
  });
}

function load_variables() {
  start_tier = parseInt($('input:radio[name="start_tier"]:checked').val());
  start_division = parseInt($('input:radio[name="start_division"]:checked').val());
  end_tier = parseInt($('input:radio[name="end_tier"]:checked').val());
  end_division = parseInt($('input:radio[name="end_division"]:checked').val());
  matches = $('input[name="matches"]').val();
  hours = $('input[name="hours"]').val();
  is_priority = $('input:checkbox[name="is_priority"]:checked').val();
  is_bonus_win = $('input:checkbox[name="is_bonus_win"]:checked').val();

  if (form_id == 1 || form_id == 2) {
    if (start_tier != 8) {
      start_lp = $('select[name="start_lp"]').val();
    } else {
      start_lp = document.getElementById('start_lp_input').value;
    }
  } else {
    start_lp = $('select[name="start_lp"]').val();
  }

  if (form_id == 1) {
    if (end_tier == 8) {
      end_lp = document.getElementById('end_lp_input').value;
    }
  }
  is_duo = $('input:radio[name="is_duo"]:checked').val();
  lp_gain = $('select[name="lp_gain"]').val();
  server = $('select[name="server"]').val();
  discount_code = $('input[name="discount_code"]').val();
  lb_coins = $('input[name="lb_coins"]').val();

  duo_change();
  disable_duo();
  update_pricing();
}

$('input:radio[name="start_tier"], input:radio[name="start_division"]').on('change', function () {
  start_tier = parseInt($('input:radio[name="start_tier"]:checked').val());
  if (start_tier > 7 || start_tier == 0) {
    $('select[name="start_lp"]').parent().hide();
    $('#start_lp_full').show();
    $('#start_divisions').hide();
    if (form_id == 1) {
      $('#current_lp_dropdown').hide();
    }
    $('#start_divisions').addClass('d-none');
  } else {
    $('select[name="start_lp"]').parent().show();
    $('#start_lp_full').hide();
    $('#start_divisions').show();
    if (form_id == 1) {
      $('#current_lp_dropdown').show();
    }
    $('#start_divisions').removeClass('d-none');
  }
});

$('input:radio[name="end_tier"], input:radio[name="end_division"]').on('change', function () {
  end_tier = parseInt($('input:radio[name="end_tier"]:checked').val());
  if (end_tier > 7 || end_tier == 0) {
    $('select[name="end_lp"]').parent().parent().hide();
    $('#end_lp_full').show();
    $('#end_divisions').hide();
    $('#end_divisions').addClass('d-none');
  } else {
    $('select[name="end_lp"]').parent().parent().show();
    $('#end_lp_full').hide();
    $('#end_divisions').show();
    $('#end_divisions').removeClass('d-none');
  }
});
$(window).on('load', function () {
  load_variables();
  let champ_roles_md = new bootstrap.Modal('#champions_roles_modal', {
    backdrop: 'static',
  });

  $('#is_champions_roles').change(function () {
    if ($(this).is(':checked')) {
      champ_roles_md.show();
    } else {
      champ_roles_md.hide();
      // $('.champions_roles_modal input').each(function (index) {
      //     $(this).prop("checked", false);
      // });
    }
  });
});

// on window loaded event

// manual lp inputs
startLPInput = document.getElementById('start_lp_input');
endLPInput = document.getElementById('end_lp_input');

// event listeners

if (startLPInput != null) {
  startLPInput.addEventListener('input', function (event) {
    event.target.value = event.target.value.replace(/[^0-9]/g, '');

    if (event.target.value === '') {
      event.target.value = 0;
    } else {
      event.target.value = parseInt(event.target.value, 10);
    }

    setTimeout(checkLPDifference, 1000);
  });
}

if (endLPInput != null) {
  endLPInput.addEventListener('input', (event) => {
    event.target.value = event.target.value.replace(/[^0-9]/g, '');

    if (event.target.value === '') {
      event.target.value = 0;
    } else {
      let value = parseInt(event.target.value, 10);
      if (value > 2000) {
        event.target.value = 2000;
      }
    }

    setTimeout(checkLPDifference, 1000);
  });

  endLPInput.addEventListener('keydown', (event) => {
    let value = parseInt(endLPInput.value, 10) || 0;
    if (value >= 2000 && event.key !== 'Backspace' && event.key !== 'Delete') {
      event.preventDefault();
    }
  });
}

// increment / decrement functions

function decrementValue(input) {
  if (!(input.value <= 0)) {
    input.value = parseInt(input.value) - 25;
    $(input).trigger('change');
  }

  checkLPDifference();
  load_variables();
}

function incrementValue(input) {
  let value = parseInt(input.value, 10) || 0;

  if (input === endLPInput) {
    input.value = value + 25 > 2000 ? 2000 : value + 25; // Lock at 2000
  } else if (input === startLPInput) {
    input.value = value + 25 > 1950 ? 1950 : value + 25; // Lock at 1950
  }

  $(input).trigger('change');
  checkLPDifference();
  load_variables();
}

function checkLPDifference() {
  if (form_id == 1) {
    startLP = parseInt(startLPInput.value);
    endLP = parseInt(endLPInput.value);

    if (endLP - startLP < 50 && start_tier == 8) {
      endLPInput.value = startLP + 50;
      $(endLPInput).trigger('change');
    }

    load_variables();
  }
}

function processOrderSummaryIcons() {
  let ranks = {
    0: 'Unranked',
    1: 'Iron',
    2: 'Bronze',
    3: 'Silver',
    4: 'Gold',
    5: 'Platinum',
    6: 'Emerald',
    7: 'Diamond',
    8: 'Master',
    9: 'GrandMaster',
    10: 'Challenger',
  };

  let arenas = {
    1: 'Wood',
    2: 'Bronze',
    3: 'Silver',
    4: 'Gold',
    5: 'Gladiator',
  };

  let divisions = {
    1: 'IV',
    2: 'III',
    3: 'II',
    4: 'I',
  };

  var startTier = $('input[name="start_tier"]:checked').val();
  var endTier = $('input[name="end_tier"]:checked').val();

  var start_arena = $('input[name="start_arena"]').val();
  var $mastery_level = $('input[name="mastery_level"]').val();
  var clash_boost = $('input[name="clash_boost"]').val();
  var level_boost = $('input[name="level_boost"]').val();

  var startDivision = $('input[name="start_division"]:checked').val();
  var endDivision = $('input[name="end_division"]:checked').val();

  var start_lp = $('select[name="start_lp"]').find(':selected').text();
  var queue_type = $('select[name="queue_type"]').find(':selected').text();

  var start_lp_input = $('#start_lp_input').val();
  var end_lp_input = $('#end_lp_input').val();

  if (start_arena) {
    $('.current-rank-img').attr('src', '/public/assets/core/main/img/lol/arenas/' + start_tier + '.webp');

    $('.current-summary-rank-img').attr('src', '/public/assets/core/main/img/lol/arenas/' + start_tier + '.webp');

    $('.current-summary-rank-name').text(arenas[start_tier]);
  } else if ($mastery_level) {
    $('.current-rank-img').attr('src', '/public/assets/core/main/img/lol/mastery/' + startTier + '.webp');

    $('.desired-rank-img').attr('src', '/public/assets/core/main/img/lol/mastery/' + endTier + '.webp');

    $('.current-summary-rank-img').attr('src', '/public/assets/core/main/img/lol/mastery/' + startTier + '.webp');

    $('.desired-summary-rank-img').attr('src', '/public/assets/core/main/img/lol/mastery/' + endTier + '.webp');

    $('.current-summary-rank-name').text('Level ' + startTier);
    $('.desired-summary-rank-name').text('Level ' + endTier);
  } else if (clash_boost) {
    startTier = $('select[name="start_tier"]').find(':selected').val();
    var boosters = $('input[name="boosters"]').val();

    $('.current-summary-rank-name').text(`Tier ${startTier} (${boosters} Boosters)`);
  } else if (level_boost) {
    startTier = $('input[name="start_tier"]').val();
    endTier = $('input[name="end_tier"]').val();

    $('.current-summary-rank-name').text(`Level ${startTier}`);
    $('.desired-summary-rank-name').text(`Level ${endTier}`);
  } else {
    $('.current-rank-img').attr('src', '/public/assets/core/main/img/lol/ranks/mini/' + startTier + '.png');

    $('.desired-rank-img').attr('src', '/public/assets/core/main/img/lol/ranks/mini/' + endTier + '.png');

    $('.current-summary-rank-img').attr('src', '/public/assets/core/main/img/lol/ranks/mini/' + startTier + '.png');

    $('.desired-summary-rank-img').attr('src', '/public/assets/core/main/img/lol/ranks/mini/' + endTier + '.png');

    if (startTier > 7) {
      $('.current-summary-rank-name').text(ranks[startTier]);
      $('.current-summary-lp').text(`[ ${start_lp_input} LP ]`);
    } else {
      $('.current-summary-rank-name').text(ranks[startTier] + ' ' + divisions[startDivision]);

      $('.current-summary-lp').text(`[ ${start_lp} ]`);
    }

    if (endTier > 7) {
      $('.desired-summary-rank-name').text(ranks[endTier]);
      $('.desired-summary-lp').text(`[ ${end_lp_input} LP ]`);
    } else {
      $('.desired-summary-rank-name').text(ranks[endTier] + ' ' + divisions[endDivision]);

      $('.desired-summary-lp').text('');
    }

    $('.game-mode').text(queue_type);
  }
}

$(
  'input[name="start_tier"], input[name="end_tier"], input[name="start_division"], input[name="end_division"], select[name="start_lp"], select[name="queue_type"], [name="start_lp_full"], [name="end_lp_full"], input[name="start_arena"], select[name="start_tier"]'
).on('change', function () {
  processOrderSummaryIcons();
});

const startLevel = $('#start_level');
const endLevel = $('#end_level');

function decrementLevel(input) {
  if ($(input).val() <= 1) {
    return;
  }

  $(input).val(parseInt($(input).val()) - 1);
  $(input).trigger('change');

  adjustLevelDifference();
}

function incrementLevel(input) {
  $(input).val(parseInt($(input).val()) + 1);
  $(input).trigger('change');

  adjustLevelDifference();
}

function adjustLevelDifference() {
  if (parseInt(startLevel.val()) >= parseInt(endLevel.val())) {
    endLevel.val(parseInt(startLevel.val()) + 1);
    endLevel.trigger('change');
  }
}
