<?php

namespace BitApps\Social;

/*
 * Main class for the plugin.
 *
 * @since 1.0.0-alpha
 */

use BitApps\Social\Deps\BitApps\WPKit\Hooks\Hooks;
use BitApps\Social\Deps\BitApps\WPKit\Http\RequestType;
use BitApps\Social\Deps\BitApps\WPKit\Migration\MigrationHelper;
use BitApps\Social\Deps\BitApps\WPKit\Utils\Capabilities;
use BitApps\Social\Deps\BitApps\WPTelemetry\Telemetry\Telemetry;
use BitApps\Social\Deps\BitApps\WPTelemetry\Telemetry\TelemetryConfig;
use BitApps\Social\HTTP\Controllers\BitSocialAnalyticsController;
use BitApps\Social\HTTP\Middleware\NonceCheckerMiddleware;
use BitApps\Social\HTTP\Services\Schedule\CustomSchedule;
use BitApps\Social\Model\Schedule;
use BitApps\Social\Providers\HookProvider;
use BitApps\Social\Providers\InstallerProvider;
use BitApps\Social\Views\Layout;
use BitApps\Social\Views\PluginPageActions;

final class Plugin
{
    /**
     * Initialize the Plugin with hooks.
     */

    /**
     * Main instance of the plugin.
     *
     * @since 1.0.0-alpha
     *
     * @var null|Plugin
     */
    private static $_instance;

    private $_registeredMiddleware = [];

    public function __construct()
    {
        $this->registerInstaller();

        Hooks::addAction('plugins_loaded', [$this, 'loaded']);

        Hooks::addFilter(Config::withPrefix('telemetry_additional_data'), [new BitSocialAnalyticsController(), 'filterTrackingData']);

        $this->initWPTelemetry();
    }

    public function registerInstaller()
    {
        $installerProvider = new InstallerProvider();
        $installerProvider->register();
    }

    /**
     * Load the plugin.
     */
    public function loaded()
    {
        Hooks::doAction(Config::withPrefix('loaded'));
        Hooks::addAction('init', [$this, 'registerProviders'], 11);
        Hooks::addFilter('plugin_action_links_' . Config::get('BASENAME'), [new PluginPageActions(), 'renderActionLinks']);
        $this->maybeMigrateDB();
        $this->customScheduleInit();
    }

    public function initWPTelemetry()
    {
        TelemetryConfig::setSlug(Config::SLUG);
        TelemetryConfig::setTitle(Config::TITLE);
        TelemetryConfig::setVersion(Config::VERSION);
        TelemetryConfig::setPrefix(Config::VAR_PREFIX);

        TelemetryConfig::setServerBaseUrl('https://wp-api.bitapps.pro/public/');

        TelemetryConfig::setTermsUrl('https://bitapps.pro/terms-of-service/');
        TelemetryConfig::setPolicyUrl('https://bitapps.pro/refund-policy/');

        Telemetry::report()->init();
        Telemetry::feedback()->init();
    }

    public function customScheduleInit()
    {
        $schedules = Schedule::get(['id', 'config', 'status', 'started_at', 'cron_status', 'schedule_type']);
        if ($schedules && is_countable($schedules) && \count($schedules) > 0) {
            $schedule = new CustomSchedule($schedules);
            $schedule->register();
        }
    }

    public function middlewares()
    {
        return [
            'nonce' => NonceCheckerMiddleware::class,
        ];
    }

    public function getMiddleware($name)
    {
        if (isset($this->_registeredMiddleware[$name])) {
            return $this->_registeredMiddleware[$name];
        }

        $middlewares = $this->middlewares();
        if (isset($middlewares[$name]) && class_exists($middlewares[$name]) && method_exists($middlewares[$name], 'handle')) {
            $this->_registeredMiddleware[$name] = new $middlewares[$name]();
        } else {
            return false;
        }

        return $this->_registeredMiddleware[$name];
    }

    /**
     * Instantiate the Provider class.
     */
    public function registerProviders()
    {
        if (RequestType::is('admin')) {
            new Layout();
        }

        new HookProvider();
    }

    public static function maybeMigrateDB()
    {
        if (!Capabilities::check('manage_options')) {
            return;
        }

        if (version_compare(Config::getOption('db_version'), Config::DB_VERSION, '<') || version_compare(Config::getOption('version'), Config::VERSION, '<')) {
            MigrationHelper::migrate(InstallerProvider::migration());
        }
    }

    /**
     * Retrieves the main instance of the plugin.
     *
     * @since 1.0.0-alpha
     *
     * @return Plugin plugin main instance
     */
    public static function instance()
    {
        return static::$_instance;
    }

    /**
     * Loads the plugin main instance and initializes it.
     *
     * @return bool True if the plugin main instance could be loaded, false otherwise
     *
     * @since 1.0.0-alpha
     */
    public static function load()
    {
        if (static::$_instance !== null) {
            return false;
        }

        static::$_instance = new static();

        return true;
    }
}
