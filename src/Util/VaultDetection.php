<?php

namespace Titoshadow\AnsibleVault\Util;

final class VaultDetection
{
    public static function isEncryptedString(string $content): bool
    {
        return (bool)preg_match('/^\s*\$ANSIBLE_VAULT;/', $content);
    }

    public static function isEncryptedFile(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }
        $fh = @fopen($path, 'r');
        if ($fh === false) {
            return false;
        }
        $first = fgets($fh) ?: '';
        fclose($fh);
        return self::isEncryptedString($first);
    }
}
