<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Error logging into lb_php_notices.log / lb_php_errors.log was removed on request.
// It was added temporarily to diagnose the 500s and is no longer written to.
// To bring it back, restore:
//   ini_set('log_errors', '1');
//   ini_set('error_log', __DIR__ . '/lb_php_notices.log');
//   plus a register_shutdown_function() that appends fatals to lb_php_errors.log.

session_start();


const LB_TURNSTILE_SITE_KEY   = '0x4AAAAAADgwAfxpjeGwgLIG';
const LB_TURNSTILE_SECRET_KEY = '0x4AAAAAADgwAcobKsilBwnIT748SNv7ZPU';
const LB_SECURITY_COOKIE      = 'lb_security_verified';
const LB_SECURITY_COOKIE_TTL  = 7200;
const LB_SECURITY_FLAG_FILE   = __DIR__ . '/security_under_attack.flag';

function lb_security_current_path(): string {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return '/' . ltrim($path, '/');
}

function lb_security_internal_prefixes(): array {
    return [
        '/admin-area',
        '/booster-area',
        '/seller-area',
    ];
}

function lb_security_path_matches_prefix(string $path, string $prefix): bool {
    $path = '/' . ltrim(strtolower($path), '/');
    $prefix = '/' . trim(strtolower($prefix), '/');
    return $path === $prefix || str_starts_with($path, $prefix . '/');
}

function lb_security_is_internal_path(string $path): bool {
    foreach (lb_security_internal_prefixes() as $prefix) {
        if (lb_security_path_matches_prefix($path, $prefix)) {
            return true;
        }
    }
    return false;
}

function lb_security_is_https(): bool {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['HTTP_CF_VISITOR'] ?? '') && str_contains((string)$_SERVER['HTTP_CF_VISITOR'], 'https'));
}

function lb_security_cookie_params(): array {
    return [
        'expires'  => time() + LB_SECURITY_COOKIE_TTL,
        'path'     => '/',
        'secure'   => lb_security_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}


function lb_security_client_ip(): string {
    $headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    ];

    foreach ($headers as $header) {
        $value = trim((string)($_SERVER[$header] ?? ''));
        if ($value === '') {
            continue;
        }
        $ip = trim(explode(',', $value)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return 'unknown';
}

function lb_security_safe_redirect(string $redirect): string {
    $redirect = trim($redirect);
    if ($redirect === '' || $redirect[0] !== '/' || str_starts_with($redirect, '//')) {
        return '/';
    }
    if (str_starts_with($redirect, '/security-check')) {
        return '/';
    }
    return $redirect;
}

function lb_security_cookie_signature(): string {
    // Bind the "passed the challenge" cookie to the REAL visitor IP + UA.
    // NOTE: behind Cloudflare, $_SERVER['REMOTE_ADDR'] is the Cloudflare edge
    // IP, which rotates between requests. Using it here caused verified users
    // to be re-challenged with a 403 over and over. lb_security_client_ip()
    // returns the real client IP (CF-Connecting-IP / X-Forwarded-For).
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    $ip = lb_security_client_ip();
    return hash_hmac('sha256', $ua . '|' . $ip, LB_TURNSTILE_SECRET_KEY);
}

function lb_security_is_verified(): bool {
    $token = $_COOKIE[LB_SECURITY_COOKIE] ?? '';
    if (!is_string($token) || $token === '') {
        return false;
    }

    $expected = lb_security_cookie_signature();
    return hash_equals($expected, $token);
}

function lb_security_should_skip(string $path): bool {
    if (lb_security_is_internal_path($path)) {
        return true;
    }

    $skipPrefixes = [
        '/security-check',
        '/ajax',
        '/ajax.php',
        '/webhook',
        '/payment',
        '/callback',
        '/cron',
        '/api',
        '/assets',
        '/public',
    ];

    foreach ($skipPrefixes as $prefix) {
        if (lb_security_path_matches_prefix($path, $prefix)) {
            return true;
        }
    }

    return false;
}

function lb_security_suspicious_request(string $path): bool {
    $ua = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
    $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

    if ($ua === '' || strlen($ua) < 12) {
        return true;
    }

    $badUaParts = ['curl', 'python', 'scrapy', 'httpclient', 'go-http-client', 'libwww', 'wget', 'nikto', 'sqlmap', 'headless'];
    foreach ($badUaParts as $needle) {
        if (str_contains($ua, $needle)) {
            return true;
        }
    }

    $badPathParts = ['wp-admin', 'wp-login', 'xmlrpc.php', '.env', 'phpmyadmin', 'vendor/phpunit', 'boaform', 'cgi-bin'];
    $lowerPath = strtolower($path);
    foreach ($badPathParts as $needle) {
        if (str_contains($lowerPath, $needle)) {
            return true;
        }
    }

    return false;
}

function lb_security_attack_mode_enabled(): bool {
    $env = strtolower((string)getenv('LB_SECURITY_MODE'));
    return is_file(LB_SECURITY_FLAG_FILE) || in_array($env, ['1', 'on', 'true', 'attack'], true);
}

function lb_security_verify_turnstile(string $token): bool {
    if ($token === '' || LB_TURNSTILE_SECRET_KEY === '') {
        return false;
    }

    $payload = http_build_query([
        'secret'   => LB_TURNSTILE_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 8,
        ],
    ]);

    $response = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $context);
    if (!$response) {
        return false;
    }

    $data = json_decode($response, true);
    return !empty($data['success']);
}

