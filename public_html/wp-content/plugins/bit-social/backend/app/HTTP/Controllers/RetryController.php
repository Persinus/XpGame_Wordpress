<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\Model\Account;
use BitApps\Social\Model\Schedule;
use BitApps\Social\Utils\Common;
use BitApps\Social\Utils\WpPost;

class RetryController
{
    use Common, WpPost;

    public function retry(Request $request)
    {
        $validated = $request->validate([
            'key'        => ['required'],
            'scheduleId' => ['required'],
            'details'    => ['required'],
        ]);

        $accountDetails = Account::select(['id', 'details', 'platform'])->findOne(['account_id' => $validated['details']['account_id']]);

        if (empty($accountDetails)) {
            return Response::error('Account not found');
        }

        $scheduleId = $validated['scheduleId'];
        $postId = $validated['details']['post_id'] ?? null;
        $post = get_post($postId);

        $scheduleDetail = Schedule::select(['id', 'config', 'schedule_type'])->findOne(['id' => $scheduleId]);
        $template = $scheduleDetail['config']['templates'][$accountDetails['platform']];

        $isPlatFormExists = $this->isPlatFormExists($accountDetails['id']);
        $platformPostPublish = new $isPlatFormExists['class']();

        $postData = [
            'ID'   => $postId,
            'name' => $post->post_title ?? null,
        ];

        if (!empty($template) && !empty($isPlatFormExists['platform'])) {
            $data = [
                'post'            => $postData,
                'template'        => $template,
                'account_details' => $isPlatFormExists['details'],
                'schedule_id'     => $scheduleId,
                'schedule_type'   => $scheduleDetail['schedule_type'],
                'log_id'          => $validated['key'],
                'retry'           => true,
            ];

            return $platformPostPublish->publishPost($data);
        }
    }
}
