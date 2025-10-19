<?php

namespace BitApps\Social\Views;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Hooks\Hooks;
use BitApps\Social\Deps\BitApps\WPKit\Utils\Capabilities;

class SideBarMenu
{
    /**
     * Provides menus for wordpress admin sidebar.
     * should return an array of menus with the following structure:
     * [
     *   'type' => menu | submenu,
     *  'name' => 'Name of menu will shown in sidebar',
     *  'capability' => 'capability required to access menu',
     *  'slug' => 'slug of menu after ?page=',.
     *
     *  'title' => 'page title will be shown in browser title if type is menu',
     *  'callback' => 'function to call when menu is clicked',
     *  'icon' =>   'icon to display in menu if menu type is menu',
     *  'position' => 'position of menu in sidebar if menu type is menu',
     *
     * 'parent' => 'parent slug if submenu'
     * ]
     *
     * @return array
     */
    public function createMenu()
    {
        $icon = '<svg width="36" height="34" viewBox="0 0 36 34" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M17.9403 1.3999C15.1093 1.3999 12.8145 3.66555 12.8145 6.46034C12.8145 9.25515 15.1093 11.5208 17.9403 11.5208C20.7713 11.5208 23.066 9.25515 23.066 6.46034C23.066 3.66555 20.7713 1.3999 17.9403 1.3999ZM7.91819 11.2447C13.2538 11.2447 17.5792 15.5149 17.5792 20.7825V28.5999H12.7738V20.7825C12.7738 20.2214 12.6762 19.6831 12.4969 19.1827L5.43836 26.1513L2.04042 22.7965L8.84736 16.0765C8.54661 16.019 8.23595 15.9888 7.91819 15.9888H0V11.2447H7.91819ZM28.0819 11.2447C22.7463 11.2447 18.4208 15.5149 18.4208 20.7825V28.5999H23.2262V20.7825C23.2262 20.3432 23.2861 19.9175 23.3983 19.5133L30.7819 26.8028L34.18 23.4481L26.7963 16.1586C27.2058 16.0479 27.637 15.9888 28.0819 15.9888H36V11.2447H28.0819Z" fill="#808285"/>
          </svg>';

        return [
            'Home' => [
                'type'       => 'menu',
                'title'      => __("Bit Social - Your social of automation's", 'bit-social'),
                'name'       => __('Bit Social', 'bit-social'),
                'capability' => 'manage_options',
                'slug'       => Config::SLUG,
                'callback'   => [new Body(), 'render'],
                'icon'       => 'data:image/svg+xml;base64,' . base64_encode($icon),
                'position'   => '20',
            ],
            'Dashboard' => [
                'parent'     => Config::SLUG,
                'type'       => 'submenu',
                'name'       => __('Home'),
                'capability' => 'manage_options',
                'slug'       => Config::SLUG . '#/',
            ],
            'Accounts' => [
                'parent'     => Config::SLUG,
                'type'       => 'submenu',
                'name'       => __('Accounts'),
                'capability' => 'manage_options',
                'slug'       => Config::SLUG . '#/accounts',
            ],
            'Auto Post' => [
                'parent'     => Config::SLUG,
                'type'       => 'submenu',
                'name'       => __('WP Auto Post'),
                'capability' => 'manage_options',
                'slug'       => Config::SLUG . '#/auto-post',
            ],
            'Schedules' => [
                'parent'     => Config::SLUG,
                'type'       => 'submenu',
                'name'       => __('WP Post Schedules'),
                'capability' => 'manage_options',
                'slug'       => Config::SLUG . '#/schedules',
            ],
            'Share Now' => [
                'parent'     => Config::SLUG,
                'type'       => 'submenu',
                'name'       => __('Share Now'),
                'capability' => 'manage_options',
                'slug'       => Config::SLUG . '#/share-now',
            ],
            'Calendar' => [
                'parent'     => Config::SLUG,
                'type'       => 'submenu',
                'name'       => __('Calendar'),
                'capability' => 'manage_options',
                'slug'       => Config::SLUG . '#/calendar',
            ],
            'Templates' => [
                'parent'     => Config::SLUG,
                'type'       => 'submenu',
                'name'       => __('Templates'),
                'capability' => 'manage_options',
                'slug'       => Config::SLUG . '#/templates',
            ],
            'Logs' => [
                'parent'     => Config::SLUG,
                'type'       => 'submenu',
                'name'       => __('Logs'),
                'capability' => 'manage_options',
                'slug'       => Config::SLUG . '#/logs',
            ],
            'Settings' => [
                'parent'     => Config::SLUG,
                'type'       => 'submenu',
                'name'       => __('Settings'),
                'capability' => 'manage_options',
                'slug'       => Config::SLUG . '#/settings',
            ],
            'Support' => [
                'parent'     => Config::SLUG,
                'type'       => 'submenu',
                'name'       => __('License & Support'),
                'capability' => 'manage_options',
                'slug'       => Config::SLUG . '#/support',
            ],
        ];
    }

    /**
     * Register the admin left sidebar menu item.
     */
    public function addMenu()
    {
        $menus = Hooks::applyFilter(Config::withPrefix('admin_sidebar_menu'), $this->createMenu());
        global $submenu;
        foreach ($menus as $menu) {
            if (isset($menu['capability']) && Capabilities::check($menu['capability'])) {
                if ($menu['type'] == 'menu') {
                    add_menu_page(
                        $menu['title'],
                        $menu['name'],
                        $menu['capability'],
                        $menu['slug'],
                        $menu['callback'],
                        $menu['icon'],
                        $menu['position']
                    );
                } else {
                    $submenu[$menu['parent']][] = [$menu['name'], $menu['capability'], 'admin.php?page=' . $menu['slug']];
                }
            }
        }
    }
}
