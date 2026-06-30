<?php

namespace NimblePHP\Settings;

use NimblePHP\Framework\Abstracts\AbstractModel;
use NimblePHP\Framework\Exception\DatabaseException;

/**
 * Runtime key-value settings stored in the `module_setting` table.
 * Values are typed (see SettingType) and cached per request.
 */
class ModuleSettingModel extends AbstractModel
{

    /**
     * Per-request cache of decoded values, keyed by setting name.
     * @var array<string, mixed>
     */
    private static array $cache = [];

    /**
     * Whether the cache has been fully loaded from the database.
     * @var bool
     */
    private static bool $loaded = false;

    /**
     * Get a setting value by key.
     * @param string $key
     * @param mixed $default Returned when the key does not exist.
     * @return mixed
     * @throws DatabaseException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->loadCache();

        return array_key_exists($key, self::$cache) ? self::$cache[$key] : $default;
    }

    /**
     * Set (create or update) a setting value. The stored type is derived from the PHP value.
     * @param string $key
     * @param mixed $value
     * @return bool
     * @throws DatabaseException
     */
    public function set(string $key, mixed $value): bool
    {
        $type = SettingType::detect($value);
        $encoded = $type->encode($value);
        $existing = $this->read(['module_setting.name' => $key], ['module_setting.id']);

        if ($existing) {
            $saved = $this->setId((int)$existing['module_setting']['id'])
                ->update(['value' => $encoded, 'type' => $type->value]);
        } else {
            $saved = $this->create(['name' => $key, 'value' => $encoded, 'type' => $type->value]);
        }

        if ($saved) {
            self::$cache[$key] = $type->decode($encoded);
        }

        return $saved;
    }

    /**
     * Check whether a setting exists.
     * @param string $key
     * @return bool
     * @throws DatabaseException
     */
    public function has(string $key): bool
    {
        $this->loadCache();

        return array_key_exists($key, self::$cache);
    }

    /**
     * Remove a setting.
     * @param string $key
     * @return bool
     * @throws DatabaseException
     */
    public function forget(string $key): bool
    {
        $existing = $this->read(['module_setting.name' => $key], ['module_setting.id']);

        if (!$existing) {
            return false;
        }

        $deleted = $this->setId((int)$existing['module_setting']['id'])->delete();

        if ($deleted) {
            unset(self::$cache[$key]);
        }

        return $deleted;
    }

    /**
     * Get all settings as a key => value map.
     * @return array<string, mixed>
     * @throws DatabaseException
     */
    public function all(): array
    {
        $this->loadCache();

        return self::$cache;
    }

    /**
     * Drop the in-memory cache so the next read hits the database again.
     * @return void
     */
    public function flushCache(): void
    {
        self::$cache = [];
        self::$loaded = false;
    }

    /**
     * Load and decode all settings into the per-request cache once.
     * @return void
     * @throws DatabaseException
     */
    private function loadCache(): void
    {
        if (self::$loaded) {
            return;
        }

        $rows = $this->readAll(null, ['module_setting.name', 'module_setting.value', 'module_setting.type']);

        foreach ($rows as $row) {
            $record = $row['module_setting'];
            $type = SettingType::getByKey($record['type']);
            self::$cache[$record['name']] = $type->decode((string)($record['value'] ?? ''));
        }

        self::$loaded = true;
    }

}
