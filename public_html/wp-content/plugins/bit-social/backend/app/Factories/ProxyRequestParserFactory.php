<?php

namespace BitApps\Social\Factories;

use BitApps\Social\Utils\Hash;

/**
 * Class ProxyRequestParserFactory
 * Handles the parsing and encryption/decryption of request data.
 */
class ProxyRequestParserFactory
{
    /**
     * Parses the given request array and processes headers, query parameters, and body parameters.
     *
     * @param array $request The request array containing headers, queryParams, and bodyParams.
     *
     * @return array The parsed request array.
     */
    public static function parse(array $request): array
    {
        if (isset($request['headers'])) {
            $request['headers'] = self::parseArrayValue($request['headers']);
        }
        if (isset($request['queryParams'])) {
            $request['queryParams'] = self::parseArrayValue($request['queryParams']);
        }
        if (isset($request['bodyParams'])) {
            $request['bodyParams'] = self::parseArrayValue($request['bodyParams']);
        }

        return $request;
    }

    /**
     * Recursively parses array values, processing encryption if specified.
     *
     * @param mixed $data The data to be parsed (array or scalar).
     *
     * @return mixed The parsed data.
     */
    private static function parseArrayValue($data)
    {
        if (!\is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $item) {
            if (!\is_array($item)) {
                continue;
            }
            if (!isset($item['encryption'])) {
                $data[$key] = implode('', self::parseArrayValue($item));

                continue;
            }
            $data[$key] = self::processEncryption(self::parseArrayValue($item));
        }

        return $data;
    }

    /**
     * Processes encryption or decryption based on the specified encryption type.
     *
     * @param array $data The data array containing 'encryption' type and 'value'.
     *
     * @return mixed The encrypted or decrypted value, or the original value if encryption type is unknown.
     */
    private static function processEncryption(array $data)
    {
        $value = $data['value'];

        switch ($data['encryption']) {
            case 'base64_encode':
                return base64_encode($value);
            case 'base64_decode':
                return base64_decode($value);
            case 'hmac_decrypt':
                return Hash::decrypt($value);
            case 'hmac_encrypt':
                return Hash::encrypt($value);
            default:
                return $value;
        }
    }
}
