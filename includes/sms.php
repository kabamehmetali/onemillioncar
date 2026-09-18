<?php
/**
 * Twilio SMS alerts for new leads.
 *
 * Credentials live in the settings table (Admin → SMS alerts), never in code,
 * so they can be rotated without a deployment. Every send is best-effort: a
 * Twilio outage, a wrong token or a missing cURL extension must never stop a
 * lead from being stored or block the visitor's form submission.
 */
declare(strict_types=1);

const TWILIO_API_BASE = 'https://api.twilio.com/2010-04-01';
const SMS_TIMEOUT     = 8; // seconds for the whole request
const SMS_CONNECT     = 4; // seconds to establish the connection
const SMS_MAX_SEGMENTS = 2; // hard cap on what one lead may cost

/**
 * The GSM-7 alphabet. One character outside it — a "·", a curly quote, an
 * accent Twilio cannot map, an emoji — switches the whole message to UCS-2,
 * where a segment holds 67 characters instead of 153. Same text, twice the
 * price, so the budget below is derived from the message itself.
 */
const GSM7_PATTERN = '/[^\n\r A-Za-z0-9@£$¥èéùìòÇØøÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ!"#¤%&\'()*+,\-.\/:;<=>?¡ÄÖÑÜ§¿äöñüà]/u';

function sms_is_gsm7(string $text): bool
{
    return !preg_match(GSM7_PATTERN, $text);
}

/** Characters that fit in SMS_MAX_SEGMENTS segments for this text's encoding. */
function sms_budget(string $text): int
{
    return sms_is_gsm7($text) ? SMS_MAX_SEGMENTS * 153 : SMS_MAX_SEGMENTS * 67;
}

/** Swap the typography this file generates for GSM-7 equivalents. Text typed by
 *  the visitor is left alone — a mangled name is worse than a second segment. */
function sms_plain(string $text): string
{
    return strtr($text, [
        '·' => '-', '•' => '-', '–' => '-', '—' => '-', '…' => '...',
        '“' => '"', '”' => '"', '„' => '"', '‘' => "'", '’' => "'", "\xc2\xa0" => ' ',
    ]);
}

/** Digits-only input becomes E.164. Ten digits are assumed to be North American. */
function sms_e164(string $number): string
{
    $number = trim($number);
    if ($number === '') {
        return '';
    }
    $digits = preg_replace('/\D/', '', $number) ?? '';
    if ($digits === '') {
        return '';
    }
    if (str_starts_with($number, '+')) {
        return '+' . $digits;
    }
    if (strlen($digits) === 10) {
        return '+1' . $digits;
    }
    if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
        return '+' . $digits;
    }
    return '+' . $digits;
}

/** @return array{sid:string, token:string, from:string, to:string} */
function sms_config(): array
{
    return [
        'sid'   => trim(setting('twilio_account_sid')),
        'token' => trim(setting('twilio_auth_token')),
        'from'  => sms_e164(setting('twilio_from_number')),
        'to'    => sms_e164(setting('sms_notify_number')),
    ];
}

/** True when alerts are switched on and every credential is present. */
function sms_enabled(): bool
{
    if (setting('sms_enabled') !== '1') {
        return false;
    }
    $c = sms_config();
    return $c['sid'] !== '' && $c['token'] !== '' && $c['from'] !== '' && $c['to'] !== '';
}

/**
 * Send one message through Twilio's REST API.
 *
 * @return array{ok:bool, sid:string, error:string}
 */
