<?php

declare(strict_types=1);

namespace App\Factory;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;

class LoggerFactory
{
    public static function create(string $channel = 'app'): Logger
    {
        $logDir = __DIR__ . '/../../tmp/log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $filename = $logDir . '/' . date('Y-m-d') . '.log';

        $logger = new Logger($channel);

        $handler = new StreamHandler($filename, Level::Debug);

        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context%\n",
            'Y-m-d H:i:s'
        );
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);

        return $logger;
    }
}