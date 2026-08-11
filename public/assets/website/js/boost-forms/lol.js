var masterDuoNoticeStyles = document.getElementById('master-duo-disabled-notice-styles');
if (!masterDuoNoticeStyles) {
  masterDuoNoticeStyles = document.createElement('style');
  masterDuoNoticeStyles.id = 'master-duo-disabled-notice-styles';
  masterDuoNoticeStyles.textContent = `
    .master-duo-disabled-notice {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin-top: 12px;
      padding: 12px 14px;
      border: 1px solid rgba(139, 92, 246, 0.38);
      border-radius: 14px;
      background: linear-gradient(135deg, rgba(124, 92, 255, 0.16), rgba(12, 10, 28, 0.72));
      color: #f3f0ff;
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.06);
      font-size: 13px;
      font-weight: 700;
      line-height: 1.35;
    }
    .master-duo-disabled-notice__icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 24px;
      height: 24px;
      min-width: 24px;
      border-radius: 999px;
      background: rgba(139, 92, 246, 0.24);
      color: #b9a7ff;
      box-shadow: inset 0 0 0 1px rgba(185, 167, 255, 0.18);
    }
    .master-duo-disabled-notice__text {
      padding-top: 2px;
    }
  `;
  document.head.appendChild(masterDuoNoticeStyles);
}

var form = $('#lol_boost_form');
var type = $('input[name="type"]').val();
var form_id = parseInt($('input[name="form_id"]').val(), 10);
var start_tier, start_division, end_tier, end_division, is_priority, is_bonus_win, start_lp, end_lp, lp_gain, server, discount_code, matches, is_duo, hours;

function duo_change() {
  if (is_duo == 0) {
    $('.solo-option').removeClass('hidden');
    $('.duo-option').addClass('hidden').find('input').prop('checked', false);
  } else {
    $('.duo-option').removeClass('hidden');
    $('.solo-option').addClass('hidden').find('input').prop('checked', false);
  }
}

function disable_duo() {
  var $duoRadios = $('input:radio[name="is_duo"]');
  if (!$duoRadios.length) return;

  // Only hide/show the actual toggle wrapper (never the whole order summary)
  var $toggleGroup = $duoRadios.closest('.toggle-group');
  var $masterDuoNotice = $('#master-duo-disabled-notice');

  function hideMasterDuoNotice() {
    if ($masterDuoNotice.length) {
      $masterDuoNotice.hide();
    }
  }

  function showMasterDuoNotice() {
    if (!$masterDuoNotice.length && $toggleGroup.length) {
      $masterDuoNotice = $('<div id="master-duo-disabled-notice" class="master-duo-disabled-notice" role="note" aria-live="polite"><span class="master-duo-disabled-notice__icon"><i class="fas fa-info-circle"></i></span><span class="master-duo-disabled-notice__text">Duo Queue is disabled for Master, Grandmaster and Challenger. Solo only.</span></div>');
      $toggleGroup.after($masterDuoNotice);
    }
    if ($masterDuoNotice.length) {
      $masterDuoNotice.show();
    }
  }

  hideMasterDuoNotice();
  $duoRadios.prop('disabled', false);
  if ($toggleGroup.length) $toggleGroup.show();

  // Rank Boost, Win Boost, the supported TFT forms, Pro Games and
  // Duo Pass may use Duo even on tier 8+.
  var isMasterDuoAllowedForm = (
    form_id === 1 ||
    form_id === 2 ||
    form_id === 21 ||
    form_id === 22 ||
    form_id === 24 ||
    form_id === 26 ||
    form_id === 27
  );

  // TFT Placement Boost (form_id 23) stays Solo only.
  if (form_id === 23) {
    $duoRadios.prop('checked', false);
    $duoRadios.filter('[value="0"]').prop('checked', true);
    is_duo = 0;
    if ($toggleGroup.length) $toggleGroup.hide();
    duo_change();
    return;
  }

  // Pro Games and Duo Pass are dedicated play-with-booster products.
  if (form_id === 26 || form_id === 27 || form_id === 35 || form_id === 36) {
    $duoRadios.prop('checked', false);
    $duoRadios.filter('[value="1"]').prop('checked', true);
    is_duo = 1;
    if ($toggleGroup.length) $toggleGroup.hide();
    duo_change();
    return;
  }

  // Riot disables Duo Queue from Master upward.
  // Only if current/start rank reaches Master+ (tier 8+), force Solo.
  var selectedStartTier = parseInt($('input:radio[name="start_tier"]:checked').val(), 10);
  var isMasterPlusOrder = !isNaN(selectedStartTier) && selectedStartTier >= 8;

  if (isMasterPlusOrder && !isMasterDuoAllowedForm) {
    $duoRadios.prop('checked', false);
    $duoRadios.filter('[value="0"]').prop('checked', true);
    $duoRadios.filter('[value="1"]').prop('disabled', true);
    is_duo = 0;
    showMasterDuoNotice();
    duo_change();
    return;
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
  var _buyForm = document.getElementById('lol_boost_form');
  if (!_buyForm) return;
  if (form_id === 26 || form_id === 35) {
    var boosterId = $('#pg-booster-id').val();
    var $bid = $(_buyForm).find('input[name="selected_booster_id"]');
    if ($bid.length) { $bid.val(boosterId); }
    else { $(_buyForm).append('<input type="hidden" name="selected_booster_id" value="' + boosterId + '">'); }
  }
  $(_buyForm).find('input[name="buy_now"]').remove();
  $(_buyForm).append('<input type="hidden" name="buy_now" value="1">');
  $(_buyForm).trigger('submit');
});

