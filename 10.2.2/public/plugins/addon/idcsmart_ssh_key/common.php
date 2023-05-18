<?php

function getPublicKeyFingerprint(string $pemEncodedKey, string $hashAlgorithm = 'sha1')
{
    $keyWithoutPemWrapper = \preg_replace(
        '/^-----BEGIN (?:[A-Z]+ )?PUBLIC KEY-----([A-Za-z0-9\\/\\+\\s=]+)-----END (?:[A-Z]+ )?PUBLIC KEY-----$/ms',
        '\\1',
        $pemEncodedKey
    );
    $keyDataWithoutSpaces = \preg_replace('/\\s+/', '', $keyWithoutPemWrapper);

    $binaryKey = \base64_decode($keyDataWithoutSpaces);

    return \hash($hashAlgorithm, $binaryKey);
}