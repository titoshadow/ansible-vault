<?php

namespace Titoshadow\AnsibleVault;

use InvalidArgumentException;

class Editor
{
    public function __construct(protected CommandExecutor $executor)
    {
    }

    public function edit(string $path, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): bool
    {
        $command = ['ansible-vault', 'edit'];
        if ($password !== null) {
            $command = array_merge($command, ['--vault-password', $password]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new InvalidArgumentException('Password or vault password file is required to edit.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        $command[] = $path;

        $this->executor->execute($command, tty: true);

        return true;
    }
}