function lb_security_render_page(string $redirect, string $error = ''): void {
    $siteKey = htmlspecialchars(LB_TURNSTILE_SITE_KEY, ENT_QUOTES, 'UTF-8');
    $redirectEsc = htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8');
    $errorHtml = $error !== '' ? '<div class="lb-sec-alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>' : '';
    $configWarning = LB_TURNSTILE_SITE_KEY === ''
        ? '<div class="lb-sec-alert">Turnstile Site Key und Secret Key müssen noch in index.php eingetragen werden.</div>'
        : '';

    http_response_code(403);
    header('Content-Type: text/html; charset=UTF-8');
    echo <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Security Check | LoLBoost.gg</title>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        :root{--bg:#0a0d1f;--text:#fff;--muted:rgba(255,255,255,.46);--muted2:rgba(255,255,255,.64);--purple:#6c63ff;--purple2:#a855f7;--blue:#818cf8;--line:rgba(255,255,255,.10);--panel:rgba(13,17,39,.72)}
        *{box-sizing:border-box}html,body{min-height:100%;margin:0}body{font-family:Inter,Montserrat,system-ui,-apple-system,Segoe UI,sans-serif;background:var(--bg);color:var(--text);overflow:hidden}.sec-wrapper{min-height:100vh;background:#0a0d1f;position:relative;overflow:hidden}.sec-wrapper:before{content:"";position:absolute;inset:-20%;background:radial-gradient(ellipse at center,rgba(99,85,255,.18) 0%,transparent 38%),radial-gradient(circle at 50% 62%,rgba(168,85,247,.10),transparent 28%);z-index:0;pointer-events:none}.sec-wrapper:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,13,31,.10),rgba(10,13,31,.72));z-index:0;pointer-events:none}#sec-canvas{position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:0}.sec-inner{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;text-align:center;padding:32px 16px}.sec-brand{position:absolute;top:42px;left:50%;transform:translateX(-50%);display:inline-flex;align-items:center;gap:12px;color:#fff;text-decoration:none}.sec-logo{height:64px;width:auto;display:block;filter:drop-shadow(0 6px 24px rgba(108,99,255,.28))}.sec-eyebrow{display:inline-flex;align-items:center;gap:10px;margin-bottom:24px;color:var(--purple);font-size:12px;font-weight:900;letter-spacing:2px;text-transform:uppercase}.sec-eyebrow:before,.sec-eyebrow:after{content:"";display:block;width:32px;height:2px;background:var(--purple);border-radius:2px}.sec-num{font-size:clamp(70px,13vw,132px);font-weight:950;line-height:1;background:linear-gradient(135deg,#818cf8 0%,#6c63ff 42%,#a855f7 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;display:inline-block;margin-bottom:10px;animation:sec-float 5s ease-in-out infinite;filter:drop-shadow(0 0 40px rgba(108,99,255,.4))}@keyframes sec-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}.sec-title{font-size:clamp(22px,3vw,34px);font-weight:900;color:#fff;margin:0 0 12px;letter-spacing:-.03em}.sec-sub{font-size:15px;color:var(--muted);max-width:520px;line-height:1.7;margin:0 auto 28px;font-weight:650}.sec-card{width:min(100%,470px);padding:18px;border-radius:26px;background:linear-gradient(180deg,rgba(17,21,47,.82),rgba(10,13,31,.78));border:1px solid var(--line);box-shadow:0 24px 80px rgba(0,0,0,.35),0 0 45px rgba(108,99,255,.12);backdrop-filter:blur(12px)}.sec-card-head{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:14px;color:#fff;font-weight:900}.sec-dot{width:10px;height:10px;border-radius:50%;background:#46d7a7;box-shadow:0 0 0 8px rgba(70,215,167,.10),0 0 24px rgba(70,215,167,.45)}.sec-widget{min-height:72px;display:flex;align-items:center;justify-content:center;margin:4px 0 0}.sec-widget .cf-turnstile{display:flex;justify-content:center;max-width:100%;overflow:hidden;border-radius:10px}.sec-widget iframe{max-width:100%}.sec-auto{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:52px;margin-top:14px;background:linear-gradient(135deg,rgba(108,99,255,.92),rgba(168,85,247,.92));border-radius:50px;padding:14px 28px;font-size:15px;font-weight:900;color:#fff;box-shadow:0 4px 24px rgba(108,99,255,.35)}.sec-auto-dot{width:8px;height:8px;border-radius:50%;background:#fff;opacity:.82;animation:sec-pulse 1.1s ease-in-out infinite}@keyframes sec-pulse{0%,100%{transform:scale(.72);opacity:.45}50%{transform:scale(1);opacity:1}}.sec-alert{margin:0 0 14px;padding:13px 15px;border-radius:14px;background:rgba(255,91,124,.12);border:1px solid rgba(255,91,124,.28);color:#ffb6c4;font-size:13px;font-weight:800;line-height:1.5}.sec-links{position:absolute;left:50%;bottom:34px;transform:translateX(-50%);display:flex;align-items:center;gap:22px;font-size:13px}.sec-links a{color:rgba(255,255,255,.58);text-decoration:none}.sec-links a:hover{color:#fff}.sec-small{margin-top:14px;color:rgba(255,255,255,.32);font-size:12px;line-height:1.6}@media(max-width:700px){body{overflow:auto}.sec-brand{top:28px}.sec-inner{padding-top:110px;padding-bottom:90px}.sec-num{font-size:82px}.sec-links{position:relative;left:auto;bottom:auto;transform:none;margin-top:32px;flex-wrap:wrap;justify-content:center}.sec-card{border-radius:22px}}
    </style>
</head>
<body>
    <main class="sec-wrapper">
        <canvas id="sec-canvas"></canvas>
        <section class="sec-inner">
            <a class="sec-brand" href="/" aria-label="LoLBoost.gg"><img class="sec-logo" src="/public/assets/website/images/logo.svg" alt="LoLBoost.gg"></a>
            <div class="sec-eyebrow">Security Check</div>
            <div class="sec-num">403</div>
            <h1 class="sec-title">Checking your browser.</h1>
            <p class="sec-sub">We are protecting the rift from automated traffic. Complete the quick verification and you will be sent back automatically.</p>
            <form class="sec-card" id="lb-security-form" method="post" action="">
                {$configWarning}
                {$errorHtml}
                <input type="hidden" name="redirect" value="{$redirectEsc}">
                <div class="sec-card-head"><span class="sec-dot"></span><span>Browser verification</span></div>
                <div class="sec-widget"><div class="cf-turnstile" data-sitekey="{$siteKey}" data-theme="dark" data-callback="lbSecurityVerified"></div></div>
                <div class="sec-auto"><span class="sec-auto-dot"></span><span>Redirecting after verification</span></div>
                <div class="sec-small">Legitimate visitors only need to complete this once.</div>
            </form>
            <div class="sec-links"><a href="/contact">Contact 24/7</a><a href="/discord">Discord</a></div>
        </section>
    </main>
    <script>
    var lbSecuritySubmitting=false;
    window.lbSecurityVerified=function(token){
        if(lbSecuritySubmitting)return;
        lbSecuritySubmitting=true;
        var form=document.getElementById('lb-security-form');
        if(!form)return;
        var field=form.querySelector('[name=\"cf-turnstile-response\"]');
        if(!field){field=document.createElement('input');field.type='hidden';field.name='cf-turnstile-response';form.appendChild(field);}
        field.value=token||field.value||'';
        window.setTimeout(function(){form.submit();},220);
    };
    (function(){var c=document.getElementById('sec-canvas');if(!c)return;var ctx=c.getContext('2d');var stars=[];function resize(){c.width=window.innerWidth;c.height=window.innerHeight}resize();window.addEventListener('resize',resize);for(var i=0;i<220;i++){stars.push({x:Math.random(),y:Math.random(),r:Math.random()*1.2+.2,speed:Math.random()*0.003+.001,phase:Math.random()*Math.PI*2})}function draw(){ctx.clearRect(0,0,c.width,c.height);var t=Date.now()/1000;for(var i=0;i<stars.length;i++){var s=stars[i];var a=.2+.5*Math.abs(Math.sin(t*s.speed*10+s.phase));ctx.beginPath();ctx.arc(s.x*c.width,s.y*c.height,s.r,0,Math.PI*2);ctx.fillStyle='rgba(160,150,255,'+a+')';ctx.fill()}requestAnimationFrame(draw)}draw()})();
    </script>
</body>
</html>
HTML;
    exit;
}

$lbSecurityPath = lb_security_current_path();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['cf-turnstile-response'])) {
    $redirect = lb_security_safe_redirect((string)($_POST['redirect'] ?? '/'));
    $turnstileToken = (string)($_POST['cf-turnstile-response'] ?? '');

    if (lb_security_verify_turnstile($turnstileToken)) {
        $cookieValue = lb_security_cookie_signature();
        setcookie(LB_SECURITY_COOKIE, $cookieValue, lb_security_cookie_params());
        header('Location: ' . $redirect, true, 302);
        exit;
    }

    lb_security_render_page($redirect, 'Verification failed. Please try again.');
}

