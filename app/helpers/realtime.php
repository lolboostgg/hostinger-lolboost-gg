<?php
/**
 * Lolboost Realtime Bridge
 *
 * Usage from PHP after an order, request or notification change:
 *   require_once __DIR__ . '/../helpers/realtime.php';
 *   lb_realtime_emit('new_order', $data);
 *
 * Required constants, ideally in config.php:
 *   define('REALTIME_URL', 'https://socket.lolboost.gg');
 *   define('REALTIME_SECRET', 'same-secret-as-hostinger-env');
 */

if (!function_exists('lb_realtime_emit')) {
    /**
     * @param string $event
     * @param array $data
     * @param string|array $room Either a single room name (e.g. 'boosters') or an
     *                            array of room names (e.g. ['boosters','admins','clients']).
     *                            All rooms are notified via a single HTTP call to the
     *                            socket server, so this never multiplies request latency.
     */
    function lb_realtime_emit(string $event, array $data = [], $room = 'boosters'): bool
    {
        $url = defined('REALTIME_URL') ? rtrim((string)REALTIME_URL, '/') : 'https://socket.lolboost.gg';
        $secret = defined('REALTIME_SECRET') ? (string)REALTIME_SECRET : '';

        if ($secret === '') {
            return false;
        }

        $payload = json_encode([
            'event' => $event,
            'room' => $room,
            'data' => $data,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            return false;
        }

        $endpoint = $url . '/emit';

        // Important: this call happens synchronously inside normal page/AJAX requests
        // (e.g. while sending a chat message). Keep the timeouts very short so a slow
        // or unreachable socket server can never noticeably delay the response to the
        // user — worst case the realtime push is skipped and the existing polling
        // fallback in the frontend still picks the update up a bit later.
        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Realtime-Secret: ' . $secret,
                ],
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT_MS => 300,
                CURLOPT_TIMEOUT_MS => 500,
            ]);
            $body = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($code < 200 || $code >= 300) {
                // Silent failures made a broken realtime setup impossible to diagnose:
                // a Cloudflare challenge (403 "Just a moment...") looks exactly like a
                // working push from PHP's side. Log at most once per minute per event.
                $flag = sys_get_temp_dir() . '/lb_rt_emit_fail_' . md5($event);
                if (!is_file($flag) || (time() - (int)@filemtime($flag)) > 60) {
                    @touch($flag);
                    $snippet = is_string($body) ? substr(strip_tags($body), 0, 200) : '';
                    error_log(sprintf(
                        'Realtime emit failed: event=%s room=%s http=%d curl=%s body=%s',
                        $event,
                        is_array($room) ? implode(',', $room) : (string)$room,
                        $code,
                        $error,
                        trim(preg_replace('/\s+/', ' ', $snippet))
                    ));
                }
            }

            return $code >= 200 && $code < 300;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nX-Realtime-Secret: {$secret}\r\n",
                'content' => $payload,
                'timeout' => 0.5,
                'ignore_errors' => true,
            ],
        ]);

        $result = @file_get_contents($endpoint, false, $context);
        return $result !== false;
    }
}

if (!function_exists('lb_realtime_emit_new_order')) {
    /**
     * Builds the same toast/notification payload the booster-panel "check_new_orders"
     * polling endpoint produces (name, details, game, client_username, ...) and pushes
     * it out live as a `new_order` event, so the frontend toast/sound/browser-notification
     * has real content instead of an empty "undefined" order card.
     *
     * Call this whenever an order becomes available (or available again) for boosters
     * to claim — i.e. status = 'PAID' and booster_id is empty.
     */
    function lb_realtime_emit_new_order($order_id, string $room = 'boosters', array $eventMeta = []): bool
    {
        if (!function_exists('db_get_row')) {
            return false;
        }

        $order = db_get_row('orders', ['id' => $order_id], 1);
        if (!$order) {
            return false;
        }

        $form = db_get_row('boost_forms', ['id' => (int)($order['form_id'] ?? 0)], 1) ?: [];

        $details = '';
        if (function_exists('util_format_boost_overview')) {
            try {
                $opts = db_get_row('order_options', ['order_id' => $order_id], 1) ?: [];
                $acc = db_get_row('order_accounts', ['order_id' => $order_id], 1) ?: [];
                $merged = array_merge((array)$form, (array)$opts, (array)$acc, (array)$order);
                $game = (string)($form['game'] ?? $order['game'] ?? 'lol');
                $type = (string)($form['type'] ?? $order['type'] ?? '');
                $details = trim(strip_tags((string)util_format_boost_overview($game, $type, $merged)));
                $details = html_entity_decode($details, ENT_QUOTES, 'UTF-8');
                $details = preg_replace('/\s+/', ' ', $details);
            } catch (\Throwable $e) {
                $details = '';
            }
        }
        if ($details === '') {
            $server = strtoupper(trim((string)($order['server'] ?? '')));
            $details = trim($server . ($server !== '' ? ' - ' : '') . (string)($form['name'] ?? 'Order'));
        }

        $clientUsername = '';
        $clientIcon = '';
        $clientId = (int)($order['client_id'] ?? 0);
        if ($clientId > 0) {
            $clientRow = db_get_row('clients', ['id' => $clientId], 1);
            if ($clientRow) {
                $clientUsername = trim((string)($clientRow['username'] ?? $clientRow['name'] ?? $clientRow['email'] ?? ''));
                $clientIcon = trim((string)($clientRow['icon'] ?? ''));
            }
        }

        $data = [
            'order_id' => (int)$order_id,
            'name' => (string)($form['name'] ?? 'Order'),
            'details' => $details,
            'type' => (string)($form['type'] ?? 'order'),
            'created_at' => (string)($order['paid_at'] ?? $order['created_at'] ?? ''),
            'game' => (string)($form['game'] ?? ''),
            'created_ts' => time(),
            'client_username' => $clientUsername,
            'client_icon' => $clientIcon,
        ];

        // Reposts carry their own event identity so they are not suppressed by the
        // original order toast's dedupe key in the booster browser.
        if (!empty($eventMeta)) {
            $data = array_merge($data, $eventMeta);
        }

        return lb_realtime_emit('new_order', $data, $room);
    }
}

