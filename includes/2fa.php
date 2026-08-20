<?php
// Seed2Greens - Two-Factor Authentication (TOTP)
// RFC 6238 / Google Authenticator compatible
// No external dependencies required

class TwoFactorAuth {
    const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    const TIME_STEP = 30;
    const DIGITS = 6;
    const WINDOW = 1;

    public static function generateSecret($length = 20) {
        $bytes = random_bytes($length);
        return self::base32Encode($bytes);
    }

    public static function base32Encode($data) {
        $binary = '';
        if (is_string($data)) {
            $binary = $data;
        } else {
            foreach ($data as $byte) {
                $binary .= chr($byte);
            }
        }

        $output = '';
        $buffer = 0;
        $bufferBits = 0;

        for ($i = 0; $i < strlen($binary); $i++) {
            $buffer = ($buffer << 8) | ord($binary[$i]);
            $bufferBits += 8;

            while ($bufferBits >= 5) {
                $output .= self::BASE32_ALPHABET[($buffer >> ($bufferBits - 5)) & 0x1F];
                $bufferBits -= 5;
            }
        }

        if ($bufferBits > 0) {
            $output .= self::BASE32_ALPHABET[($buffer << (5 - $bufferBits)) & 0x1F];
        }

        while (strlen($output) % 8 !== 0) {
            $output .= '=';
        }

        return $output;
    }

    public static function base32Decode($base32) {
        $base32 = strtoupper(str_replace('=', '', $base32));
        $output = '';
        $buffer = 0;
        $bufferBits = 0;

        for ($i = 0; $i < strlen($base32); $i++) {
            $char = $base32[$i];
            $val = strpos(self::BASE32_ALPHABET, $char);
            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bufferBits += 5;

            if ($bufferBits >= 8) {
                $output .= chr(($buffer >> ($bufferBits - 8)) & 0xFF);
                $bufferBits -= 8;
            }
        }

        return $output;
    }

    public static function generateTOTP($secret, $time = null) {
        if ($time === null) {
            $time = time();
        }

        $secretBytes = self::base32Decode($secret);
        $timeStep = (int) floor($time / self::TIME_STEP);

        $timeBytes = pack('N*', $timeStep);
        $hash = hash_hmac('sha1', $timeBytes, $secretBytes, true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = (ord($hash[$offset]) & 0x7F) << 24 |
                  (ord($hash[$offset + 1]) & 0xFF) << 16 |
                  (ord($hash[$offset + 2]) & 0xFF) << 8 |
                  (ord($hash[$offset + 3]) & 0xFF);

        $otp = $binary % 1000000;
        return str_pad((string) $otp, self::DIGITS, '0', STR_PAD_LEFT);
    }

    public static function verifyTOTP($secret, $code, $window = null) {
        if ($window === null) {
            $window = self::WINDOW;
        }

        $code = preg_replace('/\s/', '', $code);
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $time = time();
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::generateTOTP($secret, $time + ($i * self::TIME_STEP)), $code)) {
                return true;
            }
        }

        return false;
    }

    public static function getProvisioningUri($username, $secret, $issuer = 'Seed2Greens') {
        $params = [
            'secret' => $secret,
            'issuer' => $issuer,
            'period' => self::TIME_STEP,
            'digits' => self::DIGITS,
            'algorithm' => 'SHA1',
        ];

        $query = http_build_query($params);
        $label = rawurlencode($issuer) . ':' . rawurlencode($username);
        return 'otpauth://totp/' . $label . '?' . $query;
    }

    public static function getQrCodeUrl($username, $secret, $issuer = 'Seed2Greens', $size = 200) {
        $uri = self::getProvisioningUri($username, $secret, $issuer);
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/';
        return $apiUrl . '?size=' . $size . 'x' . $size . '&data=' . urlencode($uri);
    }

    public static function generateBackupCodes($count = 10, $length = 10) {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $bytes = random_bytes($length);
            $codes[] = strtoupper(bin2hex($bytes));
        }
        return $codes;
    }

    public static function hashBackupCode($code) {
        return password_hash($code, PASSWORD_DEFAULT);
    }

    public static function verifyBackupCode($code, $hashedCodes) {
        if (!is_array($hashedCodes)) {
            $hashedCodes = json_decode($hashedCodes, true) ?: [];
        }

        foreach ($hashedCodes as $index => $hashed) {
            if (password_verify($code, $hashed)) {
                return $index;
            }
        }
        return false;
    }

    public static function removeBackupCode(&$hashedCodes, $index) {
        if (isset($hashedCodes[$index])) {
            unset($hashedCodes[$index]);
            $hashedCodes = array_values($hashedCodes);
        }
    }
}
