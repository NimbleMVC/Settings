<?php

namespace NimblePHP\Settings;

/**
 * Value type of a stored setting. Handles encoding to / decoding from the
 * string representation kept in the database.
 */
enum SettingType: string
{

    case string = 'string';

    case integer = 'integer';

    case float = 'float';

    case boolean = 'boolean';

    case json = 'json';

    /**
     * Detect the matching type for a PHP value.
     * @param mixed $value
     * @return self
     */
    public static function detect(mixed $value): self
    {
        return match (true) {
            is_bool($value) => self::boolean,
            is_int($value) => self::integer,
            is_float($value) => self::float,
            is_array($value), is_null($value) => self::json,
            default => self::string,
        };
    }

    /**
     * Resolve a type by its stored key, falling back to string for unknown values.
     * @param string|null $key
     * @return self
     */
    public static function getByKey(?string $key): self
    {
        return self::tryFrom((string)$key) ?? self::string;
    }

    /**
     * Encode a value to its string representation for storage.
     * @param mixed $value
     * @return string
     */
    public function encode(mixed $value): string
    {
        return match ($this) {
            self::boolean => $value ? '1' : '0',
            self::json => (string)json_encode($value),
            default => (string)$value,
        };
    }

    /**
     * Decode a stored string back to its typed value.
     * @param string $raw
     * @return mixed
     */
    public function decode(string $raw): mixed
    {
        return match ($this) {
            self::integer => (int)$raw,
            self::float => (float)$raw,
            self::boolean => $raw === '1',
            self::json => json_decode($raw, true),
            self::string => $raw,
        };
    }

}
