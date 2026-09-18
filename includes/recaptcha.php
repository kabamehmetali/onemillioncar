<?php
/**
 * Google reCAPTCHA v3 for the public forms.
 *
 * Keys live in the settings table (Admin -> Settings -> reCAPTCHA) so they can
 * be rotated without a deployment, exactly like the Twilio credentials.
 *
 * v3 never shows a puzzle: the browser scores the visit in the background and
 * the server decides. The token is minted when the visitor presses submit, not
 * when the page loads, because a v3 token expires after two minutes and the
 * financing form takes longer than that to fill in.
 *
 * Failure policy. A missed sale costs far more than one spam lead, so the only
 * submissions refused are the ones Google actually calls suspicious:
 *
 *   REFUSED  no token at all (a bot that never ran our JavaScript), a forged
 *            short token, a token Google says it already used or that expired,
 *            an action that does not match the form, or a score below the
 *            configured threshold. The message carries the phone number so the
 *            visitor still gets through to a human.
 *   ALLOWED  everything Google declines to grade: it is unreachable, cURL is
 *            missing, the reply is unreadable, the secret is refused, the keys
 *            disagree about a real browser token, or "browser-error" comes back.
 *            Logged as "[recaptcha] allowed without a verdict" every time, so a
 *            broken key shows up in the log instead of in lost sales.
 *   SKIPPED  reCAPTCHA switched off or half-configured -- nothing is rendered
 *            or verified and the forms behave exactly as they did before.
 *
 * That last ALLOWED case matters: siteverify answers "invalid-input-response"
 * both for a forged token AND for a genuine token checked against the wrong
 * secret, and it no longer returns "invalid-input-secret" to tell them apart
 * (verified against the live API). Length is what separates them -- a real v3
 * token is hundreds of characters -- so a long token that fails is treated as
 * our misconfiguration. Without that rule one mistyped key would silently
 * swallow every enquiry the site receives.
 */
declare(strict_types=1);

const RECAPTCHA_VERIFY_URL    = 'https://www.google.com/recaptcha/api/siteverify';
const RECAPTCHA_JS_URL        = 'https://www.google.com/recaptcha/api.js';
const RECAPTCHA_FIELD         = 'g-recaptcha-response';
const RECAPTCHA_TIMEOUT       = 6; // seconds for the whole verification request
const RECAPTCHA_CONNECT       = 3; // seconds to establish the connection
const RECAPTCHA_DEFAULT_SCORE = 0.5;
const RECAPTCHA_REAL_TOKEN    = 100; // a genuine v3 token is far longer than this

/** @return array{site:string, secret:string, score:float} */
function recaptcha_config(): array
{
    $score = setting('recaptcha_min_score');
    return [
        'site'   => trim(setting('recaptcha_site_key')),
        'secret' => trim(setting('recaptcha_secret_key')),
        'score'  => is_numeric($score)
            ? min(1.0, max(0.0, (float) $score))
            : RECAPTCHA_DEFAULT_SCORE,
    ];
}

/** True when the toggle is on and both keys are present. */
function recaptcha_enabled(): bool
{
    if (setting('recaptcha_enabled') !== '1') {
        return false;
    }
    $c = recaptcha_config();
    return $c['site'] !== '' && $c['secret'] !== '';
}

/**
 * Remembers whether a form on this page asked for a token, so the footer only
 * loads Google's script on pages that actually need it.
 */
function recaptcha_used(bool $mark = false): bool
{
    static $used = false;
    if ($mark) {
        $used = true;
    }
    return $used;
}

/** Google only accepts letters, digits, slash and underscore in an action name. */
function recaptcha_action(string $action): string
{
    $action = preg_replace('/[^A-Za-z0-9_\/]/', '_', $action) ?? '';
    return $action !== '' ? $action : 'submit';
}

/**
 * The hidden token field. Emitted inside every public form by
 * form_guard_fields(); returns nothing when reCAPTCHA is off.
 */
function recaptcha_field(string $action): string
{
    if (!recaptcha_enabled()) {
        return '';
    }
    recaptcha_used(true);
    return '<input type="hidden" name="' . RECAPTCHA_FIELD . '" value=""'
        . ' data-recaptcha-action="' . e(recaptcha_action($action)) . '">';
}

/**
 * The disclosure Google's terms require when the floating badge is hidden.
 * Goes next to the privacy line at the foot of each form.
 */
