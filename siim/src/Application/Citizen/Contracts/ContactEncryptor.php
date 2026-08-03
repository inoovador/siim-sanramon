<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Contracts;

interface ContactEncryptor
{
    public function encrypt(string $plaintext): string;

    public function decrypt(string $ciphertext): string;

    public function cipher(): string;
}
