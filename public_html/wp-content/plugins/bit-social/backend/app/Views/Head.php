<?php

namespace BitApps\Social\Views;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Helpers\DateTimeHelper;

class Head
{
    public const FONT_URL = 'https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap';

    /**
     * Load the asset libraries.
     *
     * @param string $currentScreen $top_level_page variable for current page
     */
    public function addHeadScripts($currentScreen)
    {
        if (strpos($currentScreen, Config::SLUG) === false) {
            return;
        }

        $version = Config::VERSION;
        $slug = Config::SLUG;
        $codeName = Config::get('BUILD_CODE_NAME');

        // loading google fonts
        wp_enqueue_style('googleapis-PRECONNECT', 'https://fonts.googleapis.com');
        wp_enqueue_style('gstatic-PRECONNECT-CROSSORIGIN', 'https://fonts.gstatic.com');
        wp_enqueue_style('font', self::FONT_URL, [], $version);

        if (Config::getEnv('DEV')) {
            wp_enqueue_script($slug . '-vite-client-helper-MODULE', Config::getENV('DEV_URL') . '/src/config/devHotModule.js', [], null);
            wp_enqueue_script($slug . '-vite-client-MODULE', Config::getENV('DEV_URL') . '/@vite/client', [], null);
            wp_enqueue_script($slug . '-index-MODULE', Config::getENV('DEV_URL') . '/src/main.tsx', [], null);
            // wp_enqueue_style('load-id', 'http://wpdev.co/wp-admin/load-styles.php?c=0&amp;dir=ltr&amp;load%5Bchunk_0%5D=dashicons,admin-bar,common,forms,admin-menu,dashboard,list-tables,edit,revisions,media,themes,about,nav-menus,wp-pointer,widgets&amp;load%5Bchunk_1%5D=,site-icon,l10n,buttons,media-views,wp-auth-check&amp;ver=6.6.2', [], null);
        } else {
            wp_enqueue_script($slug . '-index-MODULE', Config::get('ASSET_URI') . "/main-{$codeName}.js", [], ''); // WARNING: Do not add version in production, it may cause unexpected behavior.
            wp_enqueue_style($slug . '-styles', Config::get('ASSET_URI') . "/main-{$slug}-ba-assets-{$codeName}.css", null, $version, 'screen');
        }

        if (!wp_script_is('media-upload')) {
            wp_enqueue_media();
        }

        wp_localize_script(Config::SLUG . '-index-MODULE', Config::VAR_PREFIX, self::createConfigVariable());
    }

    /**
     * Create config variable for js.
     *
     * @return array
     */
    public static function createConfigVariable()
    {
        $frontendVars = apply_filters(
            Config::withPrefix('localized_script'),
            [
                'nonce'            => wp_create_nonce(Config::withPrefix('nonce')),
                'rootURL'          => Config::get('ROOT_URI'),
                'assetsURL'        => Config::get('ASSET_URI'),
                'siteUrl'          => Config::get('SITE_URL'),
                'siteBaseURL'      => is_multisite() ? network_site_url() : site_url(),
                'siteName'         => Config::get('SITE_NAME'),
                'baseURL'          => Config::get('ADMIN_URL') . 'admin.php?page=' . Config::SLUG . '#',
                'ajaxURL'          => admin_url('admin-ajax.php'),
                'apiURL'           => Config::get('API_URL'),
                'routePrefix'      => Config::VAR_PREFIX,
                'settings'         => Config::getOption('settings'),
                'dateFormat'       => Config::getOption('date_format', true, true),
                'timeFormat'       => Config::getOption('time_format', true, true),
                'timeZone'         => DateTimeHelper::wp_timezone_string(),
                'version'          => Config::VERSION,
                'changelogVersion' => Config::getOption('changelog_version', '0.0.0'),
                'pluginSlug'       => Config::SLUG,
                'baseAuthStateURL' => Config::get('BASE_AUTH_STATE_URL'),
                'wpCronStatus'     => Config::get('WP_CRON_STATUS'),
                'loggedInUserName' => wp_get_current_user()->display_name,

            ]
        );

        if (get_locale() !== 'en_US' && file_exists(Config::get('ROOT_DIR') . '/languages/frontend-extracted-strings.php')) {
            $i18nStrings = include Config::get('ROOT_DIR') . '/languages/frontend-extracted-strings.php';
            $frontendVars['translations'] = $i18nStrings;
        }

        return $frontendVars;
    }
}
