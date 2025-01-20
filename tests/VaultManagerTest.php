<?php

namespace Titoshadow\AnsibleVault\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Exception;
use Titoshadow\AnsibleVault\CommandExecutor;
use Titoshadow\AnsibleVault\VaultManager;
use Titoshadow\AnsibleVault\Exception\AnsibleVaultNotFoundException;

class VaultManagerTest extends TestCase {

    public function testCanCreateAnEncryptedVaultWithAPassword(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_encrypted.yml';
        $password = 'test_password';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(
                $this->callback(function (array $command) use ($vaultPath, $password) {
                    return in_array('ansible-vault', $command) &&
                        in_array('create', $command) &&
                        in_array('--new-vault-password', $command) &&
                        in_array($password, $command) &&
                        in_array($vaultPath, $command);
                })
            );
            $vaultManager = new VaultManager($executor);
            $this->assertTrue($vaultManager->create($vaultPath, $password));
            $this->assertTrue(true);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($vaultPath)) {
                unlink($vaultPath);
            }
        }

    }

    public function testCanCreateAnEncryptedVaultWithAPasswordFile(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_encrypted_file.yml';
        $passwordFile = __DIR__ . '/temp_password_file.txt';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(
                $this->callback(function (array $command) use ($vaultPath, $passwordFile) {
                    return in_array('ansible-vault', $command) &&
                        in_array('create', $command) &&
                        in_array('--new-vault-password-file', $command) &&
                        in_array($passwordFile, $command) &&
                        in_array($vaultPath, $command);
                })
            );
            $vaultManager = new VaultManager($executor);
            $this->assertTrue($vaultManager->create($vaultPath, vaultPasswordFile: $passwordFile));
            $this->assertTrue(true);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($vaultPath)) {
                unlink($vaultPath);
            }
            if (file_exists($passwordFile)) {
                unlink($passwordFile);
            }
        }
    }

    public function testCanCreateAnUnencryptedVault(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_unencrypted.yml';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(
                $this->callback(function (array $command) use ($vaultPath) {
                    return in_array('ansible-vault', $command) &&
                        in_array('create', $command) &&
                        !in_array('--new-vault-password', $command) &&
                        !in_array('--new-vault-password-file', $command) &&
                        in_array($vaultPath, $command);
                })
            );
            $vaultManager = new VaultManager($executor);
            $this->assertTrue($vaultManager->create($vaultPath, encrypted: false));
            $this->assertTrue(true);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($vaultPath)) {
                unlink($vaultPath);
            }
        }
    }

    public function testCannotCreateAnEncryptedVaultWithoutAPassword(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_no_password.yml';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $vaultManager = new VaultManager($executor);
            $this->expectException(InvalidArgumentException::class);
            $vaultManager->create($vaultPath);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        }
    }

    public function testCanRemoveAVault(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_to_remove.yml';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(['rm', $vaultPath]);
            $vaultManager = new VaultManager($executor);
            $this->assertTrue($vaultManager->remove($vaultPath));
            $this->assertTrue(true);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($vaultPath)) {
                unlink($vaultPath);
            }
        }
    }
}