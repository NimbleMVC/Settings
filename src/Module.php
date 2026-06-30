<?php

namespace NimblePHP\Settings;

use krzysztofzylka\DatabaseManager\Exception\ConnectException;
use krzysztofzylka\DatabaseManager\Exception\DatabaseManagerException;
use NimblePHP\Framework\Exception\DatabaseException;
use NimblePHP\Framework\Exception\NimbleException;
use NimblePHP\Framework\Kernel;
use NimblePHP\Framework\Module\Interfaces\ModuleInterface;
use NimblePHP\Framework\Module\Interfaces\ModuleUpdateInterface;
use NimblePHP\Migrations\Exceptions\MigrationException;
use NimblePHP\Migrations\Migrations;
use Throwable;

class Module implements ModuleInterface, ModuleUpdateInterface
{

    public function getName(): string
    {
        return 'Settings for NimblePHP';
    }

    public function register(): void
    {
        Kernel::$serviceContainer->set(Settings::SERVICE_ID, new SettingsService());
    }

    /**
     * Execute on application update - runs pending migrations
     * @return void
     * @throws DatabaseException
     * @throws NimbleException
     * @throws MigrationException
     * @throws Throwable
     * @throws ConnectException
     * @throws DatabaseManagerException
     */
    public function onUpdate(): void
    {
        $migration = new Migrations(Kernel::$projectPath, __DIR__ . '/Migrations', 'module_setting');
        $migration->runMigrations();
    }

}