if ($lbSecurityPath === '/security-check') {
    $redirect = lb_security_safe_redirect((string)($_GET['redirect'] ?? '/'));
    lb_security_render_page($redirect);
}

if (
    lb_security_attack_mode_enabled()
    && !lb_security_should_skip($lbSecurityPath)
    && !lb_security_is_verified()
) {
    lb_security_render_page(lb_security_safe_redirect((string)($_SERVER['REQUEST_URI'] ?? '/')));
}

require 'vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Auto Language (NO redirect, NO URL change)
|--------------------------------------------------------------------------
| Priority:
| 1) Manual selection (lang_manual=1) + cookie lang
| 2) Cloudflare country (HTTP_CF_IPCOUNTRY)
| 3) Browser Accept-Language
| 4) en
*/

$allowedLangs = [
    'en','de','fr','es','pt','it','nl','pl','ru','jp','zh','sv','no','da','fi','el','hu','cs','bg','ro','tr','hr','ar','fil','id','th'
];

function normalize_lang(?string $lang): ?string {
    if (!$lang) return null;
    $lang = strtolower(trim($lang));
    $lang = preg_replace('~[^a-z-]~', '', $lang);

    // z.B. de-DE -> de
    $lang = explode('-', $lang)[0] ?? $lang;

    // Browser-Codes -> deine Codes
    if ($lang === 'ja') $lang = 'jp';

    return $lang ?: null;
}

