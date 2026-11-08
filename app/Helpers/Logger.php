<?php
// app/Helpers/Logger.php - Quản lý ghi log ứng dụng theo môi trường

class Logger
{
    private static ?string $logFile = null;

    public static function getLogFile(): string
    {
        if (self::$logFile === null) {
            $logDir = defined('ROOT') ? ROOT . '/storage/logs' : dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            self::$logFile = $logDir . '/app.log';
        }
        return self::$logFile;
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $time = date('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        $logLine = "[{$time}] [{$levelUpper}]: {$message}{$contextStr}" . PHP_EOL;

        @file_put_contents(self::getLogFile(), $logLine, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }
}
