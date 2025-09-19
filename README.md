# Ansible Vault PHP Wrapper

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE) [![Tests](https://github.com/titoshadow/ansible-vault/actions/workflows/tests.yml/badge.svg)](https://github.com/titoshadow/ansible-vault/actions/workflows/tests.yml)
[![Coverage](https://img.shields.io/codecov/c/github/titoshadow/ansible-vault.svg)](https://codecov.io/gh/titoshadow/ansible-vault)

A pragmatic, secure wrapper around `ansible-vault` for encrypting/decrypting strings and files, editing vaults, and re-keying from PHP. Tailored for host-centric workflows like storing SSH passwords that Ansible uses to connect to remote hosts.

## Requirements

- PHP 8.3 or later
- Ansible 2.10+ (`ansible-vault` available on the system)

## Binary resolution and availability check

The wrapper locates `ansible-vault` and validates availability with `--version`.

Resolution order:
1) Constructor argument `binary`
2) Environment variable `ANSIBLE_VAULT_BIN`
3) Fallback to `ansible-vault` in PATH

## Installation

Ensure Ansible is installed and configured correctly before using this library.

You can install this library via Composer:

```bash
composer require titoshadow/ansible-vault
```

## Usage
Include the library in your PHP code:

```php
use Titoshadow\AnsibleVault\AnsibleVault;

...

// Default (uses PATH) $vault = new AnsibleVault();
// Custom binary path $vault = new AnsibleVault(binary: '/usr/local/bin/ansible-vault');
// Or via environment putenv('ANSIBLE_VAULT_BIN=/opt/ansible/ansible-vault'); $vault = new AnsibleVault();
// Create an instance of the library

$vault = new AnsibleVault('/path/to/vault-password-file');
```

## Security model and password handling

- Avoid plaintext passwords on the command line. This wrapper:
    - Writes provided password strings to secure temp files (0600 on POSIX) and passes `--vault-password-file`.
    - Supports user-provided password file paths as-is.
- Errors scrub secrets in both `--flag value` and `--flag=value` forms (covers `--vault-password`, `--vault-password-file`, `--password`, `-p`, etc.).


## Command execution settings

All commands run via a lightweight executor that supports:
- Default timeout (60s) and default working directory (null)
- Per-call override for timeout and cwd (used internally)
- TTY for interactive edit sessions
 
Configure defaults when needed:

```php 
use Titoshadow\AnsibleVault\CommandExecutor; 
use Titoshadow\AnsibleVault\AnsibleVault;

$executor = new CommandExecutor(defaultTimeout: 120.0, defaultCwd: '/srv/project');
$vault = new AnsibleVault(executor: $executor);
``` 

## Core methods 

### Encrypt a String (stdin-name control):

```php
// stdin-name defaults to "secret" 
$encrypted = $vault->encryptString('Sensitive data', password: 'vault_pwd');
// Custom name to make output variable-friendly 
$encrypted = $vault->encryptString('Sensitive data', password: 'vault_pwd', stdinName: 'my_secret');
```

### Decrypt a String
```php 
$decryptedString = $vault->decryptString($encrypted, password: 'vault_pwd');
```

### Encrypt a File
```php 
vault->encrypt('/path/plain.txt', password: 'vault_pwd');
// or with an existing password
filevault->encrypt('/path/plain.txt', vaultPasswordFile: '/path/vault.pass');
```

### Decrypt a File
```php 
$vault->decrypt('/path/secret.txt', password: 'vault_pwd')
```

### Create a Vault
```php 
$vault->create('/path/vault.yml', password: 'vault_pwd', encrypted: true);
```

### Edit a Vault
```php 
$vault->edit('/path/vault.yml', password: 'vault_pwd'); 
```

### Rekey a Vault
```php 
$vault->rekey('/path/vault.yml', oldPassword: 'old', newPassword: 'new')
```

### Remove a Vault
```php 
$vault->remove('/path/vault.yml');
```

## Host-centric SSH helpers

Encrypt an SSH password to the conventional variable `ansible_ssh_pass` (in-memory):
```php
use Titoshadow\AnsibleVault\CommandExecutor; 
use Titoshadow\AnsibleVault\Encrypter;
$encrypter = new Encrypter(new CommandExecutor());
$encrypted = encrypter->encryptSshPasswordVar('ssh_password', password: 'vault_pwd'); 
//encrypted starts with "$ANSIBLE_VAULT;"
```
Encrypt and write the SSH secret to a file (directories are created if missing):

## Exceptions and error handling

On failures, a sanitized exception is thrown:
- VaultCliUsageException — typically exit code 2 (CLI misuse, invalid flags)
- VaultAuthException — typically exit code 4 (authentication/decryption issues)
- VaultExecutionException — default/fallback with masked secrets
```php 
use Titoshadow\AnsibleVault\Exception\VaultAuthException; 
use Titoshadow\AnsibleVault\Exception\VaultCliUsageException; 
use Titoshadow\AnsibleVault\Exception\VaultExecutionException;

try {
    $vault->decrypt('/path/secret.txt', password: 'wrong'); 
} catch (VaultAuthExceptione) {
// wrong password 
} catch (VaultCliUsageException e) {
 // invalid CLI usage 
} catch (VaultExecutionExceptione) {
// generic error (message is sanitized) 
}
```