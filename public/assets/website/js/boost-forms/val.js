var form = $('#val_boost_form');
var type = $('input[name="type"]').val();
var form_id = parseInt($('input[name="form_id"]').val(), 10);

var start_tier, start_division, end_tier, end_division;
var is_priority, is_bonus_win, start_rr, end_rr, server, discount_code, matches, is_duo, hours;

function duo_change() {
  // New LoL-style layout uses .hidden class (not bootstrap d-none)
  if (String(is_duo) === '0') {
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

  // Only hide/show the toggle wrapper, never other blocks
  var $toggleGroup = $duoRadios.closest('.toggle-group');

  // For Valorant: do NOT hard-disable duo except in the edge case "Radiant-only" style selections.
  // (Keeping it permissive fixes the "can't select solo/duo" issue when tiers are missing on some forms.)
  var st = isNaN(start_tier) ? 0 : start_tier;
  var et = isNaN(end_tier) ? 0 : end_tier;

  // If BOTH sides are Immortal/Radiant (>=8) on Rank Boost, force Solo (safe default)
  if ((type === 'rank') && st >= 8 && et >= 8) {
    $duoRadios.prop('checked', false);
    $duoRadios.filter('[value="0"]').prop('checked', true);
    is_duo = 0;
    if ($toggleGroup.length) $toggleGroup.hide();
  } else {
    if ($toggleGroup.length) $toggleGroup.show();
  }

  duo_change();
}

function update_pricing() {
  form.submit();
}

// tier converter (kept for compatibility)
function convert_tier(tier) {
  var tiers = {
    0: 'unranked',
    1: 'iron',
    2: 'bronze',
    3: 'silver',
    4: 'gold',
    5: 'platinum',
    6: 'diamond',
    7: 'ascendent',
    8: 'immortal',
    9: 'radiant',
  };
  if (typeof tier === 'string') {
    for (var key in tiers) {
      if (tiers[key] === tier) return key;
    }
  } else if (typeof tier === 'number') {
    return tiers[tier];
  }
}

function convert_division(division) {
  var divisions = { 3: 'I', 2: 'II', 1: 'III' };
  if (typeof division === 'string') {
    for (var key in divisions) {
      if (divisions[key] === division) return key;
    }
  } else if (typeof division === 'number') {
    return divisions[division];
  }
}

$('input[name="currency"]').val($('#local_currency').val());

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
      if (response.redirectUrl) window.location.href = response.redirectUrl;
      if (response.refreshPage) location.reload();

      if (response.updatePricing) {
        if (response.price == 0 || response.price < 0) {
          $boost_form.find('button[type="submit"] span.indicator-label').text('Invalid Selection');
          $boost_form.find('button[type="submit"]').attr('disabled', true);
        } else {
          // Some templates use "Continue" / "Buy Now"; we keep label untouched if it exists.
          var $lbl = $boost_form.find('button[type="submit"] span.indicator-label');
          if ($lbl.length) $lbl.text($lbl.text() || 'Continue');
          $boost_form.find('button[type="submit"]').attr('disabled', false);
        }

        // New layout uses .total-price (and also #total-price in some templates)
        $('.total-price, #total-price, #new-price').text(response.currency + util_price(response.price));
      }

      if (response.discount_status) {
        $('#discount_alert').text(response.discount_msg).removeClass('text-danger').addClass('text-success');
      } else if (response.discount_msg != null) {
        $('#discount_alert').text(response.discount_msg).removeClass('text-success').addClass('text-danger');
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

$('input:radio, input:checkbox, select').on('change', function () {
  load_variables();
});

let timeout;
$("input:text, :input[type='number']").on('keyup change', function () {
  clearTimeout(timeout);
  timeout = setTimeout(function () {
    load_variables();
  }, 400);
});

// Sliders (match LoL behaviour)
var number_format = wNumb({ decimals: 0 });

function init_matches_slider(elId, startVal, minVal, maxVal) {
  var el = document.getElementById(elId);
  if (!el) return;

  // prevent double init
  if (el.noUiSlider) return;

  noUiSlider.create(el, {
    start: startVal,
    step: 1,
    tooltips: [true],
    connect: 'lower',
    range: { min: [minVal], max: [maxVal] },
    format: number_format,
  });

  el.noUiSlider.on('update', function (values) {
    $('input[name="matches0"]').val(number_format.from(values[0]));
  });

  el.noUiSlider.on('change.one', function (values) {
    $('.win-count').text(number_format.from(values[0]));
    load_variables();
  });
}

// Placement/Normal: #matches_slider
init_matches_slider('matches_slider', 3, 1, 5);

// Win Boost: #matches_slider1 (usually max 3, but keep 5 if you ever enable bigger packages)
init_matches_slider('matches_slider1', 2, 1, 3);

// Coaching hours
var hours_slider = document.getElementById('hours_slider');
if (hours_slider != null) {
  if (!hours_slider.noUiSlider) {
    noUiSlider.create(hours_slider, {
      start: 5,
      step: 1,
      tooltips: [true],
      connect: 'lower',
      range: { min: [1], max: [10] },
      format: number_format,
    });
    hours_slider.noUiSlider.on('update', function (values) {
      $('input[name="hours"]').val(number_format.from(values[0]));
    });
    hours_slider.noUiSlider.on('change.one', function (values) {
      $('.hour-count').text(number_format.from(values[0]));
      load_variables();
    });
  }
}

function load_variables() {
  start_tier = parseInt($('input:radio[name="start_tier"]:checked').val(), 10);
  start_division = parseInt($('input:radio[name="start_division"]:checked').val(), 10);
  end_tier = parseInt($('input:radio[name="end_tier"]:checked').val(), 10);
  end_division = parseInt($('input:radio[name="end_division"]:checked').val(), 10);

  // avoid NaN explosions on forms that don't have end_* inputs
  if (isNaN(start_division)) start_division = 0;
  if (isNaN(end_tier)) end_tier = 0;
  if (isNaN(end_division)) end_division = 0;

  // matches/hours values are submitted via inputs; slider writes matches0
  matches = $('input[name="matches0"]').val() || $('input[name="matches"]').val();
  hours = $('input[name="hours"]').val();

  is_priority = $('input:checkbox[name="is_priority"]:checked').val();
  is_bonus_win = $('input:checkbox[name="is_bonus_win"]:checked').val();

  // RR: rank-like forms may use manual RR input for Immortal/Radiant
  if (type === 'rank' || type === 'win' || type === 'placement') {
    if (start_tier >= 8) {
      start_rr = ($('#start_rr_input').val() ?? $('input[name="start_rr_full"]').val() ?? 0);
    } else {
      start_rr = $('select[name="start_rr"]').val();
    }
  } else {
    start_rr = $('select[name="start_rr"]').val();
  }

  if (type === 'rank' && end_tier >= 8) {
    end_rr = ($('#end_rr_input').val() ?? $('input[name="end_rr_full"]').val() ?? 0);
  }

  is_duo = $('input:radio[name="is_duo"]:checked').val() ?? 0;

  server = $('select[name="server"]').val();
  discount_code = $('input[name="discount_code"]').val();

  duo_change();
  disable_duo();
  update_pricing();
}

// RR selectors show/hide (kept from old val.js but made safe)
$('input:radio[name="start_tier"], input:radio[name="start_division"]').on('change', function () {
  start_tier = parseInt($('input:radio[name="start_tier"]:checked').val(), 10);
  if (start_tier > 7 || start_tier === 0) {
    $('select[name="start_rr"]').parent().hide();
    $('#start_rr_full').show();
    $('#start_divisions').hide();
  } else {
    $('select[name="start_rr"]').parent().show();
    $('#start_rr_full').hide();
    $('#start_divisions').show();
  }
});

$('input:radio[name="end_tier"], input:radio[name="end_division"]').on('change', function () {
  end_tier = parseInt($('input:radio[name="end_tier"]:checked').val(), 10);
  if (end_tier > 7 || end_tier === 0) {
    $('select[name="end_rr"]').parent().parent().hide();
    $('#end_rr_full').show();
    $('#end_divisions').hide();
  } else {
    $('select[name="end_rr"]').parent().parent().show();
    $('#end_rr_full').hide();
    $('#end_divisions').show();
  }
});

$(window).on('load', function () {
  load_variables();
});

// Manual RR inputs
var startRRInput = document.getElementById('start_rr_input');
var endRRInput = document.getElementById('end_rr_input');

function getRRInputNumber(input, attribute, fallback) {
  if (!input) return fallback;
  var value = parseInt(input.getAttribute(attribute), 10);
  return Number.isFinite(value) ? value : fallback;
}

if (startRRInput != null) {
  startRRInput.addEventListener('input', function (event) {
    event.target.value = event.target.value.replace(/[^0-9]/g, '');
    if (event.target.value !== '') {
      var value = parseInt(event.target.value, 10);
      var max = getRRInputNumber(event.target, 'max', 1500);
      event.target.value = Math.min(max, value);
    }
  });
  startRRInput.addEventListener('blur', function (event) {
    if (event.target.value === '') event.target.value = getRRInputNumber(event.target, 'min', 0);
    var changed = checkRRDifference();
    $(changed ? endRRInput : event.target).trigger('change');
  });
}

if (endRRInput != null) {
  endRRInput.addEventListener('input', function (event) {
    event.target.value = event.target.value.replace(/[^0-9]/g, '');
    if (event.target.value !== '') {
      var value = parseInt(event.target.value, 10);
      var max = getRRInputNumber(event.target, 'max', 1500);
      event.target.value = Math.min(max, value);
    }
  });
  endRRInput.addEventListener('blur', function (event) {
    if (event.target.value === '') event.target.value = getRRInputNumber(event.target, 'min', 0);
    var changed = checkRRDifference();
    $(changed ? endRRInput : event.target).trigger('change');
  });
}

function decrementValue(input) {
  if (!input) return;
  var value = parseInt(input.value, 10) || 0;
  var min = getRRInputNumber(input, 'min', 0);
  var step = getRRInputNumber(input, 'step', 25);
  input.value = Math.max(min, value - step);
  checkRRDifference();
  $(input).trigger('change');
}

function incrementValue(input) {
  if (!input) return;
  var value = parseInt(input.value, 10) || 0;
  var max = getRRInputNumber(input, 'max', 1500);
  var step = getRRInputNumber(input, 'step', 25);
  input.value = Math.min(max, value + step);
  checkRRDifference();
  $(input).trigger('change');
}

function checkRRDifference() {
  var changed = false;
  // keep legacy constraint for rank boost if needed
  if (type === 'rank' && startRRInput && endRRInput) {
    var s = parseInt(startRRInput.value, 10) || 0;
    var e = parseInt(endRRInput.value, 10) || 0;
    if ((e - s) < 50 && (parseInt(start_tier, 10) === 8)) {
      var endMax = getRRInputNumber(endRRInput, 'max', 1500);
      endRRInput.value = Math.min(endMax, s + 50);
      changed = true;
    }
  }
  return changed;
}

$(window).on('load', function () {
  // Ensure pricing + UI is initialized
  load_variables();

  // --- Agents modal (robust: works with Bootstrap 5 OR bootstrap-native like your LoL forms) ---
  function getAgentsModalInstance() {
    var el = document.getElementById('agents_modal');
    if (!el) return null;

    // bootstrap-native (some pages use global `Modal`)
    if (typeof window.Modal === 'function') {
      try { return new window.Modal('#agents_modal'); } catch (e) {}
    }

    // Bootstrap 5
    if (window.bootstrap && window.bootstrap.Modal) {
      try { return window.bootstrap.Modal.getOrCreateInstance(el, { backdrop: 'static' }); } catch (e) {}
      try { return new window.bootstrap.Modal(el, { backdrop: 'static' }); } catch (e) {}
    }

    return null;
  }

  function syncAgentsHiddenInput() {
    var select = document.getElementById('agents_select');
    var input = document.getElementById('agents_input');
    if (!select || !input) return;
    var values = Array.from(select.selectedOptions).map(function (o) { return o.value; });
    input.value = values.join(',');
  }

  var agents_md = getAgentsModalInstance();

  // Open/close when toggled
  $('#is_agents').off('change.agentsModal').on('change.agentsModal', function () {
    if (!agents_md) return;
    if ($(this).is(':checked')) {
      agents_md.show();
    } else {
      // clear selection if disabled
      $('#agents_select option:selected').prop('selected', false);
      $('#agents_select').trigger('change');
      syncAgentsHiddenInput();
      agents_md.hide();
    }
  });

  // Keep hidden input synced for backend pricing / saving
  $('#agents_select').off('change.agentsSync').on('change.agentsSync', function () {
    syncAgentsHiddenInput();
    // changing agents should also refresh price if it affects it
    load_variables();
  });

  // Sync once on load (in case something is preselected)
  syncAgentsHiddenInput();
});

function processOrderSummaryIcons() {
  let ranks = {
    0: 'Unranked',
    1: 'Iron',
    2: 'Bronze',
    3: 'Silver',
    4: 'Gold',
    5: 'Platinum',
    6: 'Diamond',
    7: 'Ascendent',
    8: 'Immortal',
    9: 'Radiant',
  };

  let divisions = { 1: 'I', 2: 'II', 3: 'III' };

  var startTier = $('input[name="start_tier"]:checked').val();
  var endTier = $('input[name="end_tier"]:checked').val();
  var startDivision = $('input[name="start_division"]:checked').val();
  var endDivision = $('input[name="end_division"]:checked').val();
  var start_rr_text = $('select[name="start_rr"]').find(':selected').text();
  var queue_type = $('select[name="queue_type"]').find(':selected').text();
  var start_rr_input = $('#start_rr_input').val();
  var end_rr_input = $('#end_rr_input').val();

  if (startTier) {
    $('.current-summary-rank-img, .current-rank-img').attr('src', '/public/assets/core/main/img/val/ranks/mini/' + startTier + '.png');
  }
  if (endTier) {
    $('.desired-summary-rank-img, .desired-rank-img').attr('src', '/public/assets/core/main/img/val/ranks/mini/' + endTier + '.png');
  }

  if (startTier && parseInt(startTier, 10) > 7) {
    $('.current-summary-rank-name').text(ranks[startTier]);
    if ($('.current-summary-lp').length) $('.current-summary-lp').text(`[ ${start_rr_input || 0} RR ]`);
  } else if (startTier) {
    $('.current-summary-rank-name').text(ranks[startTier] + (startDivision ? (' ' + divisions[startDivision]) : ''));
    if ($('.current-summary-lp').length) $('.current-summary-lp').text(start_rr_text ? `[ ${start_rr_text} ]` : '');
  }

  if (endTier && parseInt(endTier, 10) > 7) {
    $('.desired-summary-rank-name').text(ranks[endTier]);
    if ($('.desired-summary-lp').length) $('.desired-summary-lp').text(`[ ${end_rr_input || 0} RR ]`);
  } else if (endTier) {
    $('.desired-summary-rank-name').text(ranks[endTier] + (endDivision ? (' ' + divisions[endDivision]) : ''));
    if ($('.desired-summary-lp').length) $('.desired-summary-lp').text('');
  }

  if (queue_type) $('.game-mode').text(queue_type);
}

$('input[name="start_tier"], input[name="end_tier"], input[name="start_division"], input[name="end_division"], select[name="start_rr"], select[name="queue_type"], [name="start_rr_full"], [name="end_rr_full"]').on('change', function () {
  processOrderSummaryIcons();
});

// === Agents modal (LoL markup 1:1) ===
(function () {
  if (!window.jQuery) return;
  var $ = window.jQuery;

  function syncAgentsInput() {
    var vals = $('#agents_select').val() || [];
    $('#agents_input').val(vals.join(','));
  }

  function ensureSelect2() {
    if (!$.fn || typeof $.fn.select2 !== 'function') return;
    var $sel = $('#agents_select');
    if (!$sel.length) return;
    if ($sel.data('select2')) return;

    $sel.select2({
      closeOnSelect: false,
      width: '100%',
      dropdownParent: $('#agents_modal')
    });
  }

  var md = null;
  try {
    if (typeof window.Modal === 'function') md = new window.Modal('#agents_modal');
  } catch (e) { md = null; }

  function showModal() {
    ensureSelect2();
    if (md && typeof md.show === 'function') md.show();
    else $('#agents_modal').addClass('show');
  }

  function hideModal() {
    if (md && typeof md.hide === 'function') md.hide();
    else $('#agents_modal').removeClass('show');
  }

  // Toggle by checkbox
  $(document).off('change.valAgentsLol1', '#is_agents');
  $(document).on('change.valAgentsLol1', '#is_agents', function () {
    if (this.checked) showModal();
    else {
      // turned off: clear selection
      $('#agents_select').val(null).trigger('change');
      syncAgentsInput();
      hideModal();
      if (typeof window.load_variables === 'function') window.load_variables();
    }
  });

  // Cancel buttons: close + uncheck
  $(document).off('click.valAgentsCancel1', '#agents_modal .cancel-agents, #agents_modal .modal-header .close-modal:not(.save-agents)');
  $(document).on('click.valAgentsCancel1', '#agents_modal .cancel-agents, #agents_modal .modal-header .close-modal:not(.save-agents)', function (e) {
    e.preventDefault();
    hideModal();
    $('#is_agents').prop('checked', false);
  });

  // Save: must run BEFORE modal closes; stop the default close-modal handler if Modal() binds one.
  $(document).off('click.valAgentsSave1', '#agents_modal .save-agents');
  $(document).on('click.valAgentsSave1', '#agents_modal .save-agents', function (e) {
    e.preventDefault();
    e.stopPropagation();
    syncAgentsInput();
    hideModal();
    $('#is_agents').prop('checked', true);
    if (typeof window.load_variables === 'function') window.load_variables();
    else $('#agents_input').trigger('change');
  });

  // Keep hidden in sync on changes
  $(document).off('change.valAgentsSync1', '#agents_select');
  $(document).on('change.valAgentsSync1', '#agents_select', function () {
    syncAgentsInput();
  });

  $(function () {
    ensureSelect2();
    syncAgentsInput();
  });
})();

$(function () {
  const $agents = $('#agents_select');
  if (!$agents.length) return;

  function formatAgent(state) {
    if (!state.id) return state.text;

    const img = $(state.element).data('image');
    if (!img) return state.text;

    // gleiche Klassen wie LoL -> übernimmt 1:1 dein Styling
    return $(`
      <span class="s2-champ">
        <img class="s2-champ__img" src="${img}" alt="">
        <span class="s2-champ__text">${state.text}</span>
      </span>
    `);
  }

  // Re-init to ensure settings apply (wie bei LoL)
  if ($agents.hasClass('select2-hidden-accessible')) {
    $agents.select2('destroy');
  }

  $agents.select2({
    width: '100%',
    closeOnSelect: false,
    dropdownAutoWidth: true,

    // Suchfeld aktiv
    minimumResultsForSearch: 0,

    templateResult: formatAgent,
    templateSelection: formatAgent,
    escapeMarkup: m => m,

    // Selected aus Dropdown entfernen + Suche weiterhin möglich (wie bei LoL)
    matcher: function (params, data) {
      if (!data.id) return data;

      const selected = $agents.val() || [];
      if (Array.isArray(selected) && selected.includes(data.id)) {
        return null;
      }

      const term = (params.term || '').toLowerCase();
      if (!term) return data;

      const text = (data.text || '').toLowerCase();
      return text.includes(term) ? data : null;
    }
  });

  // Dropdown live aktualisieren nach select/unselect
  $agents.on('select2:select select2:unselect', function () {
    if ($agents.data('select2') && $agents.data('select2').isOpen()) {
      $agents.select2('close').select2('open');
    }
  });
});
