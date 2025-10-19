<?php

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPDatabase\Connection as DB;
use BitApps\Social\Deps\BitApps\WPKit\Migration\Migration;

if (!defined('ABSPATH')) {
    exit;
}

final class BSPluginOptions extends Migration
{
    public function up()
    {
        if (!Config::getOption('installed', null)) {
            Config::addOption('db_version', Config::DB_VERSION, true);
            Config::addOption('installed', time(), true);
            Config::addOption('version', Config::VERSION, true);
            Config::addOption('secret_key', Config::SLUG . time(), null);
        }
    }

    public function down()
    {
        $pluginOptions = [
            Config::withPrefix('db_version'),
            Config::withPrefix('installed'),
            Config::withPrefix('version'),
            Config::withPrefix('secret_key'),
            Config::withPrefix('auto_post_settings'),
            Config::withPrefix('templates_settings'),
        ];

        DB::query(
            DB::prepare(
                'DELETE FROM `' . DB::wpPrefix() . 'options` WHERE option_name in ('
                    . implode(
                        ',',
                        array_map(
                            function () {
                                return '%s';
                            },
                            $pluginOptions
                        )
                    ) . ')',
                $pluginOptions
            )
        );
    }
}
