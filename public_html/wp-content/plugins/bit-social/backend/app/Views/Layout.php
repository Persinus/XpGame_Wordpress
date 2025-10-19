<?php

namespace BitApps\Social\Views;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Hooks\Hooks;

/**
 * The admin Layout and page handler class.
 */
class Layout
{
    public function __construct()
    {
        Hooks::addAction('in_admin_header', [$this, 'removeAdminNotices']);
        Hooks::addAction('admin_menu', [new SideBarMenu(), 'addMenu']);
        Hooks::addAction('admin_enqueue_scripts', [new Head(), 'addHeadScripts'], 0);
        Hooks::addFilter('style_loader_tag', [$this, 'linkTagFilter'], 0, 3);
        Hooks::addFilter('script_loader_tag', [$this, 'scriptTagFilter'], 0, 3);
        Hooks::addFilter('script_loader_tag', [$this, 'updateScriptAttributes'], 0, 1);
        Hooks::addFilter('script_loader_src', [$this, 'removeQueryParam'], 99999, 3);
    }

    public function updateScriptAttributes($html)
    {
        $slug = Config::SLUG;

        $typeAttribute = 'type="module"';

        $keys = [
            '-vite-client-helper-MODULE-js',
            '-vite-client-MODULE-js',
            '-index-MODULE-js',
        ];

        if (Config::getEnv('DEV')) {
            foreach ($keys as $key) {
                $handle = 'id="' . $slug . $key . '"';

                if (strpos($html, $handle) !== false) {
                    $html = str_replace($handle, $handle . ' ' . $typeAttribute, $html);
                }
            }
        } else {
            $handle = 'id="' . $slug . '-index-MODULE-js"';

            if (strpos($html, $handle) !== false) {
                $html = str_replace($handle, $handle . ' ' . $typeAttribute, $html);
            }
        }

        return $html;
    }

    public function removeAdminNotices()
    {
        global $plugin_page;
        if (empty($plugin_page) || strpos($plugin_page, Config::SLUG) === false) {
            return;
        }

        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }

    /**
     * Modify style tags.
     *
     * @param string $html   link tag
     * @param mixed  $handle
     * @param mixed  $href
     *
     * @return string new link tag
     */
    public function linkTagFilter($html, $handle, $href)
    {
        $newTag = $html;
        if (str_contains($handle, 'PRECONNECT')) {
            $newTag = preg_replace('/rel=("|\')stylesheet("|\')/', 'rel="preconnect"', $newTag);
        }

        if (str_contains($handle, 'PRELOAD')) {
            $newTag = preg_replace('/rel=("|\')stylesheet("|\')/', 'rel="preload"', $newTag);
        }

        if (str_contains($handle, 'CROSSORIGIN')) {
            $newTag = preg_replace('/<link /', '<link crossorigin ', $newTag);
        }

        if (str_contains($handle, 'SCRIPT')) {
            $newTag = preg_replace('/<link /', '<link as="script" ', $newTag);
        }

        return $newTag;
    }

    /**
     * Modify script tags.
     *
     * @param string $html   script tag
     * @param mixed  $handle
     * @param mixed  $href
     *
     * @return string new script tag
     */
    public function scriptTagFilter($html, $handle, $href)
    {
        $newTag = $html;
        if (str_contains($handle, 'MODULE')) {
            $newTag = preg_replace('/<script /', '<script type="module" ', $newTag);
        }

        return $newTag;
    }

    public function removeQueryParam($src, $handle)
    {
        if (Config::SLUG . '-index-MODULE' === $handle) {
            $src = strtok($src, '?');
        }

        return $src;
    }
}
