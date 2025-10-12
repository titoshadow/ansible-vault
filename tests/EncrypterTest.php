<?php

namespace Titoshadow\AnsibleVault\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Titoshadow\AnsibleVault\CommandExecutor;
use Titoshadow\AnsibleVault\Encrypter;
use Titoshadow\AnsibleVault\Exception\AnsibleVaultNotFoundException;

#[CoversClass('Titoshadow\AnsibleVault\Encrypter')]
#[CoversMethod('Titoshadow\AnsibleVault\Encrypter', 'encrypt')]
#[UsesClass('Titoshadow\AnsibleVault\Util\PasswordFileHelper')]
class EncrypterTest extends TestCase {

    public function testCanEncryptAFileWithAPassword(): void
    {
        $filePath = __DIR__ . '/temp_file_to_encrypt.txt';
        try {
            file_put_contents($filePath, 'sensitive data');
            $password = 'test_password';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('$ANSIBLE_VAULT;1.1;AES256;...');
            $encrypter = new Encrypter($executor);
            $encrypter->encrypt($filePath, $password);
            $this->assertTrue(true);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }


    public function testCanEncryptAFileWithAPasswordFile(): void
    {
        $filePath = __DIR__ . '/temp_file_to_encrypt_file.txt';
        try {
            file_put_contents($filePath, 'sensitive data');
            $passwordFile = __DIR__ . '/temp_password_file_encrypt.txt';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('$ANSIBLE_VAULT;1.1;AES256;...');
            $encrypter = new Encrypter($executor);
            $encrypter->encrypt($filePath, vaultPasswordFile: $passwordFile);
            $this->assertTrue(true);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            if (file_exists($passwordFile)) {
                unlink($passwordFile);
            }
        }
    }


    public function testCanEncryptAFileWithProvidedPasswordFile(): void
    {
        $filePath = __DIR__ . '/temp_file_to_encrypt_provided_file.txt';
        try {
            file_put_contents($filePath, 'sensitive data');
            $passwordFile = __DIR__ . '/temp_provided_password_file_encrypt.txt';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('$ANSIBLE_VAULT;1.1;AES256;...');
            $encrypter = new Encrypter($executor);
            $encrypter->encrypt($filePath, vaultPasswordFile: $passwordFile);
            $this->assertTrue(true);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            if (file_exists($passwordFile)) {
                unlink($passwordFile);
            }
        }
    }


    public function testCanEncryptAFileWithAPasswordAndVaultId(): void
    {
        $filePath = __DIR__ . '/temp_file_to_encrypt_id.txt';
        try {
            file_put_contents($filePath, 'sensitive data');
            $password = 'test_password';
            $vaultId = 'my_vault@prompt';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('$ANSIBLE_VAULT;1.1;AES256;my_vault@prompt...');
            $encrypter = new Encrypter($executor);
            $output = $encrypter->encrypt($filePath, $password, $vaultId);
            $this->assertStringContainsString('my_vault@prompt', $output);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }


    public function testCanEncryptAStringWithAPassword(): void
    {
        $stringToEncrypt = 'super secret string';
        try {
            $password = 'test_password';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('$ANSIBLE_VAULT;1.1;AES256;...');
            $encrypter = new Encrypter($executor);
            $encrypted = $encrypter->encryptString($stringToEncrypt, $password);
            $this->assertStringContainsString('$ANSIBLE_VAULT', $encrypted);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        }
    }


    public function testCanEncryptAStringWithAPasswordFile(): void
    {
        $stringToEncrypt = 'super secret string file';
        try {
            $passwordFile = __DIR__ . '/temp_password_file_encrypt_string.txt';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('$ANSIBLE_VAULT;1.1;AES256;...');
            $encrypter = new Encrypter($executor);
            $encrypted = $encrypter->encryptString($stringToEncrypt, vaultPasswordFile: $passwordFile);
            $this->assertStringContainsString('$ANSIBLE_VAULT', $encrypted);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($passwordFile)) {
                unlink($passwordFile);
            }
        }
    }


    public function testCanEncryptAStringWithProvidedPasswordFile(): void
    {
        $stringToEncrypt = 'super secret string provided file';
        try {
            $passwordFile = __DIR__ . '/temp_provided_password_file_encrypt_string.txt';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('$ANSIBLE_VAULT;1.1;AES256;...');
            $encrypter = new Encrypter($executor);
            $encrypted = $encrypter->encryptString($stringToEncrypt, vaultPasswordFile: $passwordFile);
            $this->assertStringContainsString('$ANSIBLE_VAULT', $encrypted);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($passwordFile)) {
                unlink($passwordFile);
            }
        }
    }


