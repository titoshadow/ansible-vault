<?php

namespace Titoshadow\AnsibleVault\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Titoshadow\AnsibleVault\CommandExecutor;

class CommandExecutorTest extends TestCase
{
    public function testExecuteCommandSuccessfully(): void
    {
        $executor = new CommandExecutor();
        $output = $executor->execute(['echo', 'test']);
        $this->assertEquals("test\n", $output);
    }

    public function testExecuteCommandWithInput(): void
    {
        $executor = new CommandExecutor();
        $output = $executor->execute(['cat'], 'test input');
        $this->assertEquals("test input", $output);
    }

    public function testExecuteCommandFailed(): void
    {
        $this->expectException(ProcessFailedException::class);
        $executor = new CommandExecutor();
        $executor->execute(['false']);
    }
}