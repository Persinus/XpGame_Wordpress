<?php

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPDatabase\Blueprint;
use BitApps\Social\Deps\BitApps\WPDatabase\Connection;
use BitApps\Social\Deps\BitApps\WPDatabase\Schema;
use BitApps\Social\Deps\BitApps\WPKit\Migration\Migration;

if (!defined('ABSPATH')) {
    exit;
}

final class BSAccountsMigration extends Migration
{
    public function up()
    {
        Schema::withPrefix(Connection::wpPrefix() . Config::VAR_PREFIX)->create('accounts', function (Blueprint $table) {
            $table->id();
            $table->bigint('custom_app_id', 20)->nullable()->unsigned()->foreign('custom_apps', 'id')->onDelete()->cascade();
            $table->string('profile_id');
            $table->string('account_id');
            $table->string('account_name');
            $table->longtext('details');
            $table->string('platform');
            $table->integer('account_type');
            $table->boolean('status')->defaultValue(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::withPrefix(Connection::wpPrefix() . Config::VAR_PREFIX)->drop('accounts');
    }
}
