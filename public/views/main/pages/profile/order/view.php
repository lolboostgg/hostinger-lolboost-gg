<?= $this->layout('main/layouts/main', ['meta' => $meta]) ?>

<!-- Hero -->
<?= $this->insert('main/components/heroes/three', [
    'title' => $meta['h1'],
    'lead' => util_format_boost_overview($data['type'], $data)
]) ?>

<section class="container">
    <div class="row">

        <?= $this->insert('main/pages/profile/aside', ['active' => 'orders']) ?>

        <div class="col-lg-9 col-md-8 ps-lg-5 pb-5 mb-2 mb-lg-4 mt-n3 mt-md-0">
            <div class="ps-md-3 ps-lg-0 mt-md-2 py-md-4">
                <p><span class="fw-bold pe-3">Status: </span><?= util_format_boost_status($data['status']) ?></p>
                <div class="row g-4">
                    <?php if ($data['status'] == "IN_PROGRESS") : ?>
                        <div class="col-12">
                            <div class="card h-100 pb-3" style="max-height: 512px;">

                                <div class="card-header w-100 py-3 ps-4 ">
                                    <h5 class="mb-0">Order Chat</h5>
                                </div>

                                <div class="card-body overflow-scroll w-100 pb-0" id="chat_messages">

                                </div>

                                <form class="ajax-form" action="<?= AJAX_URL ?>">
                                    <input type="hidden" name="action" value="client_order_chat_send">
                                    <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
                                    <div class="card-footer d-sm-flex w-100 border-0 pt-3 pb-3 px-4">
                                        <div class="position-relative w-100 me-2 mb-3 mb-sm-0">
                                            <input type="text" name="message" class="form-control form-control-lg">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-icon btn-lg d-inline-flex ms-1">
                                            <span class="indicator-label">
                                                <i class="fa-duotone fa-paper-plane fs-sm"></i>
                                            </span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle"></span>
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($data['status'] != 'UNPAID' && $data['status'] != 'PAID') : ?>
                        <div class="col-12">
                            <div class="card h-100 pb-3" style="max-height: 512px;">

                                <div class="card-header w-100 py-3 ps-4 text-center">
                                    <?php if ($data['status'] == "COMPLETED") : ?>
                                        <h5 class="mb-0">Congratulations! Your order has been completed.</h5>
                                    <?php else : ?>
                                        <h5 class="mb-0">Your booster is working on your order!</h5>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body w-100 py-3 ps-4">
                                    <div class="text-center">
                                        <span>Satisfied with the booster's performance?</span>
                                    </div>
                                    <div class="text-center mt-3 mb-0">
                                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#send_tip_md">Send A Tip!</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header w-100 py-3 ps-4 ">
                                <h5 class="mb-0">Account</h5>
                            </div>
                            <form class="ajax-form" novalidate="" action="<?= AJAX_URL ?>">
                                <input type="hidden" name="action" value="client_update_order_account">
                                <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-6 mb-4">
                                            <label for="ign" class="form-label fs-base">Summoner Name</label>
                                            <input type="text" id="ign" name="ign" class="form-control form-control-lg" value="<?= $data['ign'] ?? null ?>">
                                        </div>
                                        <div class="col-sm-6 mb-4">
                                            <label for="rtlgn" class="form-label fs-base">Riot Login</label>
                                            <input type="text" name="login" id="rtlgn" class="form-control form-control-lg" value="<?= $data['login'] ?? null ?>">
                                        </div>
                                        <div class="col-12">
                                            <label for="rtpwd" class="form-label fs-base">Riot Password</label>
                                            <input type="text" name="password" id="rtpwd" class="form-control form-control-lg" value="<?= $data['password'] ?? null ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer w-100 ps-4">
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary">
                                            <span class="indicator-label">Save Changes</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle"></span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header w-100 py-3 ps-4 ">
                                <h5 class="mb-0">Options</h5>
                            </div>
                            <form class="ajax-form" novalidate="" action="<?= AJAX_URL ?>">
                                <input type="hidden" name="action" value="client_update_order_options">
                                <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 col-12 mb-md-0 mb-4">
                                            <label for="flash_position" class="form-label fs-base">Flash Position</label>
                                            <input type="text" id="flash_position" name="flash_position" class="form-control form-control-lg" value="<?= $data['flash_position'] ?? null ?>" placeholder="D or F">
                                        </div>
                                        <div class="col-md-6 col-12 mb-md-0 mb-4">
                                            <label for="vpn_country" class="form-label fs-base">VPN Country</label>
                                            <select name="vpn_country" id="vpn_country" class="form-select form-select-lg">
                                                <option value="">None</option>
                                                <?= util_load_country_list($data['vpn_country'] ?? null) ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer w-100 ps-4">
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary">
                                            <span class="indicator-label">Save Changes</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle"></span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header w-100 py-3 ps-4 ">
                                <h5 class="mb-0">Overview</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled list-py-1 mb-0">

                                    <li>
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center me-2">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-sm avatar-light avatar-circle">
                                                        <span class="avatar-initials">🎯</span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p class="fw-bold mb-0">Order Details</p>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <?= util_format_boost_overview($data['type'], $data) ?>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center me-2">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-sm avatar-light avatar-circle">
                                                        <span class="avatar-initials">🚧</span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p class="fw-bold mb-0">Queue Type</p>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <?= util_format_default_type($data['queue_type']) ?>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center me-2">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-sm avatar-light avatar-circle">
                                                        <span class="avatar-initials">🏁</span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p class="fw-bold mb-0">Start LP</p>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <?= $data['start_lp'] ?> LP
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center me-2">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-sm avatar-light avatar-circle">
                                                        <span class="avatar-initials">📈</span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p class="fw-bold mb-0">LP Gain</p>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <?= $data['lp_gain'] ?>
                                            </div>
                                        </div>
                                    </li>

                                </ul>
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header w-100 py-3 ps-4 ">
                                <h5 class="mb-0">Options</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled list-py-1 mb-0">
                                    <?php
                                    $options = ['roles', 'champions', 'flash_position', 'vpn_country', 'is_priority', 'is_streaming', 'is_solo_only', 'is_bonus_win', 'is_offline_mode', 'is_coaching']; ?>
                                    <?php foreach ($options as $option) : ?>
                                        <?php $ds_opt = util_format_option($option, $data[$option]) ?>
                                        <li>
                                            <div class="d-flex align-items-center">
                                                <div class="d-flex align-items-center me-2">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar avatar-sm avatar-light avatar-circle">
                                                            <span class="avatar-initials"><?= util_format_option_emoji($option) ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="fw-bold mb-0"><?= $ds_opt[0] ?></p>
                                                    </div>
                                                </div>
                                                <div class="ms-auto">
                                                    <?= $ds_opt[1] ?>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Modals -->
    <form class="ajax-form" action="<?= AJAX_URL ?>">
        <input type="hidden" name="action" value="client_send_tip">
        <input type="hidden" name="order_id" value="<?= $data['id'] ?>">
        <input type="hidden" name="booster_id" value="<?= db_get_row('orders', ['id' => $data['id']])['booster_id'] ?>">

        <div id="send_tip_md" class="modal fade" tabindex="-1" role="dialog" aria-hidden="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Send A Tip</h5>
                    </div>
                    <div class="modal-body">
                        <label class="fs-6 form-label mb-2">Enter the tip amount:</label>
                        <div class="input-group">
                            <span class="input-group-text">&euro;</span>
                            <input type="text" class="form-control" name="amount" value="" placeholder="0.00">
                        </div>
                        <div class="mt-4">
                            <label class="fs-6 form-label mb-2">Note:</label>
                            <textarea class="form-control" name="note" placeholder="Write a note..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Proceed</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>

