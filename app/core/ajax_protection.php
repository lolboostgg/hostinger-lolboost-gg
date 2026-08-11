<?php

namespace App;

/**
 * ═══════════════════════════════════════════════════════════
 * AJAX BOT PROTECTION & RATE LIMITING
 * ═══════════════════════════════════════════════════════════
 */

// ── Konfiguration ────────────────────────────────────────────
if (!defined('RATE_LIMIT_DIR'))   define('RATE_LIMIT_DIR',   sys_get_temp_dir() . '/rl_lolboost');
if (!defined('RL_WINDOW'))        define('RL_WINDOW',         60);
if (!defined('RL_MAX_GLOBAL'))    define('RL_MAX_GLOBAL',     300);  // 300/Min – normales Polling sicher drunter
if (!defined('RL_MAX_LOGIN'))     define('RL_MAX_LOGIN',       5);
if (!defined('RL_MAX_REGISTER'))  define('RL_MAX_REGISTER',    3);
// A 10-second block let a hammering bot return six times per minute, which is
// why the rate limiter never actually reduced load. These are the real values.
if (!defined('RL_BLOCK_DURATION'))define('RL_BLOCK_DURATION', 300);  // 5 minutes
if (!defined('RL_LOGIN_BLOCK'))   define('RL_LOGIN_BLOCK',    300);  // 5 minutes

const LOGIN_ACTIONS   = ['auth_client_login','auth_booster_login','auth_admin_login','auth_seller_login'];
const REGISTER_ACTIONS = ['auth_client_register'];

function rl_get_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

function rl_get_file(string $ip, string $type): string
{
    if (!is_dir(RATE_LIMIT_DIR)) @mkdir(RATE_LIMIT_DIR, 0700, true);
    return RATE_LIMIT_DIR . '/' . $type . '_' . md5($ip) . '.json';
}

function rl_load(string $file): array
{
    if (!file_exists($file)) return ['count' => 0, 'window_start' => time(), 'blocked_until' => 0];
    $data = @json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : ['count' => 0, 'window_start' => time(), 'blocked_until' => 0];
}

function rl_save(string $file, array $data): void
{
    @file_put_contents($file, json_encode($data), LOCK_EX);
}

function rl_check(string $ip, string $type, int $max, int $block_duration): bool
{
    $file = rl_get_file($ip, $type);
    $data = rl_load($file);
    $now  = time();

    if ($data['blocked_until'] > $now) return true;
    if (($now - $data['window_start']) >= RL_WINDOW) {
        $data = ['count' => 0, 'window_start' => $now, 'blocked_until' => 0];
    }
    $data['count']++;
    if ($data['count'] > $max) {
        $data['blocked_until'] = $now + $block_duration;
        rl_save($file, $data);
        return true;
    }
    rl_save($file, $data);
    return false;
}

function rl_is_ajax_request(): bool
{
    $xhr = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
    if ($xhr === 'xmlhttprequest') return true;
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (strpos($accept, 'application/json') !== false && strpos($accept, 'text/html') === false) return true;
    return false;
}

