<?php
/**
 * Simple JWT Helper for RedHope
 * 
 * Note: This is a manual implementation for environment without Composer.
 * For production, a library like lcobucci/jwt or firebase/php-jwt is recommended.
 */

class JWTHelper {
    private static $secret_key = "redhope_secure_secret_777"; // In production, move to .env
    private static $algo = 'HS256';

    public static function generate($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algo]);
        $header_base64 = self::base64UrlEncode($header);
        
        $payload_encoded = json_encode($payload);
        $payload_base64 = self::base64UrlEncode($payload_encoded);
        
        $signature = hash_hmac('sha256', $header_base64 . "." . $payload_base64, self::$secret_key, true);
        $signature_base64 = self::base64UrlEncode($signature);
        
        return $header_base64 . "." . $payload_base64 . "." . $signature_base64;
    }

    public static function verify($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        list($header_base64, $payload_base64, $signature_provided) = $parts;

        $signature_check = hash_hmac('sha256', $header_base64 . "." . $payload_base64, self::$secret_key, true);
        $signature_check_base64 = self::base64UrlEncode($signature_check);

        if ($signature_provided === $signature_check_base64) {
            return json_decode(base64_decode(strtr($payload_base64, '-_', '+/')), true);
        }

        return false;
    }

    private static function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