if (!function_exists('lb_realtime_emit_order')) {
    /**
     * Use this for events tied to ONE specific order's chat/status/account/price —
     * i.e. anything a booster, an admin, AND the client could simultaneously have
     * open on screen: 'chat_update', 'order_status_update', 'order_account_update',
     * 'price_update'. Broadcasts to all three role rooms in one request.
     *
     * Don't use this for booster-only pool events like 'new_order',
     * 'orders_panel_update' or 'booster_request' — those stay booster-room only.
     */
    function lb_realtime_emit_order(string $event, $order_id, array $extra = [], array $rooms = ['boosters', 'admins', 'clients']): bool
    {
        $data = array_merge(['order_id' => $order_id], $extra);
        return lb_realtime_emit($event, $data, $rooms);
    }
}


if (!function_exists('lb_realtime_schedule_seller_unread_email')) {
    /**
     * Ask the realtime service to run a delayed unread-message check.
     * The socket service deduplicates by seller + conversation, so every new
     * client message restarts the five-minute window.
     */
    function lb_realtime_schedule_seller_unread_email(array $data): bool
    {
        $url = defined('REALTIME_URL') ? rtrim((string)REALTIME_URL, '/') : 'https://socket.lolboost.gg';
        $secret = defined('REALTIME_SECRET') ? (string)REALTIME_SECRET : '';
        if ($secret === '') return false;

        $payload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($payload === false) return false;
        $endpoint = $url . '/schedule-seller-unread-email';

        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Realtime-Secret: ' . $secret,
                ],
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT_MS => 1500,
                CURLOPT_TIMEOUT_MS => 5000,
            ]);
            $response = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            if ($code < 200 || $code >= 300) {
                error_log('Seller unread scheduler failed: HTTP ' . $code . ($error !== '' ? ' - ' . $error : '') . ' response=' . substr((string)$response, 0, 300));
            }
            return $code >= 200 && $code < 300;
        }

        $context = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nX-Realtime-Secret: {$secret}\r\n",
            'content' => $payload,
            'timeout' => 5,
            'ignore_errors' => true,
        ]]);
        return @file_get_contents($endpoint, false, $context) !== false;
    }
}


if (!function_exists('lb_realtime_schedule_client_unread_email')) {
    /** Schedule a five-minute unread check for a seller message to a client. */
    function lb_realtime_schedule_client_unread_email(array $data): bool
    {
        $url = defined('REALTIME_URL') ? rtrim((string)REALTIME_URL, '/') : 'https://socket.lolboost.gg';
        $secret = defined('REALTIME_SECRET') ? (string)REALTIME_SECRET : '';
        if ($secret === '') return false;
        $payload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($payload === false) return false;
        $endpoint = $url . '/schedule-client-unread-email';
        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-Realtime-Secret: '.$secret],CURLOPT_POSTFIELDS=>$payload,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT_MS=>1500,CURLOPT_TIMEOUT_MS=>5000]);
            $response=curl_exec($ch); $code=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE); $error=curl_error($ch); curl_close($ch);
            if($code<200||$code>=300) error_log('Client unread scheduler failed: HTTP '.$code.($error!==''?' - '.$error:'').' response='.substr((string)$response,0,300));
            return $code>=200 && $code<300;
        }
        $ctx=stream_context_create(['http'=>['method'=>'POST','header'=>"Content-Type: application/json\r\nX-Realtime-Secret: {$secret}\r\n",'content'=>$payload,'timeout'=>5,'ignore_errors'=>true]]);
        return @file_get_contents($endpoint,false,$ctx)!==false;
    }
}
