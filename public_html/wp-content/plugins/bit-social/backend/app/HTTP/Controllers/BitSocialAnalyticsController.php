<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Model\Account;
use BitApps\Social\Model\Log;
use BitApps\Social\Model\Schedule;

final class BitSocialAnalyticsController
{
    public function filterTrackingData($additional_data)
    {
        // Add plugin data to the tracking data

        $accountsData = Account::get(['platform', 'account_type', 'status']);
        $activeAccountCount = Account::where('status', true)->count();
        $publishedPostCount = Log::where('status', true)->count();
        $activeScheduleCount = Schedule::where('status', true)->count();
        $totalShareNowCount = Schedule::where('schedule_type', Schedule::scheduleType['DIRECT_SHARE'])->count();

        $additional_data['accounts'] = json_decode(wp_json_encode($accountsData));
        $additional_data['accountSummary'] = $this->accountSummary($accountsData);

        $additional_data['activeAccountCount'] = $activeAccountCount ?? 0;
        $additional_data['publishedPostCount'] = $publishedPostCount ?? 0;
        $additional_data['activeScheduleCount'] = $activeScheduleCount ?? 0;
        $additional_data['totalShareNowCount'] = $totalShareNowCount ?? 0;

        return $additional_data;
    }

    private function accountSummary($accountsData)
    {
        $summary = [];
        $platformSummary = [];
        if (\count($accountsData) > 0) {
            foreach ($accountsData as $account) {
                $platform = $account['platform'];

                if (!isset($platformSummary[$platform])) {
                    $platformSummary[$platform] = 0;
                }

                $platformSummary[$platform]++;
            }
        }
        $summary['platformSummary'] = $platformSummary;
        $summary['totalAccounts'] = \count($accountsData);

        return $summary;
    }
}
