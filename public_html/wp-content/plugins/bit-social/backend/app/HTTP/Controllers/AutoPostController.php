<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Helpers\Arr;
use BitApps\Social\Deps\BitApps\WPKit\Http\Client\HttpClient;
use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\HTTP\Services\Social\Social;
use BitApps\Social\Model\Account;
use BitApps\Social\Utils\Common;
use BitApps\SocialPro\HTTP\Services\AutoPost\AutoPostService;
use BitApps\SocialPro\Model\GroupsAccount;

class AutoPostController
{
    use Common;

    public $defaultAutoPostSettings = [
        'isEnabled'  => false,
        'keepLogs'   => true,
        'taxonomies' => ['categories', 'tags'],
        'accounts'   => [
            'accountIds' => [],
            'groupIds'   => []
        ],
        'postType'  => ['post'],
        'postDelay' => [
            'every' => 0,
            'unit'  => ''
        ]
    ];

    public function autoPostSettings()
    {
        return Response::success($this->getAutoPostSettings());
    }

    public function update(Request $request)
    {
        Config::updateOption('auto_post_settings', $request->autoPostSettings);

        return Response::success('Auto post settings updated.');
    }

    public function getAutoPostSettings(): array
    {
        $autoPostSettings = Config::getOption('auto_post_settings');

        if (!$autoPostSettings) {
            Config::updateOption('auto_post_settings', $this->defaultAutoPostSettings);

            $autoPostSettings = $this->defaultAutoPostSettings;
        }
        $keyDiff = $this->arrayDiffNestedKeys($this->defaultAutoPostSettings, $autoPostSettings);

        if (\count($keyDiff) !== 0) {
            $autoPostSettings = array_replace_recursive($autoPostSettings, $keyDiff);

            Config::updateOption('auto_post_settings', $autoPostSettings);
        }

        return $autoPostSettings;
    }

    public function publishWpPost($postId, $post, $update, $postBefore)
    {
        $autoPostSettings = $this->getAutoPostSettings();

        $postDelay = $autoPostSettings['postDelay'];

        $delayTypeImmediately = $postDelay['every'] === 0 && $postDelay['unit'] === '';

        if ('publish' !== $post->post_status || !\in_array($post->post_type, $autoPostSettings['postType']) || ($postBefore && 'publish' === $postBefore->post_status)) {
            return;
        }

        if (!$autoPostSettings['isEnabled']) {
            return;
        }

        if (!$delayTypeImmediately && class_exists('BitApps\SocialPro\HTTP\Services\AutoPost\AutoPostService')) {
            return AutoPostService::createAutoPostDelay($post, $postId, $autoPostSettings);
        }

        if ((!isset($postBefore->post_status) && $post->post_status === 'publish') || ($post->post_status === 'publish' && $postBefore->post_status === 'future')) {
            return $this->executeSocialPost($postId);
        }

        $args = [
            'timeout'   => 0.1,
            'blocking'  => false,
            'cookies'   => $_COOKIE,
            'sslverify' => apply_filters('https_local_ssl_verify', false),
        ];

        $queryArgs = [
            'action'      => Config::VAR_PREFIX . 'auto-post-background-process',
            '_ajax_nonce' => wp_create_nonce(Config::withPrefix('nonce')),
        ];

        $url = add_query_arg($queryArgs, admin_url('admin-ajax.php'));

        (new HttpClient())->request($url, 'POST', ['post_id' => $postId], null, $args);
    }

    public function executeSocialPost($postId = null)
    {
        if (isset($_REQUEST['post_id'])) {
            $postId = sanitize_text_field($_REQUEST['post_id']);
        }

        $templates = (array) (new SocialTemplateController())->getSocialTemplates();

        $autoPostSettings = $this->getAutoPostSettings();

        $accountIds = !empty($autoPostSettings['accounts']['accountIds']) ? $autoPostSettings['accounts']['accountIds'] : [];
        $groupAccountIds = [];

        if (!empty($autoPostSettings['accounts']['groupIds']) && class_exists('BitApps\SocialPro\Model\GroupsAccount')) {
            $groupAccountIds = $this->groupsAccountIds($autoPostSettings['accounts']['groupIds']);
        }

        $allAccountIds = array_unique([...$accountIds, ...$groupAccountIds]);

        if (!empty($allAccountIds)) {
            $accounts = Account::whereIn('id', $allAccountIds)
                ->where('status', Account::ACCOUNT_STATUS['active'])
                ->get();
        }

        if (!\is_array($accounts)) {
            return;
        }

        $isSleep = false;

        foreach ($accounts as $account) {
            $isPlatFormExists = $this->isPlatFormExists($account->id);

            $platform = new Social(new $isPlatFormExists['class']());

            if (isset($templates[$isPlatFormExists['platform']])) {
                $template = $templates[$isPlatFormExists['platform']];

                preg_match('/custom_field_\[([^\]]+)\]/', $template['content'] ?? '', $matches);

                if (!$isSleep && isset($matches[1])) {
                    $isSleep = true;
                    sleep(2); // Wait for the post meta values to be saved after the hook call
                }

                $data = [
                    'post'            => (array) get_post($postId),
                    'template'        => $template,
                    'account_details' => $isPlatFormExists['details'],
                    'keepLogs'        => $autoPostSettings['keepLogs']
                ];

                $platform->publishPost($data);
            }
        }
    }

    public function groupsAccountIds($groupIds)
    {
        if (class_exists('BitApps\SocialPro\Model\GroupsAccount')) {
            $arr = new Arr();
            $accountIds = GroupsAccount::where('group_id', $groupIds)->get('account_id');

            return $arr->pluck($accountIds, 'account_id');
        }

        return [];
    }

    public function arrayDiffNestedKeys($array1, $array2)
    {
        $diff = [];
        foreach ($array1 as $key => $value) {
            if (!\array_key_exists($key, $array2)) {
                $diff[$key] = $value;
            } elseif (\is_array($value) && \is_array($array2[$key])) {
                $nestedDiff = $this->arrayDiffNestedKeys($value, $array2[$key]);
                if (!empty($nestedDiff)) {
                    $diff[$key] = $nestedDiff;
                }
            }
        }

        return $diff;
    }
}
