<?php

namespace Titoshadow\AnsibleVault\Exception;

use RuntimeException;

class VaultExecutionException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?int $exitCode = null,
        private readonly string $output = '',
        private readonly string $errorOutput = ''
    ) {
        parent::__construct($message, $exitCode ?? 0);
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function getErrorOutput(): string
    {
        return $this->errorOutput;
    }
}
