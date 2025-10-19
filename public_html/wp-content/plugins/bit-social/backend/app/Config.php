<?php

// phpcs:disable Squiz.NamingConventions.ValidVariableName

namespace BitApps\Social;

use BitApps\SocialPro\Config as ProConfig;

if (!\defined('ABSPATH')) {
    exit;
}

/**
 * Provides App configurations.
 */
class Config
{
    public const SLUG = 'bit-social';

    public const TITLE = 'Bit Social';

    public const VAR_PREFIX = 'bit_social_';

    public const VERSION = '1.7.0';

    public const DB_VERSION = '1.1.0';

    public const REQUIRED_PHP_VERSION = '7.4';

    public const REQUIRED_WP_VERSION = '5.1';

    public const API_VERSION = '1.0';

    public const APP_BASE = '../../bit-social.php';

    public const CLASS_PREFIX = 'BS';

    public const ASSETS_FOLDER = 'assets';

    public const PRO_PLUGIN_VAR_PREFIX = 'bit_social_pro_';

    public const PRO_PLUGIN_SLUG = 'bit-social-pro';

    /**
     * Provides configuration for plugin.
     *
     * @param string $type    Type of conf
     * @param string $default Default value
     *
     * @return null|array|string
     */
    public static function get($type, $default = null)
    {
        switch ($type) {
            case 'MAIN_FILE':
                return realpath(__DIR__ . DIRECTORY_SEPARATOR . self::APP_BASE);

            case 'BASENAME':
                return plugin_basename(trim(self::get('MAIN_FILE')));

            case 'BASEDIR':
                return plugin_dir_path(self::get('MAIN_FILE')) . 'backend';

            case 'ROOT_DIR':
                return plugin_dir_path(self::get('MAIN_FILE'));

            case 'SITE_URL':

                return site_url();
            case 'SITE_NAME':
                return get_bloginfo('name');

            case 'ADMIN_URL':
                return str_replace(self::get('SITE_URL'), '', get_admin_url());

            case 'BASE_AUTH_STATE_URL':
                return self::get('SITE_URL') . self::get('ADMIN_URL') . 'admin.php?page=' . self::SLUG;

            case 'API_URL':
                global $wp_rewrite;

                return [
                    'base'      => get_rest_url() . self::SLUG . '/v1',
                    'separator' => $wp_rewrite->permalink_structure ? '?' : '&',
                ];

            case 'DEV_URL':
                return isset($_ENV['DEV_URL']) ? $_ENV['DEV_URL'] : null;

            case 'ROOT_URI':
                return set_url_scheme(plugins_url('', self::get('MAIN_FILE')), parse_url(home_url())['scheme']);

            case 'ASSET_URI':
                if (self::isProActivated()) {
                    return ProConfig::get('ASSET_URI');
                }

                return self::get('ROOT_URI') . '/' . self::ASSETS_FOLDER;

            case 'BUILD_CODE_NAME':
                if (self::getEnv('DEV')) {
                    return '';
                }
                if (self::isProActivated()) {
                    return file_get_contents(ProConfig::get('ROOT_DIR') . self::ASSETS_FOLDER . '/build-code-name.txt');
                }

                return file_get_contents(self::get('ROOT_DIR') . self::ASSETS_FOLDER . '/build-code-name.txt');

            case 'WP_DB_PREFIX':
                global $wpdb;

                return $wpdb->prefix;
            case 'WP_CRON_STATUS':
                return \defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ? false : true;
            default:
                return $default;
        }
    }

    /**
     * Check if pro plugin exist and active
     *
     * @return bool
     */
    public static function isProActivated()
    {
        if (class_exists(ProConfig::class)) {
            return ProConfig::isPro();
        }

        return false;
    }

    /**
     * Prefixed variable name with prefix.
     *
     * @param string $option Variable name
     *
     * @return array
     */
    public static function withPrefix($option)
    {
        return self::VAR_PREFIX . $option;
    }

    /**
     * Prefixed table name with db prefix and var prefix.
     *
     * @param mixed $table
     *
     * @return string
     */
    public static function withDBPrefix($table)
    {
        return self::get('WP_DB_PREFIX') . self::withPrefix($table);
    }

    /**
     * Retrieves options from option table.
     *
     * @param string $option  Option name
     * @param bool   $default default value
     * @param bool   $wp      Whether option is default wp option
     *
     * @return mixed
     */
    public static function getOption($option, $default = false, $wp = false)
    {
        if ($wp) {
            return get_option($option, $default);
        }

        return get_option(self::withPrefix($option), $default);
    }

    /**
     * Saves option to option table.
     *
     * @param string $option   Option name
     * @param bool   $autoload Whether option will autoload
     * @param mixed  $value
     *
     * @return bool
     */
    public static function addOption($option, $value, $autoload = false)
    {
        return add_option(self::withPrefix($option), $value, '', $autoload ? 'yes' : 'no');
    }

    /**
     * Save or update option to option table.
     *
     * @param string $option   Option name
     * @param mixed  $value    Option value
     * @param bool   $autoload Whether option will autoload
     *
     * @return bool
     */
    public static function updateOption($option, $value, $autoload = null)
    {
        return update_option(self::withPrefix($option), $value, !\is_null($autoload) ? 'yes' : null);
    }

    public static function deleteOption($option)
    {
        return delete_option(self::withPrefix($option));
    }

    public static function getEnv($keyName)
    {
        return isset($_ENV[Config::VAR_PREFIX . $keyName]) ? $_ENV[Config::VAR_PREFIX . $keyName] : false;
    }
}