function recaptcha_notice(): string
{
    if (!recaptcha_enabled()) {
        return '';
    }
    return ' Protected by reCAPTCHA — Google\'s '
        . '<a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and '
        . '<a href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms of Service</a> apply.';
}

/**
 * Google's script plus the binding that mints a fresh token on submit.
 * Printed by includes/footer.php after scripts.js, and only on pages that
 * rendered a token field.
 */
function recaptcha_footer(): string
{
    if (!recaptcha_used()) {
        return '';
    }
    $site = recaptcha_config()['site'];
    $key  = json_encode($site, JSON_UNESCAPED_SLASHES);
    return '<script src="' . RECAPTCHA_JS_URL . '?render=' . rawurlencode($site) . '" defer></script>' . "\n"
        . '<script>(function(){var KEY=' . $key . ';' . "\n"
        . <<<'JS'
var fields = document.querySelectorAll('input[data-recaptcha-action]');
if (!fields.length) { return; }

/* grecaptcha.ready() only exists once Google's deferred script has run. */
function whenReady(cb) {
    var tries = 0;
    (function poll() {
        if (window.grecaptcha && window.grecaptcha.execute) { return grecaptcha.ready(cb); }
        if (++tries > 60) { return cb(false); }   /* ~9s, then give up quietly */
        setTimeout(poll, 150);
    })();
}

Array.prototype.forEach.call(fields, function (field) {
    var form = field.form;
    if (!form) { return; }
    form.addEventListener('submit', function (e) {
        /* The Bootstrap validation handler in scripts.js runs first and calls
           preventDefault() on an invalid form — don't spend a token on it. */
        if (e.defaultPrevented || form.dataset.recaptchaSent === '1') { return; }
        e.preventDefault();
        if (form.dataset.recaptchaBusy === '1') { return; }
        form.dataset.recaptchaBusy = '1';

        function send() {
            form.dataset.recaptchaSent = '1';
            form.dataset.recaptchaBusy = '';
            if (form.requestSubmit) { form.requestSubmit(); } else { form.submit(); }
        }
        whenReady(function (ok) {
            /* Submit even when Google never loaded: the server explains what
               happened far better than a button that does nothing. */
            if (ok === false) { return send(); }
            grecaptcha.execute(KEY, { action: field.getAttribute('data-recaptcha-action') })
                .then(function (token) { field.value = token; send(); })
                .catch(send);
        });
    });
});
JS
        . "\n})();</script>";
}

/**
 * Ask Google about one token.
 *
 * @return array{ok:bool, score:float, error:string, soft:bool}
 *         soft = the verdict is ours, not Google's (see the failure policy above).
 */
