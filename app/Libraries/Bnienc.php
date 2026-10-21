<?php

namespace App\Libraries;

class Bnienc
{
    const TIME_DIFF_LIMIT = 480;

    /**
     * Encrypt data untuk BNI API
     */
    public static function encrypt(array $json_data, $client_id, $secret_key)
    {
        return self::doubleEncrypt(strrev(time()) . '.' . json_encode($json_data), $client_id, $secret_key);
    }

    /**
     * Decrypt data dari BNI API
     */
    public static function decrypt($encrypted_string, $client_id, $secret_key)
    {
        $parsed_string = self::doubleDecrypt($encrypted_string, $client_id, $secret_key);
        
        if (!$parsed_string) {
            return null;
        }
        
        list($timestamp, $data) = array_pad(explode('.', $parsed_string, 2), 2, null);
        
        if (self::tsDiff(strrev($timestamp)) === true) {
            return json_decode($data, true);
        }
        
        return null;
    }

    /**
     * Cek perbedaan timestamp
     */
    private static function tsDiff($timestamp)
    {
        return abs($timestamp - time()) <= self::TIME_DIFF_LIMIT;
    }

    /**
     * Double encryption sesuai standar BNI
     */
    private static function doubleEncrypt($string, $key1, $key2)
    {
        $result = self::encryptSingle($string, $key1);
        $result = self::encryptSingle($result, $key2);
        return strtr(rtrim(base64_encode($result), '='), '+/', '-_');
    }

    /**
     * Single encryption
     */
    private static function encryptSingle($string, $key)
    {
        $result = '';
        $str_len = strlen($string);
        $key_len = strlen($key);
        
        for ($i = 0; $i < $str_len; $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % $key_len) - 1, 1);
            $char = chr((ord($char) + ord($keychar)) % 128);
            $result .= $char;
        }
        
        return $result;
    }

    /**
     * Double decryption sesuai standar BNI
     */
    private static function doubleDecrypt($string, $key1, $key2)
    {
        try {
            // Decode base64 dengan padding yang benar
            $string = str_pad($string, ceil(strlen($string) / 4) * 4, '=', STR_PAD_RIGHT);
            $string = strtr($string, '-_', '+/');
            $decoded = base64_decode($string, true);
            
            if ($decoded === false) {
                throw new \Exception('Base64 decode failed');
            }
            
            $result = self::decryptSingle($decoded, $key1);
            $result = self::decryptSingle($result, $key2);
            
            return $result;
        } catch (\Exception $e) {
            \Log::error('BNI Decryption failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Single decryption
     */
    private static function decryptSingle($string, $key)
    {
        $result = '';
        $str_len = strlen($string);
        $key_len = strlen($key);
        
        for ($i = 0; $i < $str_len; $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % $key_len) - 1, 1);
            $char = chr(((ord($char) - ord($keychar)) + 256) % 128);
            $result .= $char;
        }
        
        return $result;
    }

    /**
     * Helper untuk debug encryption
     */
    public static function debugEncryption($data, $client_id, $secret_key)
    {
        $encrypted = self::encrypt($data, $client_id, $secret_key);
        $decrypted = self::decrypt($encrypted, $client_id, $secret_key);
        
        return [
            'original' => $data,
            'encrypted' => $encrypted,
            'decrypted' => $decrypted,
            'encrypted_length' => strlen($encrypted),
            'match' => $data == $decrypted
        ];
    }
}