<?php

namespace Titoshadow\AnsibleVault\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Titoshadow\AnsibleVault\CommandExecutor;
use Titoshadow\AnsibleVault\Exception\VaultAuthException;
use Titoshadow\AnsibleVault\Exception\VaultCliUsageException;
use Titoshadow\AnsibleVault\Exception\VaultExecutionException;

#[CoversClass(CommandExecutor::class)]
#[CoversClass('Titoshadow\AnsibleVault\CommandExecutor')]
#[UsesClass('Titoshadow\AnsibleVault\Exception\VaultExecutionException')]
#[UsesClass('Titoshadow\AnsibleVault\Exception\VaultCliUsageException')]
#[UsesClass('Titoshadow\AnsibleVault\Exception\VaultAuthException')]
class CommandExecutorTest extends TestCase {

    public function testExecuteCommandSuccessfully(): void
    {
        $executor = new CommandExecutor();
        $output = $executor->execute(['echo', 'test']);
        $this->assertEquals("test\n", str_replace("\r\n", "\n", $output));
    }


    public function testExecuteCommandWithInput(): void
    {
        $executor = new CommandExecutor();
        $output = $executor->execute(['php', '-r', 'echo fgets(STDIN);'], 'test input');
        $this->assertEquals("test input", $output);
    }


    public function testExecuteCommandFailed(): void
    {
        $this->expectException(VaultExecutionException::class);
        $executor = new CommandExecutor();
        $executor->execute(['php', '-r', 'exit(1);']);
    }

    public function testPasswordsAreScrubbedFromExceptions(): void
    {
        $executor = new CommandExecutor();
        try {
            // Force a failure and include password-like flags to ensure scrubbing
            $executor->execute([
                'php', '-r', 'exit(1);',
                '--vault-password', 'supersecret',
                '--new-vault-password', 'anothersecret',
                '--vault-password-file', '/tmp/cleartext',
                '--password', 'pw',
                '-p', 'short',
                '--vault-password=equalsSecret',
                '--vault-password-file=/tmp/alsoclear',
                '--password=mysecret',
                '-p=combo'
            ]);
            $this->fail('Expected VaultExecutionException not thrown');
        } catch (VaultExecutionException $e) {
            $msg = $e->getMessage();
            // ensure no raw secrets or paths appear
            $this->assertStringNotContainsString('supersecret', $msg);
            $this->assertStringNotContainsString('anothersecret', $msg);
            $this->assertStringNotContainsString('/tmp/cleartext', $msg);
            $this->assertStringNotContainsString('equalsSecret', $msg);
            $this->assertStringNotContainsString('/tmp/alsoclear', $msg);
            $this->assertStringNotContainsString('mysecret', $msg);
            $this->assertStringNotContainsString('short', $msg);
            $this->assertStringNotContainsString('combo', $msg);
            $this->assertStringContainsString('****', $msg);
        }
    }

    public function testMapsExitCode2ToCliUsageException(): void
    {
        $this->expectException(VaultCliUsageException::class);
        $executor = new CommandExecutor();
        $executor->execute(['php', '-r', 'exit(2);']);
    }

    public function testMapsExitCode4ToAuthException(): void
    {
        $this->expectException(VaultAuthException::class);
        $executor = new CommandExecutor();
        $executor->execute(['php', '-r', 'exit(4);']);
    }

    public function testExecuteHonorsCwd(): void
    {
        $cwd = sys_get_temp_dir();
        $executor = new CommandExecutor();
        $out = $executor->execute(['php', '-r', 'echo getcwd();'], timeoutSeconds: 5.0, cwd: $cwd);
        $this->assertStringContainsString($cwd, $out);
    }

    public function testExecuteHonorsTimeout(): void
    {
        $this->expectException(ProcessTimedOutException::class);
        $executor = new CommandExecutor();
        // Sleep for 5 seconds but timeout at 0.1s
        $executor->execute(['php', '-r', 'usleep(5000000);'], timeoutSeconds: 0.1);
    }
}