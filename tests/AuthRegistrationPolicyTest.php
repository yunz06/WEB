<?php

declare(strict_types=1);

if (!extension_loaded('mbstring')) { echo "SKIP: mbstring is required for registration policy tests.\n"; exit(0); }

require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';

$policy = new ReflectionMethod(AuthController::class, 'passwordIsAcceptable');
$policy->setAccessible(true);
$accepts = static fn(string $password, string $confirmation): bool => (bool)$policy->invoke(null, $password, $confirmation);
$assert = static function (bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
};

$validPassword = 'StrongPassPhrase2026!';
$assert($accepts($validPassword, $validPassword), 'A valid long password with three character classes was rejected.');
$assert(!$accepts('short1!', 'short1!'), 'A short password was accepted.');
$assert(!$accepts('onlylowercasepassword', 'onlylowercasepassword'), 'A password with one character class was accepted.');
$assert(!$accepts($validPassword, $validPassword . 'x'), 'A mismatched confirmation was accepted.');
$assert(!$accepts(str_repeat('Aa1!', 19), str_repeat('Aa1!', 19)), 'A password beyond bcrypt’s 72-byte limit was accepted.');
$assert(!$accepts("Invalid\xffPassword1!", "Invalid\xffPassword1!"), 'Invalid UTF-8 password bytes were accepted.');

echo "PASS: registration password strength, confirmation, UTF-8, and bcrypt byte limit.\n";
