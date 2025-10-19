<?php

use BitApps\Social\Config as FreeConfig;
use BitApps\Social\Deps\BitApps\WPDatabase\Blueprint;
use BitApps\Social\Deps\BitApps\WPDatabase\Connection;
use BitApps\Social\Deps\BitApps\WPDatabase\Schema;
use BitApps\Social\Deps\BitApps\WPKit\Migration\Migration;

if (!defined('ABSPATH')) {
    exit;
}

final class BSFreeGroupTableDrop extends Migration
{
    public function up()
    {
        try {
            if (version_compare(FreeConfig::getOption('db_version'), '1.1.0', '<')) {
                return Schema::withPrefix(Connection::wpPrefix() . FreeConfig::VAR_PREFIX)->edit('groups', function (Blueprint $table) {
                    $table->dropColumn('account_ids');
                });
            }
        } catch (Exception $e) {
        }
    }

    public function down()
    {
        // Schema::withPrefix(Connection::wpPrefix() . Config::VAR_PREFIX)->drop('groups');
    }
}
