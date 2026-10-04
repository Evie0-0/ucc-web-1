<?php
declare(strict_types=1);

namespace Core;

final class Message {
    public const SUCCESS = 'success_msg';
    public const ERROR = 'error_msg';
    public const WARNING = 'warning_msg';

    public static function success(string $msg): void {
        $_SESSION[self::SUCCESS] = $msg;
    }

    public static function error(string $msg): void {
        $_SESSION[self::ERROR] = $msg;
    }
    
    public static function warning(string $msg): void {
        $_SESSION[self::WARNING] = $msg;
    }
}
