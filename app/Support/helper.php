<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';

function e(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function sluggify(string $text): string {
    $text = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function redirectAdmin(string $url): void {
    header('Location: /admin/' . $url);
}

function redirectPublic(string $url): void {
    header('Location: /' . $url);
}

function jsonResponse(int $statusCode = 200, string $message = '', array $data = []): never {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    exit;
}
