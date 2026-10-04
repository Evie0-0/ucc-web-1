<?php
declare(strict_types=1);

namespace Models;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../Support/helper.php';

use Core\RemoteImage;
use Core\UploadedImage;
use Core\HtmlSanitizer;
use Core\Message;
use PDO;
use Exception;
use RuntimeException;
use PDOException;

final class ProgramManager {
    private PDO $conn = null;

    public ?int $id = 0;

}