function rl_block_response(string $message, int $retry_after = 60): void
{
    http_response_code(429);
    header('Retry-After: ' . $retry_after);

    if (rl_is_ajax_request()) {
        header('Content-Type: application/json');
        echo json_encode([
            'sendToast' => [
                'type'    => 'warning',
                'title'   => 'Too Many Requests',
                'message' => $message,
            ],
            'blocked' => true,
        ]);
        exit;
    }

    if ($retry_after < 60) {
        $wait_text = $retry_after . ' second' . ($retry_after != 1 ? 's' : '');
    } else {
        $mins = (int)ceil($retry_after / 60);
        $wait_text = $mins . ' minute' . ($mins > 1 ? 's' : '');
    }
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>429 – Too Many Requests</title>
<link rel="icon" type="image/svg+xml" sizes="64x64" href="https://lolboost.gg/assets/core/main/img/logos/SVG/icon-bg.svg?v6">
<link rel="icon" type="image/png" href="https://lolboost.gg/assets/core/main/img/logos/PNG/icon-bg-64x64.png?v6">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    background: #0a0b0f;
    color: #e0e0e0;
    font-family: "Inter", sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }
  .bg {
    position: fixed; inset: 0; z-index: 0;
    background: radial-gradient(ellipse 80% 60% at 50% 0%, #1a0e2e 0%, #0a0b0f 70%);
  }
  .grid {
    position: fixed; inset: 0; z-index: 0;
    background-image:
      linear-gradient(rgba(99,60,180,0.07) 1px, transparent 1px),
      linear-gradient(90deg, rgba(99,60,180,0.07) 1px, transparent 1px);
    background-size: 40px 40px;
  }
  .wrap {
    position: relative; z-index: 1;
    text-align: center;
    padding: 2rem;
    max-width: 480px;
    width: 100%;
  }
  .icon-ring {
    width: 80px; height: 80px;
    border-radius: 50%;
    border: 2px solid rgba(180,120,255,0.4);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 2rem;
    background: rgba(99,60,180,0.15);
    animation: pulse 2.5s ease-in-out infinite;
  }
  @keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(140,80,255,0.3); }
    50%       { box-shadow: 0 0 0 16px rgba(140,80,255,0); }
  }
  .icon-ring svg { width: 36px; height: 36px; stroke: #a070f0; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
  .code {
    font-family: "Rajdhani", sans-serif;
    font-size: 88px;
    font-weight: 700;
    letter-spacing: -2px;
    line-height: 1;
    color: #fff;
    margin-bottom: 0.5rem;
  }
  .code span { color: #8c4fff; }
  h1 {
    font-family: "Rajdhani", sans-serif;
    font-size: 22px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #c0a0ff;
    margin-bottom: 1rem;
  }
  p {
    font-size: 15px;
    color: #888;
    line-height: 1.7;
    margin-bottom: 2rem;
  }
  .timer-box {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(99,60,180,0.12);
    border: 1px solid rgba(140,80,255,0.25);
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    font-size: 14px;
    color: #a070f0;
    margin-bottom: 2rem;
  }
  .timer-box svg { width: 16px; height: 16px; stroke: #a070f0; fill: none; stroke-width: 2; }
  .btn {
    display: inline-block;
    padding: 0.75rem 2rem;
    background: #8c4fff;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-family: "Rajdhani", sans-serif;
    font-size: 16px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    transition: background 0.2s, transform 0.1s;
  }
  .btn:hover { background: #7a3de0; transform: translateY(-1px); }
  .divider {
    width: 60px; height: 2px;
    background: linear-gradient(90deg, transparent, #8c4fff, transparent);
    margin: 1.5rem auto;
  }
</style>
</head>
<body>
<div class="bg"></div>
<div class="grid"></div>
<div class="wrap">
  <div class="icon-ring">
    <svg viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
  </div>
  <div class="code">4<span>2</span>9</div>
  <h1>Too Many Requests</h1>
  <div class="divider"></div>
  <p>' . htmlspecialchars($message) . '</p>
  <div class="timer-box">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    Please wait ' . $wait_text . ' before trying again
  </div>
  <br>
  <a href="/" class="btn">Back to lolboost.gg</a>
</div>
</body>
</html>';
    exit;
}

function ajax_run_protection(): void
{
    $ip     = rl_get_ip();
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    // 1. Globales Limit
    if (rl_check($ip, 'global', RL_MAX_GLOBAL, RL_BLOCK_DURATION)) {
        rl_block_response('Too many requests. Please wait a moment.', RL_BLOCK_DURATION);
    }

    // 2. Login brute-force protection
    if (in_array($action, LOGIN_ACTIONS, true)) {
        if (rl_check($ip, 'login', RL_MAX_LOGIN, RL_LOGIN_BLOCK)) {
            rl_block_response('Too many login attempts. Please wait 5 minutes.', RL_LOGIN_BLOCK);
        }
    }

    // 3. Registration spam protection
    if (in_array($action, REGISTER_ACTIONS, true)) {
        if (rl_check($ip, 'register', RL_MAX_REGISTER, RL_BLOCK_DURATION)) {
            rl_block_response('Too many registration attempts. Please wait a moment.', RL_BLOCK_DURATION);
        }
    }

    // 4. Block known bot user-agents
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (trim($ua) === '') {
        rl_block_response('Invalid request.', RL_BLOCK_DURATION);
    }
    $bot_patterns = ['python-requests','curl/','wget/','go-http-client','scrapy','httpie','libwww','java/','okhttp','axios/','node-fetch','got/','undici'];
    foreach ($bot_patterns as $pattern) {
        if (stripos($ua, $pattern) !== false) {
            if (rl_check($ip, 'bot_ua', 5, RL_BLOCK_DURATION)) {
                rl_block_response('Automated requests are not allowed.', RL_BLOCK_DURATION);
            }
            break;
        }
    }
}

ajax_run_protection();

// Cleanup alter Dateien (1% Chance)
if (mt_rand(1, 100) === 1 && is_dir(RATE_LIMIT_DIR)) {
    $cutoff = time() - 3600;
    foreach (glob(RATE_LIMIT_DIR . '/*.json') ?: [] as $f) {
        // Concurrent cleanup runs can unlink a file between glob() and filemtime(),
        // which used to emit "stat failed" warnings into the error log.
        $mtime = @filemtime($f);
        if ($mtime !== false && $mtime < $cutoff) @unlink($f);
    }
}
