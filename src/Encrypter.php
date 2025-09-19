<?php

namespace Titoshadow\AnsibleVault;

use Titoshadow\AnsibleVault\Util\PasswordFileHelper;

class Encrypter
{
    public function __construct(
        protected CommandExecutor $executor,
        protected string $binary = 'ansible-vault'
    ) {
    }

    public function encrypt(string $target, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        $command = [$this->binary, 'encrypt'];
        $tempFile = null;

        if ($password !== null) {
            $tempFile = PasswordFileHelper::create($password);
            $command = array_merge($command, ['--vault-password-file', $tempFile]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new \InvalidArgumentException('Password or vault password file is required to encrypt.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        $command[] = $target;

        try {
            return trim($this->executor->execute($command));
        } finally {
            PasswordFileHelper::delete($tempFile);
        }
    }

    public function encryptString(string $stringToEncrypt, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null, string $stdinName = 'secret'): string
    {
        $command = [$this->binary, 'encrypt_string', '--stdin', '--stdin-name', $stdinName];
        $tempFile = null;

        if ($password !== null) {
            $tempFile = PasswordFileHelper::create($password);
            $command = array_merge($command, ['--vault-password-file', $tempFile]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new \InvalidArgumentException('Password or vault password file is required to encrypt.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        try {
            return trim($this->executor->execute($command, $stringToEncrypt));
        } finally {
            PasswordFileHelper::delete($tempFile);
        }
    }

    /**
     * Convenience helper to encrypt an SSH password variable using the conventional stdin-name.
     */
    public function encryptSshPasswordVar(string $sshPassword, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        return $this->encryptString($sshPassword, $password, $vaultId, $vaultPasswordFile, stdinName: 'ansible_ssh_pass');
    }

    /**
     * Encrypts an SSH password and writes the encrypted snippet to a file (ensuring directories exist).
     */
    public function encryptSshPasswordVarToFile(string $sshPassword, string $outputPath, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): bool
    {
        $encrypted = $this->encryptSshPasswordVar($sshPassword, $password, $vaultId, $vaultPasswordFile);
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException('Failed to create directory: ' . $dir);
            }
        }
        $bytes = @file_put_contents($outputPath, $encrypted . PHP_EOL);
        if ($bytes === false) {
            throw new \RuntimeException('Failed to write encrypted content to ' . $outputPath);
        }
        return true;
    }

    public function decrypt(string $target, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        $command = [$this->binary, 'decrypt'];
        $tempFile = null;

        if ($password !== null) {
            $tempFile = PasswordFileHelper::create($password);
            $command = array_merge($command, ['--vault-password-file', $tempFile]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new \InvalidArgumentException('Password or vault password file is required to decrypt.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        $command[] = $target;

        try {
            return trim($this->executor->execute($command));
        } finally {
            PasswordFileHelper::delete($tempFile);
        }
    }

    public function decryptString(string $stringToDecrypt, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        $command = [$this->binary, 'decrypt', '--stdin'];
        $tempFile = null;

        if ($password !== null) {
            $tempFile = PasswordFileHelper::create($password);
            $command = array_merge($command, ['--vault-password-file', $tempFile]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new \InvalidArgumentException('Password or vault password file is required to decrypt.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        try {
            return trim($this->executor->execute($command, $stringToDecrypt));
        } finally {
            PasswordFileHelper::delete($tempFile);
        }
    }
}