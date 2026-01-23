<?php

namespace App\Models;

trait LastError
{
    protected static ?string $lastError = null;

    public static function setLastError(mixed $error): void
    {
        if ($error instanceof \BackedEnum) {
            self::$lastError = (string) $error->value;
        } elseif ($error instanceof \UnitEnum) {
            self::$lastError = $error->name;
        } else {
            if (is_scalar($error) || $error === null) {
                self::$lastError = (string) $error;
            } else {
                self::$lastError = json_encode($error, JSON_THROW_ON_ERROR);
            }
        }
    }

    public static function getLastError(): ?string
    {
        return self::$lastError;
    }
}
