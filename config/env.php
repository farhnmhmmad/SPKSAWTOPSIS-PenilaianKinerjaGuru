<?php
// Very small .env loader
$processEnvironment = getenv();
if (is_array($processEnvironment)) {
    foreach ($processEnvironment as $key => $value) {
        $_ENV[$key] = (string)$value;
    }
}
if (getenv('VERCEL') === '1') {
    ini_set('display_errors', '0');
}

$envPath = __DIR__ . '/../.env';
// Vercel injects environment variables into the function process; never fall
// back to local development credentials when deploying there.
if (!file_exists($envPath) && getenv('VERCEL') !== '1') {
    $envPath = __DIR__ . '/../.env.example';
}
$lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
foreach ($lines as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    if (!str_contains($line, '=')) continue;
    [$k, $v] = array_map('trim', explode('=', $line, 2));
    $processValue = getenv($k);
    if ($processValue !== false) {
        $_ENV[$k] = $processValue;
    } elseif (!array_key_exists($k, $_ENV)) {
        $_ENV[$k] = $v;
    }
}

