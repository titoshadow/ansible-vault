<?php

namespace Titoshadow\AnsibleVault;

class Encrypter
{
    public function __construct(protected CommandExecutor $executor)
    {
    }

    public function encrypt(string $target, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        $command = ['ansible-vault', 'encrypt'];
        if ($password !== null) {
            $command = array_merge($command, ['--vault-password', $password]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new \InvalidArgumentException('Password or vault password file is required to encrypt.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        $command[] = $target;

        return trim($this->executor->execute($command));
    }

    public function encryptString(string $stringToEncrypt, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        $command = ['ansible-vault', 'encrypt_string', '--stdin'];
        if ($password !== null) {
            $command = array_merge($command, ['--vault-password', $password]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new \InvalidArgumentException('Password or vault password file is required to encrypt.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        return trim($this->executor->execute($command, $stringToEncrypt));
    }

    public function decrypt(string $target, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        $command = ['ansible-vault', 'decrypt'];
        if ($password !== null) {
            $command = array_merge($command, ['--vault-password', $password]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new \InvalidArgumentException('Password or vault password file is required to decrypt.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        $command[] = $target;

        return trim($this->executor->execute($command));
    }

    public function decryptString(string $stringToDecrypt, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        $command = ['ansible-vault', 'decrypt', '--stdin'];
        if ($password !== null) {
            $command = array_merge($command, ['--vault-password', $password]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new \InvalidArgumentException('Password or vault password file is required to decrypt.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        return trim($this->executor->execute($command, $stringToDecrypt));
    }
}