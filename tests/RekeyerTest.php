<?php

namespace Titoshadow\AnsibleVault\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Exception;
use Titoshadow\AnsibleVault\CommandExecutor;
use Titoshadow\AnsibleVault\Rekeyer;
use Titoshadow\AnsibleVault\Exception\AnsibleVaultNotFoundException;

#[CoversClass('Titoshadow\AnsibleVault\Rekeyer')]
#[CoversMethod('Titoshadow\AnsibleVault\Rekeyer', 'rekey')]
#[UsesClass('Titoshadow\AnsibleVault\Util\PasswordFileHelper')]
class RekeyerTest extends TestCase {

    public function testCanRekeyAVaultWithOldAndNewPasswords(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_to_rekey.yml';
        $oldPassword = 'old_password';
        $newPassword = 'new_password';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(
                $this->callback(function (array $command) use ($vaultPath) {
                    return in_array('ansible-vault', $command) &&
                        in_array('rekey', $command) &&
                        in_array('--vault-password-file', $command) &&
                        in_array('--new-vault-password-file', $command) &&
                        in_array($vaultPath, $command);
                })
            );
            $rekeyer = new Rekeyer($executor);
            $this->assertTrue($rekeyer->rekey($vaultPath, $oldPassword, $newPassword));
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


    public function testCanRekeyAVaultWithAPasswordFileAndNewPassword(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_to_rekey_file.yml';
        $passwordFile = __DIR__ . '/temp_password_file_rekey.txt';
        $newPassword = 'new_password';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(
                $this->callback(function (array $command) use ($vaultPath, $passwordFile) {
                    return in_array('ansible-vault', $command) &&
                        in_array('rekey', $command) &&
                        in_array('--vault-password-file', $command) &&
                        in_array($passwordFile, $command) &&
                        in_array('--new-vault-password-file', $command) &&
                        in_array($vaultPath, $command);
                })
            );
            $rekeyer = new Rekeyer($executor);
            $this->assertTrue($rekeyer->rekey($vaultPath, newPassword: $newPassword, vaultPasswordFile: $passwordFile));
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


    public function testCanRekeyAVaultWithOldPasswordAndANewPasswordFile(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_to_rekey_file_new.yml';
        $oldPassword = 'old_password';
        $newPasswordFile = __DIR__ . '/temp_new_password_file_rekey.txt';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(
                $this->callback(function (array $command) use ($vaultPath, $newPasswordFile) {
                    return in_array('ansible-vault', $command) &&
                        in_array('rekey', $command) &&
                        in_array('--vault-password-file', $command) &&
                        in_array('--new-vault-password-file', $command) &&
                        in_array($newPasswordFile, $command) &&
                        in_array($vaultPath, $command);
                })
            );
            $rekeyer = new Rekeyer($executor);
            $this->assertTrue($rekeyer->rekey($vaultPath, $oldPassword, newVaultPasswordFile: $newPasswordFile));
            $this->assertTrue(true);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($vaultPath)) {
                unlink($vaultPath);
            }
            if (file_exists($newPasswordFile)) {
                unlink($newPasswordFile);
            }
        }
    }


    public function testCanRekeyAVaultWithAPasswordFileAndANewPasswordFile(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_to_rekey_both_files.yml';
        $passwordFile = __DIR__ . '/temp_password_file_rekey_both.txt';
        $newPasswordFile = __DIR__ . '/temp_new_password_file_rekey_both.txt';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(
                $this->callback(function (array $command) use ($vaultPath, $passwordFile, $newPasswordFile) {
                    return in_array('ansible-vault', $command) &&
                        in_array('rekey', $command) &&
                        in_array('--vault-password-file', $command) &&
                        in_array($passwordFile, $command) &&
                        in_array('--new-vault-password-file', $command) &&
                        in_array($newPasswordFile, $command) &&
                        in_array($vaultPath, $command);
                })
            );
            $rekeyer = new Rekeyer($executor);
            $this->assertTrue($rekeyer->rekey($vaultPath, vaultPasswordFile: $passwordFile, newVaultPasswordFile: $newPasswordFile));
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
            if (file_exists($newPasswordFile)) {
                unlink($newPasswordFile);
            }
        }
    }


    public function testCanRekeyAVaultWithVaultId(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_to_rekey_id.yml';
        $oldPassword = 'old_password';
        $newPassword = 'new_password';
        $vaultId = 'my_vault@prompt';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(
                $this->callback(function (array $command) use ($vaultPath, $vaultId) {
                    return in_array('ansible-vault', $command) &&
                        in_array('rekey', $command) &&
                        in_array('--vault-password-file', $command) &&
                        in_array('--new-vault-password-file', $command) &&
                        in_array('--vault-id', $command) &&
                        in_array($vaultId, $command) &&
                        in_array($vaultPath, $command);
                })
            );
            $rekeyer = new Rekeyer($executor);
            $this->assertTrue($rekeyer->rekey($vaultPath, $oldPassword, $newPassword, $vaultId));
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


    public function testCannotRekeyAVaultWithoutAnOldPassword(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_to_rekey_no_password.yml';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $rekeyer = new Rekeyer($executor);
            $this->expectException(InvalidArgumentException::class);
            $rekeyer->rekey($vaultPath);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        }
    }
}