    public function testCanEncryptAStringWithAPasswordAndVaultId(): void
    {
        $stringToEncrypt = 'super secret string with id';
        try {
            $password = 'test_password';
            $vaultId = 'my_vault@prompt';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('$ANSIBLE_VAULT;1.1;AES256;my_vault@prompt...');
            $encrypter = new Encrypter($executor);
            $encrypted = $encrypter->encryptString($stringToEncrypt, $password, $vaultId);
            $this->assertStringContainsString('$ANSIBLE_VAULT', $encrypted);
            $this->assertStringContainsString('my_vault', $encrypted);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        }
    }

    public function testEncryptStringUsesDefaultStdinName(): void
    {
        $stringToEncrypt = 'value';
        $password = 'pwd';
        $executor = $this->createMock(CommandExecutor::class);
        $executor->expects($this->once())->method('execute')->with(
            $this->callback(function (array $command) use ($stringToEncrypt) {
                return in_array('ansible-vault', $command, true)
                    && in_array('encrypt_string', $command, true)
                    && in_array('--name', $command, true)
                    && in_array('secret', $command, true)
                    && in_array($stringToEncrypt, $command, true)
                    && !in_array('--stdin', $command, true);
            })
        )->willReturn('$ANSIBLE_VAULT;1.1;AES256;...');
        $encrypter = new Encrypter($executor);
        $encrypter->encryptString($stringToEncrypt, $password);
        $this->assertTrue(true);
    }

    public function testEncryptStringUsesCustomStdinName(): void
    {
        $stringToEncrypt = 'value';
        $password = 'pwd';
        $stdinName = 'my_secret';
        $executor = $this->createMock(CommandExecutor::class);
        $executor->expects($this->once())->method('execute')->with(
            $this->callback(function (array $command) use ($stdinName, $stringToEncrypt) {
                return in_array('--name', $command, true)
                    && in_array($stdinName, $command, true)
                    && in_array($stringToEncrypt, $command, true)
                    && !in_array('--stdin', $command, true);
            })
        )->willReturn('$ANSIBLE_VAULT;1.1;AES256;...');
        $encrypter = new Encrypter($executor);
        $encrypter->encryptString($stringToEncrypt, $password, stdinName: $stdinName);
        $this->assertTrue(true);
    }

    public function testEncryptSshPasswordVarUsesConventionalStdinName(): void
    {
        $sshPassword = 'ssh-pass';
        $password = 'vault-pwd';
        $executor = $this->createMock(CommandExecutor::class);
        $executor->expects($this->once())->method('execute')->with(
            $this->callback(function (array $command) use ($sshPassword) {
                return in_array('encrypt_string', $command, true)
                    && in_array('--name', $command, true)
                    && in_array('ansible_ssh_pass', $command, true)
                    && in_array($sshPassword, $command, true)
                    && !in_array('--stdin', $command, true);
            })
        )->willReturn('$ANSIBLE_VAULT;1.1;AES256;...');
        $encrypter = new Encrypter($executor);
        $encrypter->encryptSshPasswordVar($sshPassword, $password);
        $this->assertTrue(true);
    }

    public function testEncryptSshPasswordVarToFileWritesContent(): void
    {
        $sshPassword = 'ssh-pass';
        $password = 'vault-pwd';
        $outFile = __DIR__ . '/vault/host_1.vault';
        @unlink($outFile);
        @is_dir(dirname($outFile)) && array_map('unlink', glob(dirname($outFile) . '/*') ?: []);
        @rmdir(dirname($outFile));

        $executor = $this->createMock(CommandExecutor::class);
        $executor->expects($this->once())->method('execute')
            ->with(
                $this->callback(function (array $command) use ($sshPassword) {
                    return in_array('encrypt_string', $command, true)
                        && in_array('--name', $command, true)
                        && in_array('ansible_ssh_pass', $command, true)
                        && in_array($sshPassword, $command, true)
                        && !in_array('--stdin', $command, true);
                })
            )
            ->willReturn('$ANSIBLE_VAULT;1.1;AES256;encrypted-data');

        $encrypter = new Encrypter($executor);
        $this->assertTrue($encrypter->encryptSshPasswordVarToFile($sshPassword, $outFile, $password));
        $this->assertFileExists($outFile);
        $this->assertStringStartsWith('$ANSIBLE_VAULT;', trim(file_get_contents($outFile)));

        // cleanup
        @unlink($outFile);
        @rmdir(dirname($outFile));
    }


