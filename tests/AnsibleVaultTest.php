<?php

namespace Titoshadow\AnsibleVault\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Titoshadow\AnsibleVault\AnsibleVault;
use Titoshadow\AnsibleVault\CommandExecutor;
use Titoshadow\AnsibleVault\Exception\AnsibleVaultNotFoundException;
use Titoshadow\AnsibleVault\Exception\VaultExecutionException;

#[CoversClass(AnsibleVault::class)]
#[UsesClass('Titoshadow\AnsibleVault\Exception\VaultExecutionException')]
#[UsesClass('Titoshadow\AnsibleVault\Editor')]
#[UsesClass('Titoshadow\AnsibleVault\Encrypter')]
#[UsesClass('Titoshadow\AnsibleVault\Rekeyer')]
#[UsesClass('Titoshadow\AnsibleVault\VaultManager')]
class AnsibleVaultTest extends TestCase
{
    public function testThrowsWhenAnsibleVaultNotAvailable(): void
    {
        $executor = $this->createMock(CommandExecutor::class);
        $executor->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $cmd) {
                return in_array('ansible-vault', $cmd, true) && in_array('--version', $cmd, true);
            }))
            ->willThrowException(new VaultExecutionException('not found', 1, '', ''));

        $this->expectException(AnsibleVaultNotFoundException::class);
        new AnsibleVault(null, $executor);
    }

    public function testConstructsWhenAnsibleVaultIsAvailable(): void
    {
        $executor = $this->createMock(CommandExecutor::class);
        $executor->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $cmd) {
                return in_array('ansible-vault', $cmd, true) && in_array('--version', $cmd, true);
            }))
            ->willReturn('ansible-vault 2.x');

        $vault = new AnsibleVault(null, $executor);
        $this->assertInstanceOf(AnsibleVault::class, $vault);
    }

    public function testUsesConfiguredBinaryPath(): void
    {
        $binary = '/custom/bin/ansible-vault';
        $executor = $this->createMock(CommandExecutor::class);
        $executor->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $cmd) use ($binary) {
                return isset($cmd[0]) && $cmd[0] === $binary && in_array('--version', $cmd, true);
            }))
            ->willReturn('ansible-vault 2.x');

        $vault = new AnsibleVault(null, $executor, $binary);
        $this->assertInstanceOf(AnsibleVault::class, $vault);
    }
}
