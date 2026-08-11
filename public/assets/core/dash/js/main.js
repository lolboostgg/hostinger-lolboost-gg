
let footer_pop = false;
function footer_pop_toggle(state) {
    if (state == 'show') {
        $('#footer_popup').removeClass('d-none');
        footer_pop = true;
    } else {
        $('#footer_popup').addClass('d-none');
        footer_pop = false;
    }
}
function get_url_path() {
    var url = window.location.href;
    var path = url.split('-area/');
    path = path[1].split('?')[0];
    return path;
}
function create_toast(type, title, message) {
    // bootstrap toast
    let toast_html = '<div class="toast slide-down toast-show bg-' + type + ' text-white fw-500 text-center fade show top-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="toast-body">' + message + '</div></div>'
    $('.toast-container').append(toast_html);
    setTimeout(function () {
        $('.toast-container .toast:last-child').removeClass('slide-down');
    }, 400);

    // remove toast after 5 seconds with opacity transition
    setTimeout(function () {
        $('.toast-container .toast:last-child').addClass('slide-up-negative');
        setTimeout(function () {
            $('.toast-container .toast:last-child').remove();
        }, 300);
    }, 5000);
}

/**
 * Shows the toast that a dashboard AJAX endpoint returned.
 *
 * Every ajax.php action answers with { success, sendToast: {type,title,message} }.
 * Pages that post with jQuery often receive the raw string, so parse it here and
 * fall back to a generic message when the endpoint sent none.
 *
 * @returns {boolean} whether the request itself succeeded
 */
function toast_from_response(res, fallbackMessage, fallbackTitle) {
    if (typeof res === 'string') {
        try { res = JSON.parse(res); } catch (e) { res = null; }
    }

    var toast = (res && res.sendToast) ? res.sendToast : null;
    // success is only explicitly false on failure; many actions omit it entirely.
    var ok = !(res && res.success === false) && !(toast && (toast.type === 'danger' || toast.type === 'error'));

    // Field validation answers with { validationErrors: { field: 'message' } }.
    var validationError = null;
    if (res && res.validationErrors && typeof res.validationErrors === 'object') {
        for (var field in res.validationErrors) {
            if (res.validationErrors[field]) { validationError = res.validationErrors[field]; ok = false; break; }
        }
    }

    if (toast && toast.message) {
        create_toast(toast.type || (ok ? 'success' : 'danger'), toast.title || '', toast.message);
    } else if (validationError) {
        create_toast('danger', 'Error', validationError);
    } else if (fallbackMessage) {
        create_toast(ok ? 'success' : 'danger', fallbackTitle || (ok ? 'Saved' : 'Error'), fallbackMessage);
    }

    return ok;
}

// Network/500 errors never carry a sendToast payload — always tell the user.
function toast_request_failed(message) {
    create_toast('danger', 'Error', message || 'Request failed. Please try again.');
}

function fetch_api(action, post_data = {}) {
    post_data['action'] = action;
    let request_data = new FormData();
    for (var key in post_data) {
        request_data.append(key, post_data[key]);
    }
    return $.ajax({
        url: ajax_url,
        data: request_data,
        dataType: 'text',
        async: true,
        method: "POST",
        cache: true,
        processData: false,
        contentType: false,
        error: function () {
        },
        success: function (response) {

        }
    })
}

$(document).ready(function () {
    var theme_mode = localStorage.getItem("hs_theme");
    $("html").attr('data-theme', theme_mode);

    var activePage = get_url_path();
    $('#navbarVerticalMenu a.nav-link').each(function () {
        var linkPage = $(this).attr('data-link');
        if (activePage == linkPage) {
            $(this).addClass("active");
            $(this).parent().addClass("here show");
            // if ($(this).parent().parent().hasClass('menu-sub')) {
            //     $(this).parent().parent().parent().addClass("here show");
            // }
        }
    });



});