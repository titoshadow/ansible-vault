<?php

namespace Titoshadow\AnsibleVault\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Titoshadow\AnsibleVault\CommandExecutor;

class CommandExecutorTest extends TestCase
{
    /**
     * @covers \Titoshadow\AnsibleVault\CommandExecutor::execute
     */
    public function testExecuteCommandSuccessfully(): void
    {
        $executor = new CommandExecutor();
        $output = $executor->execute(['echo', 'test']);
        $this->assertEquals("test\n", $output);
    }

    /**
     * @covers \Titoshadow\AnsibleVault\CommandExecutor::execute
     */
    public function testExecuteCommandWithInput(): void
    {
        $executor = new CommandExecutor();
        $output = $executor->execute(['cat'], 'test input');
        $this->assertEquals("test input", $output);
    }


    /**
     * @covers \Titoshadow\AnsibleVault\CommandExecutor::execute
     */
    public function testExecuteCommandFailed(): void
    {
        $this->expectException(ProcessFailedException::class);
        $executor = new CommandExecutor();
        $executor->execute(['false']);
    }
}