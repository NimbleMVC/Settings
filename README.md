# nimblephp/settings

Runtime key-value settings for NimblePHP. Stores typed values in the database
(table `module_setting`) and caches them per request.

## Contents

- `Module` — module registration and migrations (`onUpdate`)
- `ModuleSettingModel` — settings manager (get, set, has, forget, all)
- `SettingType` — value type enum (`string`, `integer`, `float`, `boolean`, `json`) with encode/decode
- `SettingsException` — module exception
- `src/Migrations/` — migration creating the `module_setting` table

## Installation

```bash
composer require nimblephp/settings
```

## Migration

Migrations run automatically on application update (`Module::onUpdate()`,
group `module_setting`). They can also be run manually:

```bash
php vendor/bin/nimble migration:run --dir=vendor/nimblephp/settings/src/Migrations
```

## Usage

```php
use NimblePHP\Settings\ModuleSettingModel;

$settings = $this->loadModel(ModuleSettingModel::class);

// store (type is derived from the value)
$settings->set('site.name', 'My App');     // string
$settings->set('mail.enabled', true);       // boolean
$settings->set('uploads.maxMb', 25);        // integer
$settings->set('feature.flags', ['beta' => true]); // json

// read (typed value back, with default)
$name = $settings->get('site.name', 'Default');
$enabled = $settings->get('mail.enabled', false);

// check / remove / list
$settings->has('site.name');
$settings->forget('site.name');
$all = $settings->all(); // ['key' => value, ...]
```

Values are loaded once per request and kept in an in-memory cache. `set()` and
`forget()` keep the cache in sync; call `flushCache()` to force a reload.

## Service / facade (no `loadModel` needed)

`Module::register()` registers a `SettingsService` in the container under the id
`settings`, so settings are reachable application-wide:

```php
use NimblePHP\Framework\Kernel;

$settings = Kernel::$serviceContainer->get('settings'); // SettingsService
$settings->set('site.name', 'My App');
```

Or via the static facade (self-registers the service on demand):

```php
use NimblePHP\Settings\Settings;

Settings::set('mail.enabled', true);
$enabled = Settings::get('mail.enabled', false);
```

The service, the facade and the model all share the same per-request cache.
