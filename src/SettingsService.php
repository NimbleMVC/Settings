<?php

namespace NimblePHP\Settings;

use NimblePHP\Framework\Exception\DatabaseException;
use NimblePHP\Framework\Exception\NimbleException;
use NimblePHP\Framework\Traits\LoadModelTrait;

/**
 * Application-wide settings service. Registered in the service container as "settings"
 * by Module::register(), so settings are reachable everywhere without manually loading
 * the model. The underlying model is resolved lazily on first use.
 */
class SettingsService
{

    use LoadModelTrait;

    /**
     * Lazily resolved settings model.
     * @var ModuleSettingModel|null
     */
    private ?ModuleSettingModel $model = null;

    /**
     * Get a setting value by key.
     * @param string $key
     * @param mixed $default Returned when the key does not exist.
     * @return mixed
     * @throws DatabaseException
     * @throws NimbleException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->model()->get($key, $default);
    }

    /**
     * Set (create or update) a setting value.
     * @param string $key
     * @param mixed $value
     * @return bool
     * @throws DatabaseException
     * @throws NimbleException
     */
    public function set(string $key, mixed $value): bool
    {
        return $this->model()->set($key, $value);
    }

    /**
     * Check whether a setting exists.
     * @param string $key
     * @return bool
     * @throws DatabaseException
     * @throws NimbleException
     */
    public function has(string $key): bool
    {
        return $this->model()->has($key);
    }

    /**
     * Remove a setting.
     * @param string $key
     * @return bool
     * @throws DatabaseException
     * @throws NimbleException
     */
    public function forget(string $key): bool
    {
        return $this->model()->forget($key);
    }

    /**
     * Get all settings as a key => value map.
     * @return array<string, mixed>
     * @throws DatabaseException
     * @throws NimbleException
     */
    public function all(): array
    {
        return $this->model()->all();
    }

    /**
     * Drop the in-memory cache so the next read hits the database again.
     * @return void
     * @throws NimbleException
     */
    public function flushCache(): void
    {
        $this->model()->flushCache();
    }

    /**
     * Lazily resolve the settings model.
     * @return ModuleSettingModel
     * @throws NimbleException
     */
    private function model(): ModuleSettingModel
    {
        if ($this->model === null) {
            $this->model = $this->loadModel(ModuleSettingModel::class);
        }

        return $this->model;
    }

}
