<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Helpers\Arr;
use BitApps\Social\Deps\BitApps\WPKit\Http\Client\HttpClient;
use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\Model\Account;
use BitApps\Social\Model\Group;
use BitApps\Social\Model\Schedule;

class AccountController
{
    private $httpHandler;

    public function __construct()
    {
        $this->httpHandler = new HttpClient();
    }

    public function index(Request $request)
    {
        $validatedData = (object) $request->validate([
            'search' => ['nullable', 'string'],
            'type'   => ['nullable', 'string'],
            'value'  => ['nullable', 'string'],
        ]);

        $search = $validatedData->search ?? null;
        $type = $validatedData->type ?? null;
        $value = $validatedData->value ?? null;

        if (!empty($search)) {
            $accountModel = Account::where('account_name', 'like', "%{$search}%");
        }
        if (!empty($type) && $type === 'platform' && $value !== 'all') {
            $accountModel = Account::where('platform', $value);
        }

        if (!isset($accountModel)) {
            $accountModel = new Account();
        }

        $allPageAndGroup = $accountModel->get(['id', 'account_id', 'details', 'platform', 'account_type', 'status']);

        return Response::success($allPageAndGroup);
    }

    public function store(Request $request)
    {
        $accountData = $request->accountData;

        $res = Account::insert($accountData);

        return Response::SUCCESS(['account' => $res, 'message' => 'Account connect successfully']);
    }

    public function destroy(Account $account)
    {
        $scheduleIds = $this->findScheduleByAccountId($account->id);

        if (!empty($scheduleIds)) {
            $this->accountRemoveFromSchedules($account->id, $scheduleIds);
        }

        $autoPostSettings = Config::getOption('auto_post_settings');

        if (!empty($autoPostSettings['accounts']['accountIds'])) {
            $autoPostSettings['accounts']['accountIds'] = array_values(
                array_filter(
                    $autoPostSettings['accounts']['accountIds'],
                    function ($id) use ($account) {
                        return $id !== $account->id;
                    }
                )
            );

            Config::updateOption('auto_post_settings', $autoPostSettings);
        }

        $account->delete();

        return Response::success('Accounts deleted');
    }

    public function updateStatus(Request $request, Account $account)
    {
        $validatedData = (object) $request->validate([
            'status' => ['nullable', 'integer']
        ]);

        $account->update(['status' => $validatedData->status]);
        if ($account->save()) {
            return Response::success('Account status updated');
        }

        return Response::error('Account status update failed');
    }

    public static function isExists($id)
    {
        return Account::findOne(['id' => $id, 'status' => Account::ACCOUNT_STATUS['active']]);
    }

    public function accountRemoveFromGroup($id, $value)
    {
        $group = Group::findOne(['id' => $id]);
        $groupAccountIds = explode(',', $group->account_ids);
        if (($key = array_search($value, $groupAccountIds)) !== false) {
            unset($groupAccountIds[$key]);
        }
        $updateAccountId = implode(', ', $groupAccountIds);
        $group->account_ids = $updateAccountId;

        return $group->save();
    }

    public function findScheduleByAccountId($accountId)
    {
        $schedules = Schedule::get(['id', 'config']);
        $schedulesIds = [];

        if (\is_array($schedules)) {
            foreach ($schedules as $schedule) {
                if (isset($schedule->config['accounts']['accountIds'])) {
                    $accountIds = $schedule->config['accounts']['accountIds'];
                }

                $isAccount = array_search($accountId, $accountIds);

                if ($isAccount !== false) {
                    $schedulesIds[] = $schedule->id;
                }
            }
        }

        return $schedulesIds;
    }

    public function accountRemoveFromSchedules($accountId, $scheduleIds)
    {
        $table = Config::get('WP_DB_PREFIX') . Config::VAR_PREFIX . 'schedules';
        $cases = [];
        $ids = [];
        $placeholders = '';
        $variables = '';

        if (\count($scheduleIds) >= 1) {
            $schedules = Schedule::whereIn('id', $scheduleIds)->get();

            foreach ($schedules as $schedule) {
                $scheduleConfig = $schedule['config'];
                $scheduleConfig['accounts']['accountIds'] = array_values(
                    array_diff($scheduleConfig['accounts']['accountIds'], [$accountId])
                );

                $cases[] = 'WHEN id = %d THEN %s';
                $ids[] = $schedule->id;
                $placeholders .= '%d';
                $variables .= $schedule->id . '${bs}' . json_encode($scheduleConfig);

                if (end($schedules) !== $schedule) {
                    $variables .= '${bs}';
                    $placeholders .= ',';
                }
            }

            $values = array_merge(explode('${bs}', $variables), $ids);
            $cases = implode(' ', $cases);

            $query = "UPDATE {$table} SET config = CASE {$cases} END WHERE id IN ({$placeholders})";

            return Schedule::raw($query, $values);
        }
    }

    public function accountPlatform(Request $request)
    {
        $arr = new Arr();

        $accountIds = $request->ids;
        if (\count($accountIds) >= 1) {
            $platformName = Account::select(['platform'])->whereIn('id', $accountIds)->get();
            $arrayPluck = $arr->pluck($platformName, 'platform');
            $result = array_values(array_unique($arrayPluck));

            if (\count($result) > 1) {
                array_unshift($result, 'all');
            }
            if ($platformName) {
                return Response::success($result);
            }
        }

        return Response::success([]);
    }

    public function activeAccounts()
    {
        $activeAccounts = Account::where('status', Account::ACCOUNT_STATUS['active'])
            ->get(['id', 'details', 'platform', 'account_type', 'status']);

        return Response::success($activeAccounts);
    }

    public function platformsCredentials()
    {
        $platformsCredentialsUrl = 'https://auth-apps.bitapps.pro/apps/all';
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $platformsCredentials = $this->httpHandler->request($platformsCredentialsUrl, 'GET', [], $headers);

        if ($platformsCredentials->clientInfo) {
            return Response::success($platformsCredentials);
        }

        return Response::error(['data' => null,  'message' => 'Data not found']);
    }
}
