<?php

namespace Titoshadow\AnsibleVault;

class VaultManager
{
    public function __construct(protected CommandExecutor $executor)
    {
    }

    public function create(string $path, ?string $password = null, bool $encrypted = true, ?string $vaultPasswordFile = null): bool
    {
        $command = ['ansible-vault', 'create'];
        if ($encrypted && $password === null && $vaultPasswordFile === null) {
            throw new \InvalidArgumentException('Password or vault password file is required to create an encrypted vault.');
        }

        if ($encrypted && $password !== null) {
            $command = array_merge($command, ['--new-vault-password', $password]);
        } elseif ($encrypted && $vaultPasswordFile !== null) {
            $command = array_merge($command, ['--new-vault-password-file', $vaultPasswordFile]);
        }

        $command[] = $path;

        $this->executor->execute($command);

        return true;
    }

    public function remove(string $path): bool
    {
        $this->executor->execute(['rm', $path]);

        return true;
    }
}