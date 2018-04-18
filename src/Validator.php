<?php
/**
 * This file is a part of "furqansiddiqui/bitcoind-rpc" package.
 * https://github.com/furqansiddiqui/bitcoind-rpc
 *
 * Copyright (c) 2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/furqansiddiqui/bitcoind-rpc/blob/master/LICENSE
 */

declare(strict_types=1);

namespace BitcoinRPC;

/**
 * Class Validator
 * @package BitcoinRPC
 */
class Validator
{
    /**
     * @param $hash
     * @param int $length
     * @return bool
     */
    public static function Hash($hash, int $length = 64): bool
    {
        if (is_string($hash) && preg_match(sprintf('/^[a-f0-9]{%d}$/', $length), $hash)) {
            return true;
        }

        return false;
    }
}