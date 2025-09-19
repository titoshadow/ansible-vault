<?php

namespace Titoshadow\AnsibleVault\Util;

final class PasswordFileHelper
{
    /**
     * Creates a secure temporary file containing the provided password.
     * Returns the absolute path to the created file.
     */
    public static function create(string $password): string
    {
        $dir = sys_get_temp_dir();
        $tmp = tempnam($dir, 'vault_pwd_');
        if ($tmp === false) {
            throw new \RuntimeException('Failed to create temporary password file.');
        }

        $bytes = @file_put_contents($tmp, $password);
        if ($bytes === false) {
            @unlink($tmp);
            throw new \RuntimeException('Failed to write temporary password file.');
        }

        // Best effort to secure permissions on POSIX systems
        if (function_exists('chmod')) {
            @chmod($tmp, 0600);
        }

        return $tmp;
    }

    /**
     * Deletes a temporary password file if it exists.
     */
    public static function delete(?string $path): void
    {
        if ($path !== null && $path !== '' && is_file($path)) {
            @unlink($path);
        }
    }
}
