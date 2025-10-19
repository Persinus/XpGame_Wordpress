<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\Model\Log;

final class LogController
{
    public function index(Request $request, $page, $limit)
    {
        $logs = Log::with('schedule');

        if ($request->date != 'all') {
            if ($dateRange = json_decode(stripslashes($request->date))) {
                $logs->whereBetween('DATE(`created_at`)', $dateRange[0], $dateRange[1]);
            }
        }
        if ($request->schedule != 'all' && isset($request->schedule)) {
            $logs->where('schedule_id', $request->schedule);
        }
        if ($request->status != 'all' && isset($request->status)) {
            $logs->where('status', $request->status);
        }
        if ($request->platform != 'all' && $request->platform) {
            $logs->where('platform', $request->platform);
        }

        $logs = $logs
            ->desc()
            ->paginate($page, $limit);

        return Response::success($logs);
    }

    public function destroy(Request $request)
    {
        Log::whereIn('id', $request->logIds)->delete();

        return Response::success('Selected logs deleted');
    }
}
