class Modal {
  constructor(selector) {
    this.$el = $(selector);

    this.$el.find('.close-modal').on('click', () => this.hide());
  }

  show() {
    this.$el.addClass('show');
    $('body').addClass('overlay');
  }

  hide() {
    this.$el.removeClass('show');
    $('body').removeClass('overlay');
  }
}

$(document).on('click', '[data-action="open-modal"]', function (e) {
  e.preventDefault();
  const target = $(this).data('target');
  const modal = new Modal(target);
  modal.show();
});

function util_price(price) {
  return (price / 100).toFixed(2);
}

function create_toast(type, title, message) {
  const id = 'toast-' + Date.now();

  const toast = $(`
        <div class="toast ${type}" id="${id}">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
    `);

  $('#toast-container').append(toast);

  setTimeout(() => {
    toast.css({
      animation: 'fadeOut 0.4s forwards',
    });

    setTimeout(() => toast.remove(), 400);
  }, 4000);
}

$(window).on('scroll', function () {
  if ($(this).scrollTop() > 20) {
    $('.navbar-top, .navbar-mobile').addClass('scrolled');
  } else {
    $('.navbar-top, .navbar-mobile').removeClass('scrolled');
  }
});

$(function () {
  $('.accordion-header').on('click', function () {
    const $item = $(this).closest('.accordion-item');

    $item.siblings('.accordion-item').removeClass('active').find('.accordion-content').slideUp(200);

    $item.toggleClass('active');
    $item.find('.accordion-content').stop(true, true).slideToggle(200);
  });

  // ── Single Selects: Custom Dropdown (kein Select2/AttachBody/zoom-Problem) ──
  // .boost-form hat zoom:0.8 + html hat zoom:0.88 = effektiv 0.704
  // Select2 mit AttachBody kann unter doppeltem zoom nie korrekt positionieren.
  // Loesung: komplett eigenes Dropdown das position:absolute relativ zum
  // Wrapper ist – kein JS-Koordinaten-Mapping noetig.

  (function initCustomSelects() {
    function closeAll(exceptWrapper) {
      document.querySelectorAll('.cs-wrapper.cs-open').forEach(function(w) {
        if (w !== exceptWrapper) {
          w.classList.remove('cs-open');
          var dd = w.querySelector('.cs-dropdown');
          if (dd) dd.style.display = 'none';
        }
      });
    }

    function buildCustomSelect(sel) {
      if (sel.dataset.csInit) return;
      sel.dataset.csInit = '1';
      sel.style.display = 'none';

      var wrapper = document.createElement('div');
      wrapper.className = 'cs-wrapper';

      var trigger = document.createElement('button');
      trigger.type = 'button';
      trigger.className = 'cs-trigger';
      trigger.innerHTML = '<span class="cs-trigger__label"></span><span class="cs-trigger__arrow"><svg viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';

      var dropdown = document.createElement('div');
      dropdown.className = 'cs-dropdown';
      dropdown.style.display = 'none';

      var opts = sel.options;
      var hasSearch = !sel.dataset.noSearch && opts.length > 5;

      if (hasSearch) {
        var sw = document.createElement('div');
        sw.className = 'cs-search-wrap';
        sw.innerHTML = '<svg class="cs-search-icon" viewBox="0 0 16 16" fill="none"><circle cx="6.5" cy="6.5" r="5" stroke="currentColor" stroke-width="1.4"/><path d="M10.5 10.5L14 14" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>';
        var inp = document.createElement('input');
        inp.className = 'cs-search';
        inp.type = 'text';
        inp.placeholder = 'Search...';
        sw.appendChild(inp);
        dropdown.appendChild(sw);
      }

      var list = document.createElement('div');
      list.className = 'cs-list';
      dropdown.appendChild(list);

      sel.parentNode.insertBefore(wrapper, sel);
      wrapper.appendChild(sel);
      wrapper.appendChild(trigger);
      wrapper.appendChild(dropdown);

      function renderOpts(filter) {
        list.innerHTML = '';
        Array.from(opts).forEach(function(o) {
          if (!o.text.trim()) return;
          if (filter && o.text.toLowerCase().indexOf(filter.toLowerCase()) === -1) return;
          var item = document.createElement('div');
          item.className = 'cs-option' + (o.selected ? ' cs-option--selected' : '') + (o.disabled ? ' cs-option--disabled' : '');
          item.textContent = o.text.trim();
          item.dataset.value = o.value;
          list.appendChild(item);
        });
      }

      function syncLabel() {
        var s = sel.options[sel.selectedIndex];
        wrapper.querySelector('.cs-trigger__label').textContent = s ? s.text.trim() : '';
      }

      function open() {
        closeAll(wrapper);
        wrapper.classList.add('cs-open');
        dropdown.style.display = '';
        renderOpts();
        syncLabel();
        // scroll to selected
        var sel2 = list.querySelector('.cs-option--selected');
        if (sel2) list.scrollTop = sel2.offsetTop - 40;
        if (hasSearch) { inp.value = ''; inp.focus(); }
      }

      function close() {
        wrapper.classList.remove('cs-open');
        dropdown.style.display = 'none';
      }

      syncLabel();
      renderOpts();

      trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        wrapper.classList.contains('cs-open') ? close() : open();
      });

      list.addEventListener('click', function(e) {
        var item = e.target.closest('.cs-option');
        if (!item || item.classList.contains('cs-option--disabled')) return;
        sel.value = item.dataset.value;
        sel.dispatchEvent(new Event('change', { bubbles: true }));
        syncLabel();
        renderOpts();
        close();
      });

      if (hasSearch) {
        inp.addEventListener('input', function() { renderOpts(inp.value); });
        inp.addEventListener('click', function(e) { e.stopPropagation(); });
      }

      // Sync wenn select von aussen geaendert wird (z.B. lol.js)
      sel.addEventListener('change', function() { syncLabel(); renderOpts(); });
    }

    // Init alle single selects mit class select2
    document.querySelectorAll('select.select2:not([multiple])').forEach(buildCustomSelect);

    // MutationObserver fuer spaeter hinzugefuegte selects
    new MutationObserver(function() {
      document.querySelectorAll('select.select2:not([multiple]):not([data-cs-init])').forEach(buildCustomSelect);
    }).observe(document.body, { childList: true, subtree: true });

    // Global schliessen bei Klick ausserhalb
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.cs-wrapper')) closeAll();
    });
  })();

  // ── Multi-select Select2 (Champions/Agents in Modals) ────────────────────
  // dropdownParent = Modal damit Dropdown im Modal-Viewport bleibt
  var Utils = $.fn.select2.amd.require('select2/utils');
  var Dropdown = $.fn.select2.amd.require('select2/dropdown');
  var DropdownSearch = $.fn.select2.amd.require('select2/dropdown/search');
  var CloseOnSelect = $.fn.select2.amd.require('select2/dropdown/closeOnSelect');
  var AttachBody = $.fn.select2.amd.require('select2/dropdown/attachBody');
  var dropdownAdapter = Utils.Decorate(Utils.Decorate(Utils.Decorate(Dropdown, DropdownSearch), CloseOnSelect), AttachBody);
  var noSearchAdapter = Utils.Decorate(Utils.Decorate(Dropdown, CloseOnSelect), AttachBody);

  $('.select2[multiple]').each(function () {
    const $el = $(this);
    const enableSearch = $el.attr('id') === 'champions_select' || $el.data('enable-search') === true;
    if ($el.hasClass('select2-hidden-accessible')) { $el.select2('destroy'); }

    const $modal     = $el.closest('.modal');
    const $filterBox = $el.closest('.filter-box');
    const inFilterBox = $filterBox.length > 0;

    // filter-box Selects: KEIN AttachBody, kein dropdownParent-Append.
    // Dropdown bleibt im normalen DOM-Flow direkt beim Select -> oeffnet
    // sich inline nach unten ohne Positionierungsprobleme.
    // Modal/Boost-Form Selects: AttachBody mit dropdownParent=modal.
    let adapter, parent;
    if (inFilterBox) {
      adapter = enableSearch
        ? Utils.Decorate(Utils.Decorate(Dropdown, DropdownSearch), CloseOnSelect)
        : Utils.Decorate(Dropdown, CloseOnSelect);
      parent = null; // kein dropdownParent = bleibt inline
    } else {
      adapter = enableSearch ? dropdownAdapter : noSearchAdapter;
      parent  = $modal.length ? $modal : $('body');
    }

    const cfg = {
      width: '100%',
      closeOnSelect: false,
      placeholder: $el.data('placeholder') || 'Select an option',
      allowClear: true,
      dropdownAutoWidth: false,
      multiple: true,
      dropdownAdapter: adapter,
      minimumResultsForSearch: enableSearch ? 0 : Infinity,
    };
    if (parent) cfg.dropdownParent = parent;

    $el.select2(cfg);

    $el.on('select2:opening select2:closing', function () {
      if (enableSearch) return;
      $(this).parent().find('.select2-search__field').prop('disabled', true);
    });
  });

  $('[data-tooltip]').on('mouseenter', function (e) {
    const $el = $(this);
    const text = $el.data('tooltip');

    const $tooltip = $('<div class="tooltip"></div>').text(text).appendTo('body');
    $tooltip.css('position', 'fixed');
    $tooltip.addClass('show');
    $el.data('tooltipEl', $tooltip);

    // Einmalig ueber der Maus-Position beim Reinfahren positionieren (nicht
    // bei jeder Mausbewegung neu, sonst wandert/zittert das Tooltip ueber
    // kompakten Kacheln).
    positionTooltip($tooltip, e.clientX, e.clientY);
  });

  $('[data-tooltip]').on('mouseleave', function () {
    const $el = $(this);
    const $tooltip = $el.data('tooltipEl');
    if ($tooltip) {
      $tooltip.remove();
      $el.removeData('tooltipEl');
    }
  });

  function positionTooltip($tooltip, x, y) {
    const spacing = 14;
    const ttWidth = $tooltip.outerWidth();
    const ttHeight = $tooltip.outerHeight();
    const winWidth = $(window).width();

    // Direkt ueber der Maus zentriert.
    let left = x - ttWidth / 2;
    let top = y - ttHeight - spacing;
    let below = false;

    // Viewport-Grenzen: links/rechts
    if (left < 8) left = 8;
    if (left + ttWidth > winWidth - 8) left = winWidth - ttWidth - 8;

    // Falls kein Platz oben → unterhalb
    if (top < 8) {
      top = y + spacing;
      below = true;
    }

    // Pfeil bleibt genau ueber der Maus-Position, auch wenn die Box selbst
    // wegen der Viewport-Grenzen seitlich verschoben werden musste.
    let arrowLeft = x - left;
    arrowLeft = Math.max(12, Math.min(ttWidth - 12, arrowLeft));

    $tooltip.toggleClass('tooltip--below', below);
    $tooltip.css('--tt-arrow-left', arrowLeft + 'px');
    $tooltip.css({ top, left });
  }

  $('.nav-tabs a').on('click', function (e) {
    e.preventDefault();
    var target = $(this).attr('href');

    $('.nav-tabs a').removeClass('active');
    $(this).addClass('active');

    $('.tab-pane').removeClass('active');
    $(target).addClass('active');

    history.replaceState(null, null, target);
  });

  if (location.hash) {
    var hash = location.hash;
    var $tab = $('.nav-tabs a[href="' + hash + '"]');
    if ($tab.length) $tab.trigger('click');
  }

  $('#login-btn, #login-btn-mob, #login-btn-checkout').on('click', function () {
    const loginModal = new Modal('#login_modal');
    $('.sidenav-mob').removeClass('show');
    loginModal.show();
  });

  $('#login-btn-checkout').on('click', function () {
    const loginModal = new Modal('#login_modal');
    $('#login_modal').find('input[name="action"]').val('auth_client_login');
    $('.sidenav-mob').removeClass('show');
    loginModal.show();
  });

  $('#guest-btn-checkout').on('click', function () {
    const guestModal = new Modal('#guest_checkout_modal');
    $('.sidenav-mob').removeClass('show');
    guestModal.show();
  });

  $('#frg-pwd').on('click', function () {
    const loginModal = new Modal('#login_modal');
    loginModal.hide();
    const frgPwdModal = new Modal('#forgot_password_modal');
    frgPwdModal.show();
  });

  $('.menu-icon, .close-sidenav').on('click', function () {
    $('body').toggleClass('overlay');
    $('.sidenav-mob').toggleClass('show');
  });
});