function pick_from_accept_language(array $allowedLangs): ?string {
    $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (!$header) return null;

    $parts = explode(',', $header);
    foreach ($parts as $part) {
        $code = trim(explode(';', $part, 2)[0]);
        $code = normalize_lang($code);
        if ($code && in_array($code, $allowedLangs, true)) {
            return $code;
        }
    }
    return null;
}

$manual = ($_COOKIE['lang_manual'] ?? '') === '1';
$cookieLang = normalize_lang($_COOKIE['lang'] ?? null);

// 1) Manuelle Wahl hat Vorrang
if ($manual && $cookieLang && in_array($cookieLang, $allowedLangs, true)) {
    if (!defined('LANG')) define('LANG', $cookieLang);
} else {
    // 2) Cloudflare Country
    $cc = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? null; // z.B. DE, FR
    $cc = $cc ? strtoupper($cc) : null;

    $countryToLang = [
        // Deutsch
        'DE' => 'de', 'AT' => 'de', 'CH' => 'de',

        // Französisch
        'FR' => 'fr', 'BE' => 'fr', 'LU' => 'fr', 'MC' => 'fr',

        // Spanisch
        'ES' => 'es','MX' => 'es','AR' => 'es','CO' => 'es','CL' => 'es','PE' => 'es','VE' => 'es','EC' => 'es',
        'GT' => 'es','CU' => 'es','BO' => 'es','DO' => 'es','HN' => 'es','PY' => 'es','SV' => 'es','NI' => 'es',
        'CR' => 'es','PA' => 'es','UY' => 'es','PR' => 'es',

        // Portugiesisch
        'PT' => 'pt','BR' => 'pt',

        // Italienisch
        'IT' => 'it','SM' => 'it','VA' => 'it',

        // Niederländisch
        'NL' => 'nl',

        // Polnisch
        'PL' => 'pl',

        // Russisch (grob)
        'RU' => 'ru','BY' => 'ru','KZ' => 'ru','KG' => 'ru',

        // Japanisch
        'JP' => 'jp',

        // Chinesisch
        'CN' => 'zh','TW' => 'zh','HK' => 'zh','MO' => 'zh','SG' => 'zh',

        // Skandinavisch
        'SE' => 'sv',
        'NO' => 'no',
        'DK' => 'da',
        'FI' => 'fi',

        // Griechisch
        'GR' => 'el','CY' => 'el',

        // Ungarisch
        'HU' => 'hu',

        // Tschechisch
        'CZ' => 'cs',

        // Bulgarisch
        'BG' => 'bg',

        // Rumänisch
        'RO' => 'ro','MD' => 'ro',

        // Türkisch
        'TR' => 'tr',

        // Kroatisch
        'HR' => 'hr',

        // Arabisch (grob)
        'SA' => 'ar','AE' => 'ar','QA' => 'ar','KW' => 'ar','BH' => 'ar','OM' => 'ar','JO' => 'ar','LB' => 'ar','IQ' => 'ar',
        'EG' => 'ar','MA' => 'ar','DZ' => 'ar','TN' => 'ar',

        // Filipino
        'PH' => 'fil',

        // Indonesisch
        'ID' => 'id',

        // Thai
        'TH' => 'th',
    ];

    $lang = null;

    if ($cc && isset($countryToLang[$cc])) {
        $lang = $countryToLang[$cc];
    }

    // 3) Browser Accept-Language fallback
    if (!$lang) {
        $lang = pick_from_accept_language($allowedLangs);
    }

    // 4) Default
    if (!$lang || !in_array($lang, $allowedLangs, true)) {
        $lang = 'en';
    }

    // Cookie setzen (damit Header direkt korrekt vorauswählt)
    setcookie('lang', $lang, [
        'expires'  => time() + 365*24*60*60,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => false,
        'samesite' => 'Lax',
    ]);

    if (!defined('LANG')) define('LANG', $lang);
}

require 'app/init.php';
