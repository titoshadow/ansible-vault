<?php

namespace Titoshadow\AnsibleVault;

use InvalidArgumentException;
use Titoshadow\AnsibleVault\Util\PasswordFileHelper;

class VaultManager
{
    public function __construct(
        protected CommandExecutor $executor,
        protected string $binary = 'ansible-vault'
    ) {
    }

    public function create(string $path, ?string $password = null, bool $encrypted = true, ?string $vaultPasswordFile = null): bool
    {
        $command = [$this->binary, 'create'];
        $tempFile = null;

        if ($encrypted && $password === null && $vaultPasswordFile === null) {
            throw new InvalidArgumentException('Password or vault password file is required to create an encrypted vault.');
        }

        if ($encrypted && $password !== null) {
            $tempFile = PasswordFileHelper::create($password);
            $command = array_merge($command, ['--new-vault-password-file', $tempFile]);
        } elseif ($encrypted && $vaultPasswordFile !== null) {
            $command = array_merge($command, ['--new-vault-password-file', $vaultPasswordFile]);
        }

        $command[] = $path;

        try {
            $this->executor->execute($command);
        } finally {
            PasswordFileHelper::delete($tempFile);
        }

        return true;
    }

    public function remove(string $path): bool
    {
        $this->executor->execute(['rm', $path]);

        return true;
    }
}