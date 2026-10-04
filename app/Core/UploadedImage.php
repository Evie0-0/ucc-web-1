<?php
declare(strict_types=1);

namespace Core;

use Core\Message;
use finfo;
use RuntimeException;

final class UploadedImage {
    private const MAX_SIZE = 10 * 1024 * 1024; // 10 MB
    private const MAX_WIDTH = 5000;
    private const MAX_HEIGHT = 5000;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    // Verify uploaded image
    public function verify(array $file): string|false {
        if (!isset($file['error'], $file['tmp_name'])) {
            throw new RuntimeException('The uploaded file data is missing.');
        }

        $error = $file['error'];

        if ($error !== UPLOAD_ERR_OK) {
            return $this->handleUploadError($error);
        }

        $tempFile = $file['tmp_name'];

        if (!is_uploaded_file($tempFile)) {
            throw new RuntimeException('The uploaded file could not be verified as a valid HTTP upload.');
        }

        $size = filesize($tempFile);

        if ($size === false) {
            throw new RuntimeException('Could not determine the uploaded image size.');
        }

        if ($size > self::MAX_SIZE) {
            Message::error('The image is too large. The maximum size is 10 MB.');
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tempFile);

        if ($mime === false) {
            throw new RuntimeException('Could not determine the uploaded image type.');
        }

        if (!in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            Message::error('The image format is not supported. Please use JPEG, PNG, or WebP.');
            return false;
        }

        $imageInfo = getimagesize($tempFile);

        if ($imageInfo === false) {
            Message::error('The uploaded file is not a valid image.');
            return false;
        }

        $imageMime = $imageInfo['mime'] ?? '';

        if ($imageMime !== $mime) {
            Message::error('The uploaded image could not be verified.');
            return false;
        }

        $width = $imageInfo[0] ?? 0;
        $height = $imageInfo[1] ?? 0;

        if ($width > self::MAX_WIDTH || $height > self::MAX_HEIGHT) {
            Message::error('The image dimensions are too large. The maximum is 5000 × 5000 pixels.');
            return false;
        }

        return $tempFile;
    }

    // Move temporary image to storage
    public function store(string $tempFile, string $directory): string {
        if (!is_uploaded_file($tempFile)) {
            throw new RuntimeException('The validated uploaded image is no longer a valid uploaded file.');
        }

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new RuntimeException('Failed to create the image storage directory.');
            }
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tempFile);

        if ($mime === false) {
            throw new RuntimeException('Could not determine the validated image type.');
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new RuntimeException('The validated image has an unsupported MIME type.'),
        };

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination =rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tempFile, $destination)) {
            throw new RuntimeException('Failed to move the uploaded image into permanent storage.');
        }

        return $filename;
    }

    private function handleUploadError(int $error): false {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => $this->fail('The image is too large. The maximum size is 10 MB.'),
            UPLOAD_ERR_PARTIAL => $this->fail('The image upload was incomplete. Please try again.'),
            UPLOAD_ERR_NO_FILE => $this->fail('Please select an image to upload.'),
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => throw new RuntimeException('The server could not process the uploaded image.'),
            default => throw new RuntimeException('The upload failed because of an unknown server error.'),
        };
    }

    private function fail(string $message): false {
        Message::error($message);
        return false;
    }
}
