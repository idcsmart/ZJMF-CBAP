<?php

/**
 * 时间 2022-07-07
 * @title 创建SSH密钥
 * @desc 创建SSH密钥
 * @author theworld
 * @version v1
 * @param string pemEncodedKey - 公钥 required
 * @param string hashAlgorithm - 哈希算法
 * @return string
 */
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