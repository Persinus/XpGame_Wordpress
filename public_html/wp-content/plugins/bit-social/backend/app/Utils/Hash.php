<?php

namespace BitApps\Social\Utils;

use BitApps\Social\Config;

class Hash
{
    private static $_cipher = 'aes-256-cbc';

    public static function encrypt($data)
    {
        $secretKey = Config::getOption('secret_key');
        if (!$secretKey) {
            Config::updateOption('secret_key', Config::SLUG . time(), true);
            $secretKey = Config::getOption('secret_key');
        }
        $ivlen = openssl_cipher_iv_length(self::$_cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($data, self::$_cipher, $secretKey, 0, $iv);

        return base64_encode($iv . $ciphertext);
    }

    public static function decrypt($encryptedData)
    {
        $secretKey = Config::getOption('secret_key');

        if ($secretKey) {
            $decode = base64_decode($encryptedData);
            $ivlen = openssl_cipher_iv_length(self::$_cipher);
            $iv = substr($decode, 0, $ivlen);

            $ciphertext = substr($decode, $ivlen);

            return openssl_decrypt($ciphertext, self::$_cipher, $secretKey, 0, $iv);
        }
    }
}
