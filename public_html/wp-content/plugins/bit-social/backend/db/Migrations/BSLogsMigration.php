<?php

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPDatabase\Blueprint;
use BitApps\Social\Deps\BitApps\WPDatabase\Connection;
use BitApps\Social\Deps\BitApps\WPDatabase\Schema;
use BitApps\Social\Deps\BitApps\WPKit\Migration\Migration;

if (!defined('ABSPATH')) {
    exit;
}

final class BSLogsMigration extends Migration
{
    public function up()
    {
        Schema::withPrefix(Connection::wpPrefix() . Config::VAR_PREFIX)->create('logs', function (Blueprint $table) {
            $table->id();
            $table->bigint('schedule_id', 20)->nullable()->unsigned()->foreign('schedules', 'id')->onDelete()->cascade();
            $table->longtext('details');
            $table->string('platform');
            $table->boolean('status')->defaultValue(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::withPrefix(Connection::wpPrefix() . Config::VAR_PREFIX)->drop('logs');
    }
}