function sms_send(string $to, string $body, ?array $config = null): array
{
    $c  = $config ?? sms_config();
    $to = sms_e164($to);

    if ($c['sid'] === '' || $c['token'] === '') {
        return ['ok' => false, 'sid' => '', 'error' => 'Twilio Account SID and Auth Token are required.'];
    }
    if (!str_starts_with($c['sid'], 'AC')) {
        return ['ok' => false, 'sid' => '', 'error' => 'The Account SID must start with "AC".'];
    }
    if ($c['from'] === '') {
        return ['ok' => false, 'sid' => '', 'error' => 'A Twilio phone number to send from is required.'];
    }
    if ($to === '') {
        return ['ok' => false, 'sid' => '', 'error' => 'No destination number.'];
    }
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'sid' => '', 'error' => 'PHP cURL is not available on this server.'];
    }

    $body = sms_plain(trim($body));
    $body = mb_substr($body, 0, sms_budget($body));
    $ch   = curl_init(TWILIO_API_BASE . '/Accounts/' . rawurlencode($c['sid']) . '/Messages.json');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query(['To' => $to, 'From' => $c['from'], 'Body' => $body]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
        CURLOPT_USERPWD        => $c['sid'] . ':' . $c['token'],
        CURLOPT_TIMEOUT        => SMS_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => SMS_CONNECT,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $response = curl_exec($ch);
    $status   = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        error_log('[sms] Twilio request failed: ' . $curlErr);
        return ['ok' => false, 'sid' => '', 'error' => 'Could not reach Twilio: ' . $curlErr];
    }

    $json = json_decode((string) $response, true);
    if ($status >= 200 && $status < 300 && is_array($json)) {
        return ['ok' => true, 'sid' => (string) ($json['sid'] ?? ''), 'error' => ''];
    }

    // Twilio returns a human-readable "message" plus a documented error code.
    $error = is_array($json) ? trim((string) ($json['message'] ?? '')) : '';
    if ($error === '') {
        $error = 'Twilio returned HTTP ' . $status . '.';
    } elseif (is_array($json) && !empty($json['code'])) {
        $error .= ' (Twilio error ' . $json['code'] . ')';
    }
    error_log('[sms] Twilio HTTP ' . $status . ': ' . $error);
    return ['ok' => false, 'sid' => '', 'error' => $error];
}

/** Human label for each lead type, reused by the email and SMS notifications. */
function lead_type_label(string $type): string
{
    return [
        'contact'    => 'Contact message',
        'inquiry'    => 'Vehicle inquiry',
        'test_drive' => 'Test drive request',
        'trade_in'   => 'Trade-in appraisal',
        'financing'  => 'Financing application',
    ][$type] ?? 'Lead';
}

/** The text of the alert. Kept short on purpose — it is read on a lock screen. */
function lead_sms_body(string $type, array $data): string
{
    $lines   = [];
    $lines[] = strtoupper(lead_type_label($type)) . ' - ' . site_name();
    $lines[] = trim((string) ($data['name'] ?? 'Someone'));

    $contact = array_filter([trim((string) ($data['phone'] ?? '')), trim((string) ($data['email'] ?? ''))]);
    if ($contact) {
        $lines[] = implode(' / ', $contact);
    }

    $details = $data['details'] ?? [];
    if (!empty($details['vehicle'])) {
        $lines[] = (string) $details['vehicle'];
    }
    foreach (['subject', 'preferred_date', 'budget', 'intent'] as $key) {
        if (!empty($details[$key])) {
            $lines[] = ucwords(str_replace('_', ' ', $key)) . ': ' . $details[$key];
        }
    }

    // The link to the lead is the actionable part, so it is reserved up front and
    // everything else is trimmed to whatever room is left.
    $link    = absolute_url('admin/leads.php');
    $text    = sms_plain(implode("\n", $lines));
    $message = preg_replace('/\s+/u', ' ', trim((string) ($data['message'] ?? ''))) ?? '';

    if ($message !== '') {
        $remaining = sms_budget($text . $message . $link) - mb_strlen($text) - mb_strlen($link) - 1;
        if ($remaining > 24) {
            // Room for the surrounding quotes, a trailing "..." and the newline.
            $limit = min(120, $remaining - 6);
            $text .= "\n\"" . mb_substr($message, 0, $limit) . (mb_strlen($message) > $limit ? '...' : '') . '"';
        }
    }

    // Re-derive the budget from what the alert actually says: dropping a message
    // that held the only non-GSM-7 character puts the wider budget back in play.
    // Hard-trim the body, never the link.
    $budget = sms_budget($text . $link);
    return mb_substr($text, 0, max(0, $budget - mb_strlen($link) - 1)) . "\n" . $link;
}

/** Best-effort SMS alert for a new lead; failures never reach the visitor. */
function lead_sms_notify(string $type, array $data): void
{
    if (!sms_enabled()) {
        return;
    }
    try {
        $c      = sms_config();
        $result = sms_send($c['to'], lead_sms_body($type, $data), $c);
        if (!$result['ok']) {
            error_log('[sms] Lead alert not sent: ' . $result['error']);
        }
    } catch (\Throwable $ex) {
        error_log('[sms] Lead alert threw: ' . $ex->getMessage());
    }
}