$('#lol_boost_form').on('keyup keypress', function (e) {
  var keyCode = e.keyCode || e.which;
  if (keyCode === 13) {
    e.preventDefault();
    return false;
  }
});
$('#lol_boost_form').submit(function () {
  // Guard: only process if triggered on the actual FORM, not a child element
  var formEl = document.getElementById('lol_boost_form');
  if (!formEl || this.tagName !== 'FORM') return;
  $boost_form = $('#lol_boost_form');
  var formData = new FormData(formEl);

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

      try {
        response = JSON.parse(response);
      } catch (e) {
        console.error('Pro Games AJAX parse error. Raw response:', response);
        // Try to extract JSON from mixed PHP output
        var jsonMatch = response.match(/(\{.*\})/s);
        if (jsonMatch) {
          try { response = JSON.parse(jsonMatch[1]); } catch(e2) { return; }
        } else { return; }
      }
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
        $('.total-price').text(response.currency + util_price(response.price));
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
        var ctText = (form_id === 27 || form_id === 36)
          ? '~ ' + response.completion_time + ' Hours'
          : response.completion_time;
        $('#completion-time').text(ctText);
      }
    },
  });

  return false;
});

// declare variables for the form and the inputs

$('input:radio, input:checkbox, select').on('change', function () {
  load_variables();
});

