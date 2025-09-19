<?php

namespace Titoshadow\AnsibleVault\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Titoshadow\AnsibleVault\Util\VaultDetection;

#[CoversClass(VaultDetection::class)]
class VaultDetectionTest extends TestCase
{
    public function testIsEncryptedString(): void
    {
        $this->assertTrue(VaultDetection::isEncryptedString('$ANSIBLE_VAULT;1.1;AES256;...'));
        $this->assertFalse(VaultDetection::isEncryptedString('plain text'));
    }

    public function testIsEncryptedFile(): void
    {
        $enc = __DIR__ . '/temp_enc.vault';
        $plain = __DIR__ . '/temp_plain.txt';

        file_put_contents($enc, '$ANSIBLE_VAULT;1.1;AES256' . PHP_EOL . '...');
        file_put_contents($plain, 'plain text');

        $this->assertTrue(VaultDetection::isEncryptedFile($enc));
        $this->assertFalse(VaultDetection::isEncryptedFile($plain));
        $this->assertFalse(VaultDetection::isEncryptedFile(__DIR__ . '/does_not_exist'));

        @unlink($enc);
        @unlink($plain);
    }
}
