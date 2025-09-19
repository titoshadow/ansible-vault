<?php

namespace Titoshadow\AnsibleVault;

use InvalidArgumentException;
use Titoshadow\AnsibleVault\Util\PasswordFileHelper;

class Editor
{
    public function __construct(
        protected CommandExecutor $executor,
        protected string $binary = 'ansible-vault'
    ) {
    }

    public function edit(string $path, ?string $password = null, ?string $vaultId = null, ?string $vaultPasswordFile = null): bool
    {
        $command = [$this->binary, 'edit'];
        $tempFile = null;

        if ($password !== null) {
            $tempFile = PasswordFileHelper::create($password);
            $command = array_merge($command, ['--vault-password-file', $tempFile]);
        } elseif ($vaultPasswordFile !== null) {
            $command = array_merge($command, ['--vault-password-file', $vaultPasswordFile]);
        } else {
            throw new InvalidArgumentException('Password or vault password file is required to edit.');
        }

        if ($vaultId !== null) {
            $command = array_merge($command, ['--vault-id', $vaultId]);
        }

        $command[] = $path;

        try {
            $this->executor->execute($command, tty: true);
        } finally {
            PasswordFileHelper::delete($tempFile);
        }

        return true;
    }
}