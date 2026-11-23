<?php
require_once __DIR__ . '/ApiException.php';

class ApiResponse
{
    public static function run(callable $action, int $successCode = 200): void
    {
        try {
            Flight::json($action(), $successCode);
        } catch (ApiException $e) {
            Flight::json(['error' => $e->getMessage()], $e->status());
        } catch (PDOException $e) {
            self::logError('Database error', $e);
            $status = $e->getCode() === '23000' ? 409 : 500;
            Flight::json(['error' => $status === 409
                ? 'This record conflicts with an existing record or relationship.'
                : 'A database error occurred.'], $status);
        } catch (Throwable $e) {
            self::logError('Application error', $e);
            Flight::json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    public static function body(): array
    {
        $raw = Flight::request()->getBody();
        if (trim($raw) === '') {
            throw new ApiException('A JSON request body is required.');
        }
        try {
            $object = json_decode($raw, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ApiException('Invalid JSON request body.');
        }
        if (!$object instanceof stdClass) {
            throw new ApiException('The request body must be a JSON object.');
        }
        return (array) $object;
    }

    public static function positiveId(string $raw): int
    {
        if (!ctype_digit($raw) || (int) $raw < 1) {
            throw new ApiException('Invalid record ID.');
        }
        return (int) $raw;
    }

    public static function logError(string $context, Throwable $e): void
    {
        $directory = __DIR__ . '/../logs';
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }
        // Do not log passwords, request bodies or external API keys.
        @error_log(sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), $context, get_class($e)),
            3, $directory . '/error.log');
    }
}
