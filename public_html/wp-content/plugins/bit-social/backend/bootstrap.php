<?php

use BitApps\Social\Dotenv;

if (! defined('ABSPATH')) {
    exit;
}

// Autoload vendor files.
require_once __DIR__ . '/../vendor/autoload.php';

Dotenv::load(plugin_dir_path(__DIR__) . '.env');

// Initialize the plugin.
BitApps\Social\Plugin::load();
