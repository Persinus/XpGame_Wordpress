<?php

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPDatabase\Blueprint;
use BitApps\Social\Deps\BitApps\WPDatabase\Connection;
use BitApps\Social\Deps\BitApps\WPDatabase\Schema;
use BitApps\Social\Deps\BitApps\WPKit\Migration\Migration;

if (!defined('ABSPATH')) {
    exit;
}

final class BSSchedulesMigration extends Migration
{
    public function up()
    {
        Schema::withPrefix(Connection::wpPrefix() . Config::VAR_PREFIX)->create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longtext('config')->nullable();
            $table->longtext('published_post_ids')->nullable();
            $table->boolean('repeat_schedule')->defaultValue(0);
            $table->boolean('schedule_type');
            $table->boolean('status')->defaultValue(1);
            $table->boolean('cron_status')->defaultValue(1);
            $table->timestamp('started_at')->nullable()->defaultValue('NULL');
            $table->timestamp('ended_at')->nullable()->defaultValue('NULL');
            $table->timestamp('last_published_at')->nullable()->defaultValue('NULL');
            $table->timestamp('next_published_at')->nullable()->defaultValue('NULL');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::withPrefix(Connection::wpPrefix() . Config::VAR_PREFIX)->drop('schedules');
    }
}
