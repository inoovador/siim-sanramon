<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Llm;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\IpUtils;

final class NvidiaTransportPolicy
{
    public static function assertSafe(string $baseUrl, bool $verifySsl, string $environment): void
    {
        $parts = parse_url($baseUrl);
        if (! is_array($parts) || ($parts['scheme'] ?? null) !== 'https' || ! is_string($parts['host'] ?? null)) {
            throw new InvalidArgumentException('NVIDIA base URL must use HTTPS.');
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new InvalidArgumentException('NVIDIA base URL must not contain credentials.');
        }

        $host = strtolower(trim($parts['host'], '[]'));
        if ($host === 'localhost' || str_ends_with($host, '.localhost')) {
            throw new InvalidArgumentException('NVIDIA base URL host is not allowed.');
        }

        if (self::looksLikeAlternativeIpLiteral($host)) {
            throw new InvalidArgumentException('NVIDIA base URL IP address format is not allowed.');
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false && self::isRestrictedIp($host)) {
            throw new InvalidArgumentException('NVIDIA base URL IP address is not public.');
        }

        if (! $verifySsl && ! in_array($environment, ['local', 'testing'], true)) {
            throw new InvalidArgumentException('TLS verification can only be disabled locally or in tests.');
        }
    }

    private static function isRestrictedIp(string $ip): bool
    {
        return IpUtils::checkIp($ip, [
            '0.0.0.0/8',
            '10.0.0.0/8',
            '100.64.0.0/10',
            '127.0.0.0/8',
            '169.254.0.0/16',
            '172.16.0.0/12',
            '192.0.0.0/24',
            '192.0.2.0/24',
            '192.88.99.0/24',
            '192.168.0.0/16',
            '198.18.0.0/15',
            '198.51.100.0/24',
            '203.0.113.0/24',
            '224.0.0.0/4',
            '240.0.0.0/4',
            '::/128',
            '::1/128',
            '::ffff:0:0/96',
            '100::/64',
            '2001::/23',
            '2001:db8::/32',
            '2002::/16',
            'fc00::/7',
            'fe80::/10',
            'ff00::/8',
        ]);
    }

    private static function looksLikeAlternativeIpLiteral(string $host): bool
    {
        $numericPart = '(?:0x[0-9a-f]+|0[0-7]+|[0-9]+)';

        return filter_var($host, FILTER_VALIDATE_IP) === false
            && preg_match("/\\A{$numericPart}(?:\\.{$numericPart}){0,3}\\z/iD", $host) === 1;
    }
}
