<?php

namespace Titoshadow\AnsibleVault;

use Symfony\Component\Process\Process;
use Titoshadow\AnsibleVault\Exception\VaultAuthException;
use Titoshadow\AnsibleVault\Exception\VaultCliUsageException;
use Titoshadow\AnsibleVault\Exception\VaultExecutionException;

class CommandExecutor
{
    public function __construct(
        private readonly ?float $defaultTimeout = 60.0,
        private readonly ?string $defaultCwd = null
    ) {
    }

    /**
     * Executes a command and returns STDOUT. On failure, throws a typed exception with sanitized command.
     */
    public function execute(
        array $command,
        ?string $input = null,
        bool $tty = false,
        ?float $timeoutSeconds = null,
        ?string $cwd = null
    ): string {
        $process = new Process(
            $command,
            $cwd ?? $this->defaultCwd,
            null,
            $input,
            $timeoutSeconds ?? $this->defaultTimeout
        );

        if ($tty) {
            $process->setTty(true);
        }

        // Process::run may throw on timeout; let it bubble up for clarity.
        $process->run();

        if (!$process->isSuccessful()) {
            $sanitized = $this->sanitizeCommand($command);
            $message = sprintf(
                'Command failed with exit code %d: %s%s',
                $process->getExitCode(),
                $this->stringifyCommand($sanitized),
                ($err = trim($process->getErrorOutput())) !== '' ? PHP_EOL . $err : ''
            );
            throw $this->mapToException(
                $process->getExitCode(),
                $message,
                $process->getOutput(),
                $process->getErrorOutput()
            );
        }

        return $process->getOutput();
    }

    /**
     * Masks sensitive values in the command array (e.g., passwords, password files).
     */
    private function sanitizeCommand(array $command): array
    {
        // Include common variants and file-based flags; also support short flags
        $sensitiveFlags = [
            '--vault-password',
            '--new-vault-password',
            '--vault-password-file',
            '--new-vault-password-file',
            '--password',
            '-p',
        ];

        for ($i = 0; $i < count($command); $i++) {
            $arg = (string)$command[$i];

            // Handle flags with separate argument (e.g., --vault-password secret, -p secret)
            if (in_array($arg, $sensitiveFlags, true) && isset($command[$i + 1])) {
                $command[$i + 1] = '****';
                $i++;
                continue;
            }

            // Handle flags in --flag=value form
            foreach ($sensitiveFlags as $flag) {
                // Long or short flags with equals (e.g., --password=secret, -p=secret)
                if (str_starts_with($arg, $flag . '=')) {
                    $command[$i] = $flag . '=****';
                    continue 2;
                }
            }

            // Handle compact short flag like -psecret
            if (str_starts_with($arg, '-p') && $arg !== '-p') {
                $command[$i] = '-p****';
            }
        }

        return $command;
    }

    /**
     * Converts a command array to a safe, printable string.
     */
    private function stringifyCommand(array $command): string
    {
        return implode(' ', array_map(
            static fn($part) => is_string($part) ? escapeshellarg($part) : (string)$part,
            $command
        ));
    }

    /**
     * Map known exit codes to more specific exceptions.
     */
    private function mapToException(?int $exitCode, string $message, string $output, string $errorOutput): VaultExecutionException
    {
        return match ($exitCode) {
            2 => new VaultCliUsageException($message, $exitCode, $output, $errorOutput),
            4 => new VaultAuthException($message, $exitCode, $output, $errorOutput),
            default => new VaultExecutionException($message, $exitCode, $output, $errorOutput),
        };
    }
}