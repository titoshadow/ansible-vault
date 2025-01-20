<?php

namespace Titoshadow\AnsibleVault;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CommandExecutor
{
    public function execute(array $command, ?string $input = null, bool $tty = false): string
    {
        $process = new Process($command, null, null, $input, null, ['TTY' => $tty]);
        $process->run();

        if (!$process->isSuccessful()) {
           throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}