<?php

namespace Titoshadow\AnsibleVault;

use Symfony\Component\Process\Process;
use Titoshadow\AnsibleVault\Exception\AnsibleVaultNotFoundException;

class AnsibleVault
{
    protected VaultManager $vaultManager;
    protected Encrypter $encrypter;
    protected Editor $editor;
    protected Rekeyer $rekeyer;

    /**
     * @throws AnsibleVaultNotFoundException
     */
    public function __construct(?string $vaultPasswordFile = null)
    {
        $executor = new CommandExecutor();
        $this->ensureAnsibleVaultIsAvailable($executor);
        $this->vaultManager = new VaultManager($executor);
        $this->encrypter = new Encrypter($executor);
        $this->editor = new Editor($executor);
        $this->rekeyer = new Rekeyer($executor);
    }

    /**
     * @throws AnsibleVaultNotFoundException
     */
    protected function ensureAnsibleVaultIsAvailable(CommandExecutor $executor): void
    {
        $process = new Process(['which', 'ansible-vault']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new AnsibleVaultNotFoundException('The ansible-vault command was not found. Please ensure Ansible is installed and the command is in your system\'s PATH.');
        }
    }

    public function create(string $path, ?string $password = null, bool $encrypted = true, ?string $vaultPasswordFile = null): bool
    {
        return $this->vaultManager->create($path, $password, $encrypted, $vaultPasswordFile);
    }

    public function remove(string $path): bool
    {
        return $this->vaultManager->remove($path);
    }

    public function encrypt(string $target, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        return $this->encrypter->encrypt($target, $password, $vaultId, $vaultPasswordFile);
    }

    public function encryptString(string $stringToEncrypt, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        return $this->encrypter->encryptString($stringToEncrypt, $password, $vaultId, $vaultPasswordFile);
    }

    public function decrypt(string $target, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        return $this->encrypter->decrypt($target, $password, $vaultId, $vaultPasswordFile);
    }

    public function decryptString(string $stringToDecrypt, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): string
    {
        return $this->encrypter->decryptString($stringToDecrypt, $password, $vaultId, $vaultPasswordFile);
    }

    public function edit(string $path, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): bool
    {
        return $this->editor->edit($path, $password, $vaultId, $vaultPasswordFile);
    }

    public function rekey(string $path, ?string $oldPassword = null, ?string $newPassword = null, ?string $vaultId = null, ?string $vaultPasswordFile = null, ?string $newVaultPasswordFile = null): bool
    {
        return $this->rekeyer->rekey($path, $oldPassword, $newPassword, $vaultId, $vaultPasswordFile, $newVaultPasswordFile);
    }
}