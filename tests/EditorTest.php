<?php

namespace Titoshadow\AnsibleVault\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Titoshadow\AnsibleVault\CommandExecutor;
use Titoshadow\AnsibleVault\Editor;
use Titoshadow\AnsibleVault\Exception\AnsibleVaultNotFoundException;

#[CoversClass('Titoshadow\AnsibleVault\Editor')]
#[CoversMethod('Titoshadow\AnsibleVault\Editor', 'edit')]
#[UsesClass('Titoshadow\AnsibleVault\Util\PasswordFileHelper')]
class EditorTest extends TestCase {

    public function testCanEditAnEncryptedVault(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_to_edit.yml';
        $password = 'test_password';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(
                $this->callback(function (array $command) use ($vaultPath) {
                    return in_array('ansible-vault', $command) &&
                        in_array('edit', $command) &&
                        in_array('--vault-password-file', $command) &&
                        in_array($vaultPath, $command);
                }),
                null,
                true
            );
            $editor = new Editor($executor);
            $this->assertTrue($editor->edit($vaultPath, $password));
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


    public function testCanEditAnEncryptedVaultWithPasswordFile(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_to_edit_file.yml';
        $passwordFile = __DIR__ . '/temp_password_file_edit.txt';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(
                $this->callback(function (array $command) use ($vaultPath, $passwordFile) {
                    return in_array('ansible-vault', $command) &&
                        in_array('edit', $command) &&
                        in_array('--vault-password-file', $command) &&
                        in_array($passwordFile, $command) &&
                        in_array($vaultPath, $command);
                }),
                null,
                true
            );
            $editor = new Editor($executor);
            $this->assertTrue($editor->edit($vaultPath, vaultPasswordFile: $passwordFile));
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


    public function testCanEditAnEncryptedVaultWithProvidedPasswordFile(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_to_edit_provided_file.yml';
        $passwordFile = __DIR__ . '/temp_provided_password_file_edit.txt';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(
                $this->callback(function (array $command) use ($vaultPath, $passwordFile) {
                    return in_array('ansible-vault', $command) &&
                        in_array('edit', $command) &&
                        in_array('--vault-password-file', $command) &&
                        in_array($passwordFile, $command) &&
                        in_array($vaultPath, $command);
                }),
                null,
                true
            );
            $editor = new Editor($executor);
            $this->assertTrue($editor->edit($vaultPath, vaultPasswordFile: $passwordFile));
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


    public function testCanEditAnEncryptedVaultWithVaultId(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_to_edit_id.yml';
        $password = 'test_password';
        $vaultId = 'my_vault@prompt';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $executor->expects($this->once())->method('execute')->with(
                $this->callback(function (array $command) use ($vaultPath, $vaultId) {
                    return in_array('ansible-vault', $command) &&
                        in_array('edit', $command) &&
                        in_array('--vault-password-file', $command) &&
                        in_array('--vault-id', $command) &&
                        in_array($vaultId, $command) &&
                        in_array($vaultPath, $command);
                }),
                null,
                true
            );
            $editor = new Editor($executor);
            $this->assertTrue($editor->edit($vaultPath, $password, $vaultId));
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


    public function testCannotEditAnEncryptedVaultWithoutAPassword(): void
    {
        $vaultPath = __DIR__ . '/temp_vault_to_edit_no_password.yml';
        try {
            $executor = $this->createMock(CommandExecutor::class);
            $editor = new Editor($executor);
            $this->expectException(InvalidArgumentException::class);
            $editor->edit($vaultPath);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        }
    }
}