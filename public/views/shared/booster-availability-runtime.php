<?php
/**
 * Shared runtime for the booster availability switch (Online / Away / Offline).
 *
 * Insert once per page. It is markup-agnostic and driven by data attributes, so the
 * booster dashboard header and the website header can style their own controls:
 *
 *   [data-lb-avail-option="online|away|offline"]  clickable option / segment
 *   [data-lb-avail-root]                          gets data-status mirrored onto it
 *   [data-lb-avail-label]                         text content is replaced by the label
 *
 * Every marked element on the page is repainted together, so a chip in the header and
 * the control inside the dropdown stay in sync without either knowing about the other.
 */
if (!defined('LB_AVAIL_RUNTIME_RENDERED')) {
    define('LB_AVAIL_RUNTIME_RENDERED', true);

    $__availRuntimeStatuses = function_exists('lb_booster_presence_statuses') ? lb_booster_presence_statuses() : [];
    $__availRuntimeLabels = [];
    foreach ($__availRuntimeStatuses as $__k => $__meta) {
        $__availRuntimeLabels[$__k] = $__meta['label'];
    }
?>
<script>
(function () {
  if (window.__lbAvailInit) return;
  window.__lbAvailInit = true;

  var AJAX_URL = "<?= defined('AJAX_URL') ? AJAX_URL : '/ajax' ?>";
  var LABELS = <?= json_encode($__availRuntimeLabels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var busy = false;

  function paint(status) {
    var label = LABELS[status] || status;

    document.querySelectorAll('[data-lb-avail-root]').forEach(function (el) {
      el.setAttribute('data-status', status);
      el.classList.toggle('is-busy', busy);
    });

    document.querySelectorAll('[data-lb-avail-label]').forEach(function (el) {
      el.textContent = label;
    });

    document.querySelectorAll('[data-lb-avail-option]').forEach(function (el) {
      el.classList.toggle('is-active', el.getAttribute('data-lb-avail-option') === status);
    });

    document.dispatchEvent(new CustomEvent('lb:availability', { detail: { status: status, label: label } }));
  }

  function currentStatus() {
    var root = document.querySelector('[data-lb-avail-root]');
    return root ? (root.getAttribute('data-status') || 'offline') : 'offline';
  }

  function choose(status) {
    if (busy || !status || status === currentStatus()) return;

    var previous = currentStatus();
    busy = true;
    paint(status); // optimistic, so the UI never feels laggy

    var form = new URLSearchParams();
    form.append('action', 'booster_set_availability');
    form.append('status', status);

    fetch(AJAX_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      credentials: 'include',
      body: form.toString()
    })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        busy = false;
        paint(res && res.success ? res.status : previous);
        if (res && res.sendToast && typeof window.sendToast === 'function') {
          window.sendToast(res.sendToast);
        }
      })
      .catch(function () {
        busy = false;
        paint(previous);
      });
  }

  document.addEventListener('click', function (ev) {
    var option = ev.target.closest ? ev.target.closest('[data-lb-avail-option]') : null;
    if (!option) return;
    ev.preventDefault();
    choose(option.getAttribute('data-lb-avail-option'));
  });
})();
</script>
<?php } ?>
