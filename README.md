# Ansible Vault PHP Wrapper

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE) [![Tests](https://github.com/your-namespace/repo-name/actions/workflows/tests.yml/badge.svg)](https://github.com/titoshadow/ansible-vault/actions/workflows/tests.yml)  
[![Coverage](https://img.shields.io/codecov/c/github/titoshadow/ansible-vault.svg)](https://codecov.io/gh/titoshadow/ansible-vault)

This library provides a simple PHP wrapper for the `ansible-vault` command, allowing to encrypt and decrypt content, 
and manage vaults directly within PHP. 

## Requirements

- PHP 8.3 or later
- Ansible 2.10 or later
- Ansible Vault must be available in your system PATH

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

// Create an instance of the library
$vault = new AnsibleVault('/path/to/vault-password-file');
```

### Encrypt a String

```php 
$encryptedString = $vault->encryptString('Sensitive data');
```

### Decrypt a String
```php 
$decryptedString = $vault->decryptString($encryptedString);
```

### Encrypt a File
```php 
$vault->encrypt('/path/to/plain-text-file');
```

### Decrypt a File
```php 
$vault->decrypt('/path/to/encrypted-file');
```

### Create a Vault
```php 
$vault->create('/path/to/new-vault-file', 'Content of the vault');
```

### Edit a Vault
```php 
$vault->edit('/path/to/vault-file');
```

### Rekey a Vault
```php 
$vault->rekey('/path/to/vault-file', 'old-password', 'new-password');
```

### Remove a Vault
```php 
$vault->remove('/path/to/vault-file');
```