<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Config;
use Exception;

final class DebugLogController
{
    private $logFilePath;

    public function __construct()
    {
        $this->logFilePath = WP_CONTENT_DIR . '/debug.log';
    }

    public function get()
    {
        try {
            if (!file_exists($this->logFilePath)) {
                wp_send_json_error(['message' => 'Debug file not found']);
            }

            $logData = $this->loadLogs();

            wp_send_json_success([
                'logs'        => $logData['logs'] ?? '',
                'error_types' => $logData['unique_error_types'] ?? '',
                'file_size'   => $this->getFilesize(),
            ]);
        } catch (Exception $e) {
            wp_send_json_success([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getFilesize()
    {
        return file_exists($this->logFilePath) ? filesize($this->logFilePath) : false;
    }

    public function readLargeFile()
    {
        $handle = fopen($this->logFilePath, 'r');

        try {
            while ($line = fgets($handle)) {
                yield $line;
            }
        } finally {
            fclose($handle);
        }
    }

    public function loadLogs($limit = false)
    {
        if (!file_exists($this->logFilePath)) {
            return [];
        }

        $logs = [];
        $errorTypes = []; // Initialize error types array
        $i = 0;

        foreach ($this->readLargeFile() as $line) {
            $logEntry = $this->parseLogLine($line);

            if ($logEntry !== false) {
                $logs[] = $logEntry;

                // Extract and store error type
                if (!empty($logEntry['error_type'])) {
                    $errorTypes[$logEntry['error_type']] = true;
                }

                $i++; // Increment the counter

                if ($limit && $i >= $limit) {
                    break;
                }
            }
        }

        $uniqueErrorTypes = array_keys($errorTypes);

        return [
            'logs'               => array_reverse($logs),
            'unique_error_types' => $uniqueErrorTypes,
        ];
    }

    public function clear()
    {
        if (file_exists($this->logFilePath)) {
            $open = fopen($this->logFilePath, 'r+');

            if (!$open) {
                $msg = 'Could not open file!';
            } else {
                file_put_contents($this->logFilePath, '');
                $msg = 'Log cleared';
            }
        } else {
            $msg = 'No log file yet available!';
        }

        wp_send_json_success($msg);
    }

    public function gmt_to_local_timestamp($gmt_timestamp)
    {
        $iso_date = strtotime('Y-m-d H:i:s', $gmt_timestamp);

        return get_date_from_gmt($iso_date, 'Y-m-d h:i a');
    }

    private function parseLogLine($line)
    {
        $sep = '$!$';
        $line = preg_replace("/^\[([0-9a-zA-Z-]+) ([0-9:]+) ([a-zA-Z_\/]+)\] (.*)$/i", '$1' . $sep . '$2' . $sep . '$3' . $sep . '$4', $line);
        $parts = explode($sep, $line);

        if (\count($parts) >= 4) {
            $info = stripslashes($parts[3]);
            $time = strtotime($parts[1]);

            $pluginName = $this->extractPluginName($info);

            if ($pluginName !== Config::SLUG) {
                return false;
            }

            $errorType = $this->extractErrorType($line);

            return [
                'date'        => date('d/m/y', strtotime($parts[0])),
                'time'        => human_time_diff($time, current_time('U')) . ' ago',
                'timezone'    => $parts[2],
                'details'     => $info,
                'error_type'  => $errorType,
                'plugin_name' => ucwords(str_replace('-', ' ', $pluginName)),
            ];
        }

        return false;
    }

    private function extractErrorType($logLine)
    {
        $pattern = '/PHP (Fatal error|Notice|Warning):/';
        if (preg_match($pattern, $logLine, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    private function extractPluginName($info)
    {
        $pluginName = '';
        if (preg_match('/\/plugins\/([^\/]+)\//', $info, $pluginMatches)) {
            $pluginName = $pluginMatches[1];
            if (!file_exists(WP_PLUGIN_DIR . '/' . $pluginName . '/' . $pluginName . '.php')) {
                $pluginName = '';
            }
        }

        return $pluginName;
    }
}
