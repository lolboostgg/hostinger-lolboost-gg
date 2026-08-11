<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/order_match_panel_sharing_guard_fast.php';

lb_order_match_send_pending_panel_sharing_alerts($db, DISCORD_ADMIN_WEBHOOK_URL, 20);