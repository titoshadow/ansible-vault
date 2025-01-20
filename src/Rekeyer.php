<?php

namespace Titoshadow\AnsibleVault;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Rekeyer
{
    public function __construct(protected CommandExecutor $executor)
    {
    }

    public function rekey(string $path, ?string $oldPassword = null, ?string $newPassword = null, ?string $vaultId = null, ?string $vaultPasswordFile = null, ?string $newVaultPasswordFile = null): bool
    {
        $command = ['ansible-vault', 'rekey'];

        if ($oldPassword !== null) {
            $command = array_merge($command, ['--vault-password', $oldPassword]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new \InvalidArgumentException('Old password or vault password file is required to rekey.');
        }

        if ($newPassword !== null) {
            $command = array_merge($command, ['--new-vault-password', $newPassword]);
        } elseif ($newVaultPasswordFile !== null) {
            $command = array_merge($command, ['--new-vault-password-file', $newVaultPasswordFile]);
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        $command[] = $path;

        $this->executor->execute($command);

        return true;
    }
}