<hr>
<?= $this->insert('main/components/testimonials/one') ?>

<?= $this->insert('main/components/cta/two') ?>



<?= $this->start('scripts') ?>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollTrigger.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/ScrollSmoother.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/plugins/gsap/SplitText.min.js"></script>
<script src="<?= ASSET_URL ?>/core/main/js/gsap-custom.js"></script>
<script src="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.js"></script>
<script src="<?= ASSET_URL ?>/origin/dash/vendor/daterangepicker/moment.min.js"></script>
<?php if ($data['status'] == "IN_PROGRESS") : ?>
    <script>
        let msg_none = false;
        let chat_json = {};
        let order_id = <?= $data['id'] ?>;
        let order_status = "<?= $data['status'] ?>";
        let user_type = "client";
        let user_id = <?= CLIENT_ID ?>;
        let post_data = new FormData();
        post_data['order_id'] = order_id;

        function play_that_sound() {
            audio.volume = 0.6;
            audio.play();
        }
        var chat_notif = new Audio(asset_url + '/core/dash/audio/default.webm');

        function message_sound() {
            chat_notif.volume = 0.6;
            chat_notif.play();
        }

        function load_message(msg_data, sender, last_sender, last_sender_id) {

            msg_data['content'] = $("<textarea/>").html(msg_data['content']).text();
            if (msg_data['sender'] == user_type && msg_data['sender_id'] == user_id) {
                msg_data['sender_name'] = "You";
                msg_data['class'] = 'bg-light';
                msg_data['class_b'] = 'end';
                message_header = `<div class="chat-message me">
                <div class="chat-message-meta mb-1">
                    <div class="chat-message-meta-user">
                        <span class="chat-message-meta-time">` + moment(msg_data['time'], "X").fromNow() + `</span>
                        <span class="chat-message-meta-user-name">` + msg_data['sender_name'] + `</span>
                    </div>
                </div>`;

            } else {
                msg_data['class'] = 'bg-light';
                msg_data['class_b'] = 'start';
                message_header = `<div class="chat-message">
                <div class="chat-message-meta mb-1">
                    <div class="chat-message-meta-user">
                        <span class="chat-message-meta-user-name">` + msg_data['sender_name'] + `</span>
                        <span class="chat-message-meta-time">` + moment(msg_data['time'], "X").fromNow() + `</span>
                    </div>
                </div>`;
            }
            if (msg_data['sender'] == last_sender) {

                message_html = `<div class="chat-message-body mt-1">
                    <div class="chat-message-content">
                        <div class="chat-message-text">
                            <p>` + msg_data['content'] + `</p>
                        </div>
                    </div>
                </div>
            </div>`;

            } else {

                console.log(last_sender)
                message_html = '<div class="d-flex justify-content-' + msg_data['class_b'] + ' mb-4">' +
                    '<div class="d-flex flex-column align-items-' + msg_data['class_b'] + '">' +
                    '<div class="d-flex align-items-center mb-2">' +
                    message_header +
                    '</div>' +
                    '<div class="p-5 rounded ' + msg_data['class'] + ' text-dark fw-bold mw-lg-400px text-' + msg_data['class_b'] + '">' + msg_data['content'] + '</div>' +
                    '</div></div>';

                message_html = message_header + `<div class="chat-message-body">
                    <div class="chat-message-content">
                        <div class="chat-message-text">
                            <p>` + msg_data['content'] + `</p>
                        </div>
                    </div>
                </div>
            </div>`;

            }

            return message_html;
        }

        function load_messages() {
            fetch_api('load_chat', post_data).done(function(response) {
                response = JSON.parse(response);
                chat_list = response.messages;
                let msg_count = Object.keys(chat_list).length;
                let chat_html = '';
                if (msg_count > 0) {

                    if (msg_count > Object.keys(chat_json).length) {
                        chat_json = chat_list;
                        let last_sender = "";
                        let last_sender_id = 0;
                        $.each(chat_list, function(key, val) {
                            if (val.sender === last_sender && val.sender_id === last_sender_id) {
                                chat_html = chat_html.slice(0, -12);
                            }
                            chat_html += load_message(val, user_type, last_sender, last_sender_id);

                            last_sender = val.sender;
                            last_sender_id = val.sender_id;

                        });
                        $('#chat_messages').html(chat_html);
                        $('#chat_messages').scrollTop($('#chat_messages')[0].scrollHeight);
                    } else {
                        $('.chat-time').each(function(i, el) {
                            let temp_time = moment($(this).attr('data-time'), "X").fromNow();
                            $(this).text(temp_time)
                        });
                    }
                    let last_message_id = Object.keys(chat_list)[Object.keys(chat_list).length - 1];
                    let last_message = chat_list[last_message_id];
                    if (last_message.sender == user_type && last_message.sender_id == user_id) {
                        let message_read = '';
                        if (last_message.seen == 1) {
                            message_read = '<span class="text-muted fs-7 mb-1"><i class="fa-solid fa-check-double"></i> Read</span>';
                        }
                        let read_html = '<div class="d-flex justify-content-end mt-n1 mb-2 pe-1" id="message-read-status">' + message_read + '</div>';
                        if ($("#message-read-status").length == 0) {
                            $('#chat_messages').append(read_html);
                            update_scroll();
                        } else {
                            $('#message-read-status').html(message_read);
                        }
                    } else {
                        if (last_message.notify == 0 && last_message.seen == 0) {
                            // console.log("notif");
                            update_message_notif(last_message_id)
                            // play sound
                            message_sound();
                            // update message
                        }
                    }
                } else {
                    if (msg_none == false) {
                        chat_html = '<div class="text-center"><h5 class="mt-5">No messages found.<br>Send one to get started!</h5></div>';
                        $('#chat_messages').html(chat_html);
                        msg_none = true;
                    }
                }
            });
        }

        function update_scroll() {
            $('#chat_messages').scrollTop($('#chat_messages')[0].scrollHeight);
        }

        function update_message_notif(message_id) {
            // append  message_id to post_data
            post_data['id'] = message_id;
            fetch_api('update_chat_seen', post_data).done(function(response) {});
        }
        $(document).ready(function() {

            setInterval(function() {
                fetch_api('check_order_status', post_data).done(function(response) {
                    response = JSON.parse(response);
                    if (response.order_status != order_status) {
                        message_sound();
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                });
            }, 5000);

            update_scroll();

            var refresh_msg_interval = setInterval(load_messages, 2000);
            load_messages();
        });
    </script>
<?php endif ?>
<?= $this->stop() ?>


<?= $this->start('styles') ?>
<link rel="stylesheet" media="screen" href="<?= ASSET_URL ?>/origin/main/vendor/swiper/swiper-bundle.min.css" />
<style>
    #chat_messages {
        min-height: 15rem;
        max-height: 20rem;
    }

    .legend-indicator {
        display: inline-block;
        width: 0.5rem;
        height: 0.5rem;
        background-color: #bdc5d1;
        border-radius: 50%;
        margin-right: 0.4375rem;
    }

    .chat-message-body {
        background: #F3F6FF;
        border-radius: 0.5rem;
        padding: 0.6rem 1rem;
        width: fit-content;
    }

    .chat-message-meta .chat-message-meta-time {
        font-size: 0.75rem;
        color: #6c757d;
    }

    .chat-message {
        margin-bottom: 1rem;
    }

    #chat_messages p {
        margin-bottom: 0;
    }

    .chat-message.me {
        display: flex;
        flex-direction: column;
        align-items: end;
        text-align: end;
    }

    .chat-message.me .chat-message-body {

        color: #fff;
        background: #6366F1;
    }

    .chat-message-meta .chat-message-meta-time {
        margin-left: 0.5rem;
        margin-right: 0.5rem;
    }

    .chat-message-meta-user-name {
        font-weight: 600;
    }
</style>
<?= $this->stop() ?>