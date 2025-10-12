<?php

namespace Titoshadow\AnsibleVault;

use InvalidArgumentException;
use Titoshadow\AnsibleVault\Exception\AnsibleVaultNotFoundException;
use Titoshadow\AnsibleVault\Exception\VaultExecutionException;

class AnsibleVault
{
    protected VaultManager $vaultManager;
    protected Encrypter $encrypter;
    protected Editor $editor;
    protected Rekeyer $rekeyer;
    private CommandExecutor $executor;
    private string $binary;
    private ?string $defaultVaultPasswordFile = null;

    /**
     * @throws AnsibleVaultNotFoundException
     */
    public function __construct(?string $vaultPasswordFile = null, ?CommandExecutor $executor = null, ?string $binary = null)
    {
        $this->executor = $executor ?? new CommandExecutor();
        $this->binary = $binary ?? (getenv('ANSIBLE_VAULT_BIN') ?: 'ansible-vault');
        $this->defaultVaultPasswordFile = $vaultPasswordFile;

        $this->ensureAnsibleVaultIsAvailable();

        $this->vaultManager = new VaultManager($this->executor, $this->binary);
        $this->encrypter = new Encrypter($this->executor, $this->binary);
        $this->editor = new Editor($this->executor, $this->binary);
        $this->rekeyer = new Rekeyer($this->executor, $this->binary);
    }

    /**
     * @throws AnsibleVaultNotFoundException
     */
    protected function ensureAnsibleVaultIsAvailable(): void
    {
        try {
            // Will throw VaultExecutionException if not available
            $this->executor->execute([$this->binary, '--version']);
        } catch (VaultExecutionException) {
            throw new AnsibleVaultNotFoundException(
                'The ansible-vault command was not found. Please ensure Ansible is installed and the command is in your system\'s PATH.'
            );
        }
    }

    public function create(string $path, ?string $password = null, bool $encrypted = true, ?string $vaultPasswordFile = null): bool
    {
        if ($encrypted && $password === null && $vaultPasswordFile === null && $this->defaultVaultPasswordFile !== null) {
            $vaultPasswordFile = $this->defaultVaultPasswordFile;
        }
        return $this->vaultManager->create($path, $password, $encrypted, $vaultPasswordFile);
    }

    public function remove(string $path): bool
    {
        return $this->vaultManager->remove($path);
    }

    public function encrypt(string $target, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        if ($password === null && $vaultPasswordFile === null && $this->defaultVaultPasswordFile !== null) {
            $vaultPasswordFile = $this->defaultVaultPasswordFile;
        }

        $command = [$this->binary, 'encrypt'];
        if ($password !== null) {
            // Handled internally by Encrypter when used via AnsibleVault facade; keeping here for BC only if called directly.
            $command = array_merge($command, ['--vault-password', $password]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new InvalidArgumentException('Password or vault password file is required to encrypt.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        $command[] = $target;

        return trim($this->executor->execute($command));
    }

    public function encryptString(string $stringToEncrypt, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null, string $stdinName = 'secret'): string
    {
        if ($password === null && $vaultPasswordFile === null && $this->defaultVaultPasswordFile !== null) {
            $vaultPasswordFile = $this->defaultVaultPasswordFile;
        }

        $command = [$this->binary, 'encrypt_string', $stringToEncrypt, '--name', $stdinName];
        if ($password !== null) {
            $command = array_merge($command, ['--vault-password', $password]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new InvalidArgumentException('Password or vault password file is required to encrypt.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        return trim($this->executor->execute($command));
    }

    public function decrypt(string $target, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        if ($password === null && $vaultPasswordFile === null && $this->defaultVaultPasswordFile !== null) {
            $vaultPasswordFile = $this->defaultVaultPasswordFile;
        }

        $command = [$this->binary, 'decrypt'];
        if ($password !== null) {
            $command = array_merge($command, ['--vault-password', $password]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new InvalidArgumentException('Password or vault password file is required to decrypt.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        $command[] = $target;

        return trim($this->executor->execute($command));
    }

    public function decryptString(string $stringToDecrypt, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        if ($password === null && $vaultPasswordFile === null && $this->defaultVaultPasswordFile !== null) {
            $vaultPasswordFile = $this->defaultVaultPasswordFile;
        }

        $tempVaultFile = tempnam(sys_get_temp_dir(), 'vault_');
        if ($tempVaultFile === false) {
            throw new \RuntimeException('Failed to create temporary file for vault content.');
        }

        if (@file_put_contents($tempVaultFile, $stringToDecrypt) === false) {
            @unlink($tempVaultFile);
            throw new \RuntimeException('Failed to write vault content to temporary file.');
        }

        $command = [$this->binary, 'view', $tempVaultFile];
        if ($password !== null) {
            $command = array_merge($command, ['--vault-password', $password]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            @unlink($tempVaultFile);
            throw new InvalidArgumentException('Password or vault password file is required to decrypt.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        try {
            return trim($this->executor->execute($command));
        } finally {
            @unlink($tempVaultFile);
        }
    }

    public function edit(string $path, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): bool
    {
        if ($password === null && $vaultPasswordFile === null && $this->defaultVaultPasswordFile !== null) {
            $vaultPasswordFile = $this->defaultVaultPasswordFile;
        }
        return $this->editor->edit($path, $password, $vaultId, $vaultPasswordFile);
    }

    public function rekey(string $path, ?string $oldPassword = null, ?string $newPassword = null, ?string $vaultId = null, ?string $vaultPasswordFile = null, ?string $newVaultPasswordFile = null): bool
    {
        if ($oldPassword === null && $vaultPasswordFile === null && $this->defaultVaultPasswordFile !== null) {
            $vaultPasswordFile = $this->defaultVaultPasswordFile;
        }
        return $this->rekeyer->rekey($path, $oldPassword, $newPassword, $vaultId, $vaultPasswordFile, $newVaultPasswordFile);
    }
}