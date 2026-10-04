<?php
declare(strict_types=1);

namespace Core;

final class Token {
    public const string CSRF_KEY = 'csrf_token';

    public static function generate(): string {
        if (!isset($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::CSRF_KEY];
    }

    public static function verify(?string $token): bool {
        if (!isset($_SESSION[self::CSRF_KEY]) || $token === null) {
            return false;
        }
        
        return hash_equals($_SESSION[self::CSRF_KEY], $token);
    }
}
