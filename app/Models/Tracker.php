<?php

namespace App\Models;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class Tracker
{
    public static function ip(): ?string
    {
        $request = request();
        if (!$request instanceof Request) {
            return null;
        }

        $proxyIp = self::normalizeIp((string) $request->server->get('REMOTE_ADDR', ''));
        if (!$proxyIp) {
            return null;
        }

        if (!self::isTrustedProxy($proxyIp)) {
            return $proxyIp;
        }

        $cfConnectingIp = self::normalizeIp((string) $request->header('cf-connecting-ip', ''));
        if ($cfConnectingIp) {
            return $cfConnectingIp;
        }

        $xff = (string) $request->header('x-forwarded-for', '');
        foreach (explode(',', $xff) as $candidate) {
            $validIp = self::normalizeIp(trim($candidate));
            if ($validIp) {
                return $validIp;
            }
        }

        return $proxyIp;
    }

    public static function proxyIp(): ?string
    {
        $request = request();
        if (!$request instanceof Request) {
            return null;
        }

        return self::normalizeIp((string) $request->server->get('REMOTE_ADDR', ''));
    }

    public static function cfRay(): ?string
    {
        $request = request();
        if (!$request instanceof Request) {
            return null;
        }

        $cfRay = trim((string) $request->header('cf-ray', ''));

        return $cfRay !== '' ? $cfRay : null;
    }

    private static function isTrustedProxy(string $ip): bool
    {
        $trustedProxies = config('access.trusted_proxies', []);
        if (!is_array($trustedProxies) || $trustedProxies === []) {
            return false;
        }

        return IpUtils::checkIp($ip, $trustedProxies);
    }

    private static function normalizeIp(string $ip): ?string
    {
        $ip = trim($ip);
        if ($ip === '') {
            return null;
        }

        // Normaliza IPv4 mapeado em IPv6.
        if (str_starts_with(strtolower($ip), '::ffff:')) {
            $ipv4 = substr($ip, 7);
            if (filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return $ipv4;
            }
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return null;
        }

        return $ip;
    }
}