    public function testCanDecryptAFileWithAPassword(): void
    {
        $filePath = __DIR__ . '/temp_file_to_decrypt.txt';
        try {
            $encryptedContent = '$ANSIBLE_VAULT;1.1;AES256
          63333432373832343439343334353135333333393535313430343332303032323438383630363131
          323334383830373932393038303037323937353133350A35313339363530333830303434333136
          313830353330303035373333333639300A35313333353233373336393838343936343539333631
          3636323739323930340A3832393430393437323736333932323530343133333239323334373132
          3337330A31393535363431393932333839363632343839393334383630323839343835350A32
          353238383532333139313933383038383137343434333333303334363234390A';
            file_put_contents($filePath, $encryptedContent);
            $password = 'test_password';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('sensitive data');
            $encrypter = new Encrypter($executor);
            $decrypted = $encrypter->decrypt($filePath, $password);
            $this->assertStringContainsString('sensitive data', $decrypted);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }


    public function testCanDecryptAFileWithAPasswordFile(): void
    {
        $filePath = __DIR__ . '/temp_file_to_decrypt_file.txt';
        try {
            $encryptedContent = sprintf('$ANSIBLE_VAULT;1.1;AES256
          63333432373832343439343334353135333333393535313430343332303032323438383630363131
          323334383830373932393038303037323937353133350A35313339363530333830303434333136
          313830353330303035373333333639300A35313333353233373336393838343936343539333631
          3636323739323930340A3832393430393437323736333932323530343133333239323334373132
          3337330A31393535363431393932333839363632343839393334383630323839343835350A32
          353238383532333139313933383038383137343434333333303334363234390A');
            file_put_contents($filePath, $encryptedContent);
            $passwordFile = __DIR__ . '/temp_password_file_decrypt.txt';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('sensitive data');
            $encrypter = new Encrypter($executor);
            $decrypted = $encrypter->decrypt($filePath, vaultPasswordFile: $passwordFile);
            $this->assertStringContainsString('sensitive data', $decrypted);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            if (file_exists($passwordFile)) {
                unlink($passwordFile);
            }
        }
    }


    public function testCanDecryptAFileWithProvidedPasswordFile(): void
    {
        $filePath = __DIR__ . '/temp_file_to_decrypt_provided_file.txt';
        try {
            $encryptedContent = sprintf('$ANSIBLE_VAULT;1.1;AES256
          63333432373832343439343334353135333333393535313430343332303032323438383630363131
          323334383830373932393038303037323937353133350A35313339363530333830303434333136
          313830353330303035373333333639300A35313333353233373336393838343936343539333631
          3636323739323930340A3832393430393437323736333932323530343133333239323334373132
          3337330A31393535363431393932333839363632343839393334383630323839343835350A32
          353238383532333139313933383038383137343434333333303334363234390A');
            file_put_contents($filePath, $encryptedContent);
            $passwordFile = __DIR__ . '/temp_provided_password_file_decrypt.txt';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('sensitive data');
            $encrypter = new Encrypter($executor);
            $decrypted = $encrypter->decrypt($filePath, vaultPasswordFile: $passwordFile);
            $this->assertStringContainsString('sensitive data', $decrypted);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            if (file_exists($passwordFile)) {
                unlink($passwordFile);
            }
        }
    }


    public function testCanDecryptAFileWithAPasswordAndVaultId(): void
    {
        $filePath = __DIR__ . '/temp_file_to_decrypt_id.txt';
        try {
            $encryptedContent = '$ANSIBLE_VAULT;1.1;AES256;my_vault@prompt
          37303037383739333234393636313239373938303038373836313736383830363038323538343633
          373836303030373138393139393530373131353338300A34393235363832373634393131333530
          383538373339303438333432313133330A34353439393336353333343238393232303736383431
          33343738313131310A343038353336393533373839393832363938343036313538383633313933
          33340A30393138313634313535383230383337333239323930383933343538373732340A';
            file_put_contents($filePath, $encryptedContent);
            $password = 'test_password';
            $vaultId = 'my_vault@prompt';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('super secret string with id');
            $encrypter = new Encrypter($executor);
            $decrypted = $encrypter->decrypt($filePath, $password, $vaultId);
            $this->assertStringContainsString('super secret string with id', $decrypted);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }


    public function testCanDecryptAStringWithAPassword(): void
    {
        $stringToDecrypt = sprintf('$ANSIBLE_VAULT;1.1;AES256
          38373036383537303734363637353032363831303834323136393037363034363239383432373031
          303033343939323830383731303739363534313531310A35303935343039353532363934393938
          353436373636333239393434353630390A32373938343934383737363033393633313731393831
          3838383533393033310A3333383031393530373737333534303232313831333436303433323739
          34320A3334353934353433363930353132303734373333343635');
        try {
            $password = 'test_password';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('super secret string');
            $encrypter = new Encrypter($executor);
            $decrypted = $encrypter->decryptString($stringToDecrypt, $password);
            $this->assertStringContainsString('super secret string', $decrypted);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        }
    }


    public function testCanDecryptAStringWithAPasswordFile(): void
    {
        $stringToDecrypt = '$ANSIBLE_VAULT;1.1;AES256
          38373036383537303734363637353032363831303834323136393037363034363239383432373031
          303033343939323830383731303739363534313531310A35303935343039353532363934393938
          353436373636333239393434353630390A32373938343934383737363033393633313731393831
          3838383533393033310A3333383031393530373737333534303232313831333436303433323739
          34320A3334353934353433363930353132303734373333343635';
        try {
            $passwordFile = __DIR__ . '/temp_password_file_decrypt_string.txt';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('super secret string');
            $encrypter = new Encrypter($executor);
            $decrypted = $encrypter->decryptString($stringToDecrypt, vaultPasswordFile: $passwordFile);
            $this->assertStringContainsString('super secret string', $decrypted);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($passwordFile)) {
                unlink($passwordFile);
            }
        }
    }


    public function testCanDecryptAStringWithProvidedPasswordFile(): void
    {
        $stringToDecrypt = '$ANSIBLE_VAULT;1.1;AES256
          38373036383537303734363637353032363831303834323136393037363034363239383432373031
          303033343939323830383731303739363534313531310A35303935343039353532363934393938
          353436373636333239393434353630390A32373938343934383737363033393633313731393831
          3838383533393033310A3333383031393530373737333534303232313831333436303433323739
          34320A333435393435343336393035313230373437333334363534353436313633330A';
        try {
            $passwordFile = __DIR__ . '/temp_provided_password_file_decrypt_string.txt';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('super secret string');
            $encrypter = new Encrypter($executor);
            $decrypted = $encrypter->decryptString($stringToDecrypt, vaultPasswordFile: $passwordFile);
            $this->assertStringContainsString('super secret string', $decrypted);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        } finally {
            if (file_exists($passwordFile)) {
                unlink($passwordFile);
            }
        }
    }


    public function testCanDecryptAStringWithAPasswordAndVaultId(): void
    {
        $stringToDecrypt = '$ANSIBLE_VAULT;1.1;AES256;my_vault@prompt
          37303037383739333234393636313239373938303038373836313736383830363038323538343633
          373836303030373138393139393530373131353338300A34393235363832373634393131333530
          383538373339303438333432313133330A34353439393336353333343238393232303736383431
          33343738313131310A343038353336393533373839393832363938343036313538383633313933
          33340A30393138313634313535383230383337333239323930383933343538373732340A';
        try {
            $password = 'test_password';
            $vaultId = 'my_vault@prompt';
            $executor = $this->createMock(CommandExecutor::class);
            $executor->method('execute')->willReturn('super secret string with id');
            $encrypter = new Encrypter($executor);
            $decrypted = $encrypter->decryptString($stringToDecrypt, $password, $vaultId);
            $this->assertStringContainsString('super secret string with id', $decrypted);
        } catch (AnsibleVaultNotFoundException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (Exception $e) {
            $this->markTestSkipped('Could not create mock object: ' . $e->getMessage());
        }
    }


    /**
     * @throws Exception
     */
    public function testCannotEncryptAFileWithoutAPassword(): void
    {
        $filePath = __DIR__ . '/temp_file_to_encrypt_no_password.txt';
        $executor = $this->createMock(CommandExecutor::class);
        $encrypter = new Encrypter($executor);
        $this->expectException(InvalidArgumentException::class);
        $encrypter->encrypt($filePath);
    }


    /**
     * @throws Exception
     */
    public function testCannotDecryptAFileWithoutAPassword(): void
    {
        $filePath = __DIR__ . '/temp_file_to_decrypt_no_password.txt';
        $executor = $this->createMock(CommandExecutor::class);
        $encrypter = new Encrypter($executor);
        $this->expectException(InvalidArgumentException::class);
        $encrypter->decrypt($filePath);
    }


    /**
     * @throws Exception
     */
    public function testCannotEncryptAStringWithoutAPassword(): void
    {
        $stringToEncrypt = 'super secret string';
        $executor = $this->createMock(CommandExecutor::class);
        $encrypter = new Encrypter($executor);
        $this->expectException(InvalidArgumentException::class);
        $encrypter->encryptString($stringToEncrypt);
    }


    /**
     * @throws Exception
     */
    public function testCannotDecryptAStringWithoutAPassword(): void
    {
        $stringToDecrypt = 'encrypted string';
        $executor = $this->createMock(CommandExecutor::class);
        $encrypter = new Encrypter($executor);
        $this->expectException(InvalidArgumentException::class);
        $encrypter->decryptString($stringToDecrypt);
    }
}