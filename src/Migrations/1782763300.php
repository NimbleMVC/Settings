<?php

use krzysztofzylka\DatabaseManager\Columns\DateCreatedColumn;
use krzysztofzylka\DatabaseManager\Columns\DateModifyColumn;
use krzysztofzylka\DatabaseManager\Columns\IdColumn;
use krzysztofzylka\DatabaseManager\Columns\TextColumn;
use krzysztofzylka\DatabaseManager\Columns\VarcharColumn;
use krzysztofzylka\DatabaseManager\CreateTable;
use NimblePHP\Migrations\AbstractMigration;

return new class extends AbstractMigration {

    public function run(): void
    {
        $table = new CreateTable('module_setting');
        $table->addColumn(new IdColumn);
        $table->addColumn((new VarcharColumn('name', 191))->setNull(false));
        $table->addColumn(new TextColumn('value'));
        $table->addColumn((new VarcharColumn('type', 20, false))->setDefault('string'));
        $table->addColumn(new DateCreatedColumn);
        $table->addColumn(new DateModifyColumn);
        $table->execute();

        $this->query("
            ALTER TABLE `module_setting`
            ADD UNIQUE KEY `module_setting_name` (`name`)
        ");
    }

};
