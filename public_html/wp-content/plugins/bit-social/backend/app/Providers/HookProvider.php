<?php

namespace BitApps\Social\Providers;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Hooks\Hooks;
use BitApps\Social\Deps\BitApps\WPKit\Http\RequestType;
use BitApps\Social\Deps\BitApps\WPKit\Http\Router\Router;
use BitApps\Social\Plugin;
use FilesystemIterator;

class HookProvider
{
    private $_pluginBackend;

    public function __construct()
    {
        $this->_pluginBackend = Config::get('BASEDIR') . DIRECTORY_SEPARATOR;
        $this->loadTriggersAjax();
        $this->loadAppHooks();
        $this->loadActionsHooks();
        $this->loadScheduleActionHooks();
        Hooks::addAction('rest_api_init', [$this, 'loadApi']);

        if (Config::getEnv('CLI_ACTIVE')) {
            include_once __DIR__ . '/../../../cli/RegisterCommands.php';
        }
    }

    public function loadScheduleActionHooks()
    {
        ScheduleActionHook::register();
    }

    /**
     * Helps to register integration ajax.
     */
    public function loadActionsHooks()
    {
        // $this->includeTaskHooks('Actions');
    }

    /**
     * Loads API routes.
     */
    public function loadApi()
    {
        if (
            is_readable($this->_pluginBackend . 'routes' . DIRECTORY_SEPARATOR . 'api.php')
            && RequestType::is(RequestType::API)
        ) {
            $router = new Router(RequestType::API, Config::SLUG, 'v1');

            include $this->_pluginBackend . 'routes' . DIRECTORY_SEPARATOR . 'api.php';
            $router->register();
        }
    }

    /**
     * Helps to register App hooks.
     */
    protected function loadAppHooks()
    {
        if (
            RequestType::is(RequestType::AJAX)
            && is_readable($this->_pluginBackend . 'routes' . DIRECTORY_SEPARATOR . 'ajax.php')
            && current_user_can('administrator')
        ) {
            $router = new Router(RequestType::AJAX, Config::VAR_PREFIX, '');
            $router->setMiddlewares(Plugin::instance()->middlewares());
            include $this->_pluginBackend . 'routes' . DIRECTORY_SEPARATOR . 'ajax.php';
            $router->register();
        }

        if (is_readable($this->_pluginBackend . 'hooks.php')) {
            include $this->_pluginBackend . 'hooks.php';
        }
    }

    /**
     * Helps to register Triggers ajax.
     */
    protected function loadTriggersAjax()
    {
        // $this->includeTaskHooks('Triggers');
    }

    /**
     * Backend Routes and Hooks.
     *
     * @param string $taskName Triggers|Actions
     */
    private function includeTaskHooks($taskName)
    {
        $taskDir = $this->_pluginBackend . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $taskName;
        $dirs = new FilesystemIterator($taskDir);
        foreach ($dirs as $dirInfo) {
            if ($dirInfo->isDir()) {
                $taskName = basename($dirInfo);
                $taskPath = $taskDir . DIRECTORY_SEPARATOR . $taskName . DIRECTORY_SEPARATOR;
                if (is_readable($taskPath . 'Routes.php') && RequestType::is('ajax') && RequestType::is('admin')) {
                    include $taskPath . 'Routes.php';
                }

                if (is_readable($taskPath . 'Hooks.php')) {
                    include $taskPath . 'Hooks.php';
                }
            }
        }
    }
}
