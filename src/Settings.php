<?php

namespace NimblePHP\Settings;

use NimblePHP\Framework\Exception\DatabaseException;
use NimblePHP\Framework\Exception\NimbleException;
use NimblePHP\Framework\Kernel;

/**
 * Static facade over the settings service registered in the container as "settings".
 * Self-heals by creating the service on demand when it has not been registered yet
 * (e.g. in CLI or test contexts where Module::register() did not run).
 */
class Settings
{

    /**
     * Service container id under which the settings service is registered.
     * @var string
     */
    public const string SERVICE_ID = 'settings';

    /**
     * Get a setting value by key.
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @throws DatabaseException
     * @throws NimbleException
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::service()->get($key, $default);
    }

    /**
     * Set (create or update) a setting value.
     * @param string $key
     * @param mixed $value
     * @return bool
     * @throws DatabaseException
     * @throws NimbleException
     */
    public static function set(string $key, mixed $value): bool
    {
        return self::service()->set($key, $value);
    }

    /**
     * Check whether a setting exists.
     * @param string $key
     * @return bool
     * @throws DatabaseException
     * @throws NimbleException
     */
    public static function has(string $key): bool
    {
        return self::service()->has($key);
    }

    /**
     * Remove a setting.
     * @param string $key
     * @return bool
     * @throws DatabaseException
     * @throws NimbleException
     */
    public static function forget(string $key): bool
    {
        return self::service()->forget($key);
    }

    /**
     * Get all settings as a key => value map.
     * @return array<string, mixed>
     * @throws DatabaseException
     * @throws NimbleException
     */
    public static function all(): array
    {
        return self::service()->all();
    }

    /**
     * Drop the in-memory cache so the next read hits the database again.
     * @return void
     * @throws NimbleException
     */
    public static function flushCache(): void
    {
        self::service()->flushCache();
    }

    /**
     * Resolve the settings service from the container, registering it on demand.
     * @return SettingsService
     */
    private static function service(): SettingsService
    {
        if (!Kernel::$serviceContainer->has(self::SERVICE_ID)) {
            Kernel::$serviceContainer->set(self::SERVICE_ID, new SettingsService());
        }

        return Kernel::$serviceContainer->get(self::SERVICE_ID);
    }

}