// Champion checkboxes inside the custom modal are inserted after page load.
// Use a delegated listener so even the very first champion selection updates
// the price immediately, without requiring Save or toggling the extra option.
$(document)
  .off('change.lbChampionLivePrice', '#champions_roles_modal input[name="champions[]"]')
  .on('change.lbChampionLivePrice', '#champions_roles_modal input[name="champions[]"]', function () {
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
    start: form_id == 4 ? 5 : 3,
    step: 1,
    tooltips: [true],
    connect: 'lower',
    range: {
      min: [1],
      max: form_id == 4 ? [20] : [5],
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

// Pro Games slider (form_id 26) — 1 to 10 games
var pg_slider = document.getElementById('pg_games_slider');
if (pg_slider != null) {
  noUiSlider.create(pg_slider, {
    start: 3,
    step: 1,
    tooltips: [true],
    connect: 'lower',
    range: {
      min: [1],
      max: [10],
    },
    format: number_format,
  });
  pg_slider.noUiSlider.on('update', function (values, handle) {
    var val = number_format.from(values[0]);
    $('input[name="matches"]').val(val);
    $('input[name="matches0"]').val(val);
    $('.win-count').text(val);
  });
  pg_slider.noUiSlider.on('change.one', function (values) {
    var val = number_format.from(values[0]);
    $('input[name="matches"]').val(val);
    $('input[name="matches0"]').val(val);
    $('.win-count').text(val);
    load_variables();
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
      max: [4],
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

function apply_form_overrides() {
  // Form ID 23: hide Queue Type selector (keep a safe default)
  if (form_id === 23) {
    var $qt = $('select[name="queue_type"]');
    if ($qt.length) {
      $qt.val('solo_/_duo');
      try { $qt.trigger('change'); } catch (e) {}
      $qt.closest('.option').hide();

      // ensure value is submitted even if the select is hidden/removed
      if (!$('input[type="hidden"][name="queue_type"]').length) {
        $('.boost-form').append('<input type="hidden" name="queue_type" value="solo_/_duo">');
      }
    }
  }
}

function load_variables() {
  apply_form_overrides();
  start_tier = parseInt($('input:radio[name="start_tier"]:checked').val(), 10);
  start_division = parseInt($('input:radio[name="start_division"]:checked').val(), 10);
  end_tier = parseInt($('input:radio[name="end_tier"]:checked').val(), 10);
  end_division = parseInt($('input:radio[name="end_division"]:checked').val(), 10);

  // Master+ has no divisions -> avoid NaN
  if (isNaN(start_division)) start_division = 0;
  if (isNaN(end_division)) end_division = 0;

  // When tier is Master+ force divisions to 0
  if (start_tier >= 8) start_division = 0;
  if (end_tier >= 8) end_division = 0;

  matches = $('input[name="matches"]').val();
  hours = $('input[name="hours"]').val();
  is_priority = $('input:checkbox[name="is_priority"]:checked').val();
  is_bonus_win = $('input:checkbox[name="is_bonus_win"]:checked').val();

  // Treat TFT Rank Boost (21) like LoL Rank for LP inputs
  var rankLike = (form_id == 1 || form_id == 2 || form_id == 21 || form_id == 24);

  // Start LP
  if (rankLike) {
    if (start_tier >= 8) {
      start_lp = ($('#start_lp_input').val() ?? $('input[name="start_lp_full"]').val() ?? 0);
    } else {
      start_lp = $('select[name="start_lp"]').val();
    }
  } else {
    start_lp = $('select[name="start_lp"]').val();
  }

  // End LP (LoL Rank + TFT Rank)
  if (form_id == 1 || form_id == 21 || form_id == 24) {
    if (end_tier >= 8) {
      end_lp = ($('#end_lp_input').val() ?? $('input[name="end_lp_full"]').val() ?? 0);
    } else {
      end_lp = $('select[name="end_lp"]').val();
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

    if (form_id == 1 || form_id == 21 || form_id == 24) {
      $('#current_lp_dropdown').hide();
    }
  } else {
    $('select[name="start_lp"]').parent().show();
    $('#start_lp_full').hide();
    $('#start_divisions').show();
    if (form_id == 1 || form_id == 21 || form_id == 24) {
      $('#current_lp_dropdown').show();
    }
    // 🔥 removed the extra hide
  }
});

$('input:radio[name="end_tier"], input:radio[name="end_division"]').on('change', function () {
  end_tier = parseInt($('input:radio[name="end_tier"]:checked').val());
  if (end_tier > 7 || end_tier == 0) {
    $('select[name="end_lp"]').parent().parent().hide();
    $('#end_lp_full').show();
    $('#end_divisions').hide();
  } else {
    $('select[name="end_lp"]').parent().parent().show();
    $('#end_lp_full').hide();
    $('#end_divisions').show();
    // 🔥 removed the extra hide
  }
});

// Pro Games: when selected_booster_id changes, recalculate price
$(document).on('change', 'input[name="selected_booster_id"]', function() {
  load_variables();
});

// Pro Games: also trigger on matches input change (from slider or buttons)
$(document).on('change', 'input[name="matches"]', function() {
  if (form_id === 26 || form_id === 35) {
    $('.win-count').text($(this).val());
    load_variables();
  }
});

$(window).on('load', function () {
  load_variables();
  let champ_roles_md = new Modal('#champions_roles_modal');

  $('#is_champions_roles').change(function () {
    if ($(this).is(':checked')) {
      champ_roles_md.show();
    } else {
      champ_roles_md.hide();
    }
  });
});

// on window loaded event

// manual lp inputs
var startLPInput = document.getElementById('start_lp_input');
var endLPInput = document.getElementById('end_lp_input');

function getLPInputNumber(input, attribute, fallback) {
  if (!input) return fallback;
  var value = parseInt(input.getAttribute(attribute), 10);
  return Number.isFinite(value) ? value : fallback;
}

// event listeners

if (startLPInput != null) {
  startLPInput.addEventListener('input', function (event) {
    event.target.value = event.target.value.replace(/[^0-9]/g, '');

    if (event.target.value !== '') {
      var value = parseInt(event.target.value, 10);
      var max = getLPInputNumber(event.target, 'max', 1500);
      event.target.value = Math.min(max, value);
    }
  });

  startLPInput.addEventListener('blur', function (event) {
    if (event.target.value === '') event.target.value = getLPInputNumber(event.target, 'min', 0);
    var changed = checkLPDifference();
    $(changed ? endLPInput : event.target).trigger('change');
  });
}

if (endLPInput != null) {
  endLPInput.addEventListener('input', (event) => {
    event.target.value = event.target.value.replace(/[^0-9]/g, '');

    if (event.target.value !== '') {
      var value = parseInt(event.target.value, 10);
      var max = getLPInputNumber(event.target, 'max', 1500);
      if (value > max) {
        event.target.value = max;
      }
    }
  });

  endLPInput.addEventListener('blur', function (event) {
    if (event.target.value === '') event.target.value = getLPInputNumber(event.target, 'min', 0);
    var changed = checkLPDifference();
    $(changed ? endLPInput : event.target).trigger('change');
  });
}

// increment / decrement functions

function decrementValue(input) {
  if (!input) return;
  var value = parseInt(input.value, 10) || 0;
  var min = getLPInputNumber(input, 'min', 0);
  var step = getLPInputNumber(input, 'step', 25);
  input.value = Math.max(min, value - step);
  checkLPDifference();
  $(input).trigger('change');
}

function incrementValue(input) {
  if (!input) return;
  var value = parseInt(input.value, 10) || 0;
  var max = getLPInputNumber(input, 'max', 1500);
  var step = getLPInputNumber(input, 'step', 25);
  input.value = Math.min(max, value + step);
  checkLPDifference();
  $(input).trigger('change');
}

function checkLPDifference() {
  var changed = false;
  if ((form_id == 1 || form_id == 21 || form_id == 24) && startLPInput && endLPInput) {
    var startLP = parseInt(startLPInput.value, 10) || 0;
    var endLP = parseInt(endLPInput.value, 10) || 0;

    if (endLP - startLP < 50 && start_tier == 8) {
      var endMax = getLPInputNumber(endLPInput, 'max', 1500);
      endLPInput.value = Math.min(endMax, startLP + 50);
      changed = true;
    }
  }
  return changed;
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

    if (parseInt(startTier,10) >= 8) {
      $('.current-summary-rank-name').text(ranks[startTier]);
      $('.current-summary-lp').text(`[ ${(start_lp_input || 0)} LP ]`);
    } else {
      $('.current-summary-rank-name').text(ranks[startTier] + (divisions[startDivision] ? (' ' + divisions[startDivision]) : ''));

      $('.current-summary-lp').text(`[ ${start_lp} ]`);
    }

    if (parseInt(endTier,10) >= 8) {
      $('.desired-summary-rank-name').text(ranks[endTier]);
      $('.desired-summary-lp').text(`[ ${(end_lp_input || 0)} LP ]`);
    } else {
      $('.desired-summary-rank-name').text(ranks[endTier] + (divisions[endDivision] ? (' ' + divisions[endDivision]) : ''));

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


$(function () {
  const $champ = $('#champions_select');
  if (!$champ.length) return;

  function formatChampion(state) {
    if (!state.id) return state.text;

    const img = $(state.element).data('image');
    if (!img) return state.text;

    return $(`
      <span class="s2-champ">
        <img class="s2-champ__img" src="${img}" alt="">
        <span class="s2-champ__text">${state.text}</span>
      </span>
    `);
  }

  // Re-init to ensure settings apply
  if ($champ.hasClass('select2-hidden-accessible')) {
    $champ.select2('destroy');
  }

  $champ.select2({
    width: '100%',
    closeOnSelect: false,
    dropdownAutoWidth: true,

    // ✅ SUCHFELD AKTIV (nicht deaktivieren!)
    minimumResultsForSearch: 0,

    templateResult: formatChampion,
    templateSelection: formatChampion,
    escapeMarkup: m => m,

    // ✅ Selected aus Dropdown entfernen + Suche weiterhin möglich
    matcher: function (params, data) {
      if (!data.id) return data; // optgroup/placeholder

      const selected = $champ.val() || [];
      if (Array.isArray(selected) && selected.includes(data.id)) {
        return null; // hide selected champions
      }

      const term = (params.term || '').toLowerCase();
      if (!term) return data;

      const text = (data.text || '').toLowerCase();
      return text.includes(term) ? data : null;
    }
  });

  // ✅ Dropdown live aktualisieren nach select/unselect (damit ausgewählte sofort verschwinden)
  $champ.on('select2:select select2:unselect', function () {
    if ($champ.data('select2') && $champ.data('select2').isOpen()) {
      $champ.select2('close').select2('open');
    }
  });
});
