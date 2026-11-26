<?php
declare(strict_types=1);

require __DIR__ . '/database.php';
date_default_timezone_set('UTC');

const MAX_POINTS = 2147483647;

ini_set('session.use_strict_mode', '1');
session_name('gapcorn_session');
session_set_cookie_params([
    'path' => rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();
header('Cache-Control: no-store');

function adminCredentials(): array
{
    static $credentials;
    if ($credentials !== null) {
        return $credentials;
    }

    $localConfig = dirname(__DIR__) . '/config.local.php';
    $config = is_file($localConfig) ? require $localConfig : [];
    $email = getenv('GAPCORN_ADMIN_EMAIL');
    $passwordHash = getenv('GAPCORN_ADMIN_HASH');

    return $credentials = [
        strtolower((string) ($email !== false ? $email : ($config['admin_email'] ?? ''))),
        (string) ($passwordHash !== false ? $passwordHash : ($config['admin_password_hash'] ?? '')),
    ];
}

function e(string|int $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function post(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}

function redirect(string $page): never
{
    header('Location: ' . $page, true, 303);
    exit;
}

function csrfField(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function requireCsrf(): void
{
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], post('csrf'))) {
        http_response_code(403);
        exit('Your form has expired. Return to the previous page and reload it.');
    }
}

function signIn(string $role, array $details = []): void
{
    session_regenerate_id(true);
    $_SESSION = ['role' => $role] + $details;
}

function signOut(string $page): never
{
    $_SESSION = [];
    session_regenerate_id(true);
    redirect($page);
}

function displayTime(string $timestamp, string $format = 'd M Y, g:i:s A'): string
{
    return (new DateTimeImmutable($timestamp))
        ->setTimezone(new DateTimeZone('Asia/Kolkata'))
        ->format($format);
}

function utcTimestamp(string $timestamp): string
{
    return (new DateTimeImmutable($timestamp))->format('Y-m-d\TH:i:s.v\Z');
}
