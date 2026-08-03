<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Security;

use Illuminate\Encryption\Encrypter;
use InvalidArgumentException;
use SIIM\Application\Citizen\Contracts\ContactEncryptor;

final readonly class GcmContactEncryptor implements ContactEncryptor
{
    private Encrypter $encrypter;

    public function __construct(string $appKey)
    {
        $key = str_starts_with($appKey, 'base64:') ? base64_decode(substr($appKey, 7), true) : $appKey;
        if (! is_string($key) || strlen($key) !== 32) {
            throw new InvalidArgumentException('APP_KEY must be a valid 32-byte key.');
        }
        $this->encrypter = new Encrypter($key, 'AES-256-GCM');
    }

    public function encrypt(string $plaintext): string
    {
        return $this->encrypter->encryptString($plaintext);
    }

    public function decrypt(string $ciphertext): string
    {
        return $this->encrypter->decryptString($ciphertext);
    }

    public function cipher(): string
    {
        return 'AES-256-GCM';
    }
}