function recaptcha_verify(string $token, string $action = ''): array
{
    $c = recaptcha_config();
    if ($c['secret'] === '') {
        return ['ok' => true, 'score' => 0.0, 'error' => 'No secret key configured.', 'soft' => true];
    }
    if (!function_exists('curl_init')) {
        return ['ok' => true, 'score' => 0.0, 'error' => 'PHP cURL is not available on this server.', 'soft' => true];
    }
    if ($token === '') {
        return ['ok' => false, 'score' => 0.0, 'error' => 'missing-input-response', 'soft' => false];
    }

    $ch = curl_init(RECAPTCHA_VERIFY_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query(array_filter([
            'secret'   => $c['secret'],
            'response' => $token,
            'remoteip' => client_ip(),
        ])),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => RECAPTCHA_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => RECAPTCHA_CONNECT,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $response = curl_exec($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false || $status < 200 || $status >= 300) {
        $why = $response === false ? $curlErr : ('HTTP ' . $status);
        return ['ok' => true, 'score' => 0.0, 'error' => 'Could not reach Google: ' . $why, 'soft' => true];
    }

    $json = json_decode((string) $response, true);
    if (!is_array($json)) {
        return ['ok' => true, 'score' => 0.0, 'error' => 'Unreadable response from Google.', 'soft' => true];
    }

    $codes = array_map('strval', (array) ($json['error-codes'] ?? []));
    if (empty($json['success'])) {
        // Codes that describe our setup or Google's own machinery rather than
        // the visitor. None of them is a "this is a bot" verdict, so by the
        // policy at the top of this file none of them may cost a lead.
        $soft = (bool) array_intersect($codes, [
            'missing-input-secret',  // the secret never reached Google
            'invalid-input-secret',  // wrong secret (rarely returned these days)
            'bad-request',           // we called siteverify wrongly
            'browser-error',         // Google could not grade the browser at all.
                                     // Automation lands here, but so do hardened
                                     // privacy browsers and blocked third-party
                                     // scripts, and "no signal" is not "guilty" —
                                     // the honeypot and the three-second timer in
                                     // spam_check() still stand in the way.
        ]);
        // "invalid-input-response" covers both a forged token and a genuine one
        // checked against the wrong key. Only a browser can mint something this
        // long, so blame the configuration rather than the visitor.
        if (in_array('invalid-input-response', $codes, true) && mb_strlen($token) >= RECAPTCHA_REAL_TOKEN) {
            $soft = true;
        }
        $hint = $soft && !in_array('browser-error', $codes, true)
            ? ' — check the keys in Admin → Settings → reCAPTCHA'
            : '';
        return [
            'ok'    => $soft,
            'score' => 0.0,
            'error' => ($codes ? implode(', ', $codes) : 'verification failed') . $hint,
            'soft'  => $soft,
        ];
    }

    $score = isset($json['score']) ? (float) $json['score'] : 1.0;
    $got   = (string) ($json['action'] ?? '');
    if ($action !== '' && $got !== '' && $got !== recaptcha_action($action)) {
        return ['ok' => false, 'score' => $score, 'error' => 'action mismatch (' . $got . ')', 'soft' => false];
    }
    if ($score < $c['score']) {
        return ['ok' => false, 'score' => $score, 'error' => 'score ' . $score . ' below ' . $c['score'], 'soft' => false];
    }
    return ['ok' => true, 'score' => $score, 'error' => '', 'soft' => false];
}

/**
 * Gate for a form POST. Returns '' when the visitor may pass, or the message
 * to show them. Called from spam_check() so no form can forget it.
 */
function recaptcha_check(string $action = ''): string
{
    if (!recaptcha_enabled()) {
        return '';
    }
    $token = $_POST[RECAPTCHA_FIELD] ?? '';
    try {
        $result = recaptcha_verify(is_string($token) ? $token : '', $action);
    } catch (\Throwable $ex) {
        error_log('[recaptcha] verification threw: ' . $ex->getMessage());
        return '';
    }
    if ($result['ok']) {
        if ($result['soft']) {
            error_log('[recaptcha] allowed without a verdict: ' . $result['error']);
        }
        return '';
    }
    error_log('[recaptcha] rejected ' . ($action !== '' ? $action : 'submit')
        . ' from ' . client_ip() . ': ' . $result['error']);

    $phone = setting('phone');
    return 'We could not verify that you are human, so the form was not sent.'
        . ' Please reload the page and try again'
        . ($phone !== '' ? ', or call or text ' . $phone . ' and I will take the details myself.' : '.');
}

/**
 * Can this server talk to Google, and are both keys filled in?
 *
 * It cannot check the secret itself: siteverify answers "invalid-input-response"
 * for every test token regardless of the secret — even an empty one — so any
 * "your key is valid" claim here would be a guess. What it does prove is the
 * part that actually breaks on a new host: outbound HTTPS to Google.
 *
 * @return array{ok:bool, error:string, note:string}
 */
function recaptcha_test_connection(): array
{
    $c = recaptcha_config();
    if ($c['site'] === '' || $c['secret'] === '') {
        return ['ok' => false, 'error' => 'Enter both the site key and the secret key first.', 'note' => ''];
    }
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'error' => 'PHP cURL is not available on this server, so tokens can never be verified.', 'note' => ''];
    }

    $ch = curl_init(RECAPTCHA_VERIFY_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query(['secret' => $c['secret'], 'response' => 'connection-test']),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => RECAPTCHA_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => RECAPTCHA_CONNECT,
    ]);
    $response = curl_exec($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['ok' => false, 'error' => 'Could not reach Google: ' . $curlErr, 'note' => ''];
    }
    if (!is_array(json_decode((string) $response, true))) {
        return ['ok' => false, 'error' => 'Google answered HTTP ' . $status . ' with something that is not JSON — a proxy is probably in the way.', 'note' => ''];
    }
    return [
        'ok'    => true,
        'error' => '',
        'note'  => 'Add ' . (string) ($_SERVER['HTTP_HOST'] ?? 'this domain')
            . ' to the key in the reCAPTCHA console, then submit a form on the public site — that is the only end-to-end test.',
    ];
}
