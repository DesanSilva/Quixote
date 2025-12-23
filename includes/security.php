<?php
if (!defined('APP_ENTRY')) die('Direct access not permitted');

// Validation patterns
define('PATTERN_REGISTRATION_NUMBER', '/^\d{1,4}\/[A-Z]{1,2}\/\d{1,3}$/');
define('PATTERN_DATE', '/^\d{4}-\d{2}-\d{2}$/');

// Generate CSRF token for form protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function regenerateCSRFToken() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8') . '">';
}

// Sanitize output to prevent XSS attacks
function sanitizeOutput($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function validateInt($value) {
    if ($value === null || $value === '') return false;
    $filtered = filter_var($value, FILTER_VALIDATE_INT);
    return $filtered !== false ? $filtered : false;
}

function validateString($value, $maxLength = 255) {
    if ($value === null) return false;
    $value = trim((string)$value);
    if ($value === '') return '';
    if (strlen($value) > $maxLength) return false;

    // FILTER_SANITIZE_STRING is deprecated/removed in newer PHP versions.
    // The prior behavior effectively stripped tags while keeping quotes.
    $value = strip_tags($value);
    // Remove ASCII control characters (except common whitespace already trimmed)
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
    return $value;
}

function isPostRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function validateRegistrationNumber($value) {
    return preg_match(PATTERN_REGISTRATION_NUMBER, $value);
}

function validateDate($value) {
    return preg_match(PATTERN_DATE, $value);
}

function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
?>
