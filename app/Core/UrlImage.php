<?php
declare(strict_types=1);

namespace Core;

require_once __DIR__ . '/../../bootstrap.php';

use Core\Message;
use RuntimeException;
use Throwable;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;

final class UrlImage {
    private const MAX_SIZE = 10 * 1024 * 1024; // 10 MB
    private const MAX_WIDTH = 5000;
    private const MAX_HEIGHT = 5000;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    private NoPrivateNetworkHttpClient $client;

    public function __construct()
    {
        $httpClient = HttpClient::create([
            'timeout' => 10,
            'max_duration' => 15,
            'max_redirects' => 3,
        ]);

        $this->client = new NoPrivateNetworkHttpClient($httpClient);
    }

    // Verify image
    public function verify(string $url): string|false
    {
        $tempFile = null;
        $file = null;
        $response = null;

        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'image/jpeg,image/png,image/webp',
                ],
                'buffer' => false,
            ]);

            if ($response->getStatusCode() !== 200) {
                $response->cancel();
                Message::error('The image could not be downloaded. Please check the URL and try again.');

                return false;
            }

            $headers = $response->getHeaders(false);
            $contentLength = $headers['content-length'][0] ?? null;

            if ($contentLength !== null && (int) $contentLength > self::MAX_SIZE) {
                $response->cancel();
                Message::error('The image is too large. The maximum size is 10 MB.');

                return false;
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'image_');

            if ($tempFile === false) {
                throw new RuntimeException('Failed to create a temporary file for the remote image.');
            }

            $file = fopen($tempFile, 'wb');

            if ($file === false) {
                throw new RuntimeException('Failed to open the temporary image file for writing.');
            }

            $downloadedBytes = 0;

            foreach ($this->client->stream($response) as $chunk) {
                if ($chunk->isTimeout()) {
                    Message::error('The image download timed out. Please try again.');
                    return $this->failDownload($tempFile, $file, $response);
                }

                $data = $chunk->getContent();
                $downloadedBytes += strlen($data);

                if ($downloadedBytes > self::MAX_SIZE) {
                    Message::error('The image is too large. The maximum size is 10 MB.');

                    return $this->failDownload($tempFile, $file, $response);
                }

                if ($data !== '') {
                    $this->writeAll($file, $data);
                }
            }

            fclose($file);
            $file = null;

            $imageInfo = getimagesize($tempFile);

            if ($imageInfo === false) {
                Message::error('The downloaded file is not a valid image.');
                $this->cleanupTempFile($tempFile);

                return false;
            }

            $mime = $imageInfo['mime'] ?? '';

            if (!in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
                Message::error('The image format is not supported. Use JPEG, PNG, or WebP.');
                $this->cleanupTempFile($tempFile);

                return false;
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];

            if ($width > self::MAX_WIDTH || $height > self::MAX_HEIGHT) {
                Message::error('The image dimensions are too large. The maximum is 5000 × 5000 pixels.');
                $this->cleanupTempFile($tempFile);

                return false;
            }

            return $tempFile;
        } catch (Throwable $e) {
            if (is_resource($file)) {
                fclose($file);
            }

            if ($response !== null) {
                $response->cancel();
            }

            if ($tempFile !== null) {
                $this->cleanupTempFile($tempFile);
            }

            throw $e;
        }
    }

    // Store temp image permanently
    public function store(string $tempFile, string $directory): string
    {
        if (!is_file($tempFile)) {
            throw new RuntimeException('The validated temporary image file is missing.');
        }

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new RuntimeException('Failed to create the image storage directory.');
            }
        }

        $imageInfo = getimagesize($tempFile);

        if ($imageInfo === false) {
            throw new RuntimeException('The validated temporary file is not a valid image.');
        }

        $mime = $imageInfo['mime'] ?? '';

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new RuntimeException('The validated image has an unsupported MIME type.'),
        };

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $destination = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (!rename($tempFile, $destination)) {
            throw new RuntimeException('Failed to move the validated image into permanent storage.');
        }

        return $filename;
    }

    private function writeAll($file, string $data): void {
        $length = strlen($data);
        $written = 0;

        while ($written < $length) {
            $result = fwrite($file, substr($data, $written));

            if ($result === false) {
                throw new RuntimeException('Failed to write image data to the temporary file.');
            }

            $written += $result;
        }
    }

    private function failDownload(string $tempFile, $file, $response): false {
        if (is_resource($file)) {
            fclose($file);
        }

        $response->cancel();
        $this->cleanupTempFile($tempFile);

        return false;
    }

    private function cleanupTempFile(string $tempFile): void {
        if (is_file($tempFile)) {
            unlink($tempFile);
        }
    }
}
