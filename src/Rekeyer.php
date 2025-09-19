<?php

namespace Titoshadow\AnsibleVault;

use Titoshadow\AnsibleVault\Util\PasswordFileHelper;

class Rekeyer
{
    public function __construct(
        protected CommandExecutor $executor,
        protected string $binary = 'ansible-vault'
    ) {
    }

    public function rekey(string $path, ?string $oldPassword = null, ?string $newPassword = null, ?string $vaultId = null, ?string $vaultPasswordFile = null, ?string $newVaultPasswordFile = null): bool
    {
        $command = [$this->binary, 'rekey'];
        $tempOld = null;
        $tempNew = null;

        if ($oldPassword !== null) {
            $tempOld = PasswordFileHelper::create($oldPassword);
            $command = array_merge($command, ['--vault-password-file', $tempOld]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new \InvalidArgumentException('Old password or vault password file is required to rekey.');
        }

        if ($newPassword !== null) {
            $tempNew = PasswordFileHelper::create($newPassword);
            $command = array_merge($command, ['--new-vault-password-file', $tempNew]);
        } elseif ($newVaultPasswordFile !== null) {
            $command = array_merge($command, ['--new-vault-password-file', $newVaultPasswordFile]);
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        $command[] = $path;

        try {
            $this->executor->execute($command);
        } finally {
            PasswordFileHelper::delete($tempOld);
            PasswordFileHelper::delete($tempNew);
        }

        return true;
    }
}