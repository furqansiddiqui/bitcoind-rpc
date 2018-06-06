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

    /**
     * @param $address
     * @return bool
     */
    public static function Address($address): bool
    {
        if (!is_string($address)) {
            return false;
        }

        // Minimum validation
        // Allow ":" for BCH addresses
        if (!preg_match('/^[a-z0-9\:]{30,60}$/i', $address)) {
            return false;
        }

        return true;
    }

    /**
     * @param $amount
     * @param bool $signed
     * @return bool
     */
    public static function BcAmount($amount, bool $signed = false): bool
    {
        if (!is_string($amount)) {
            return false;
        }

        $pattern = '[0-9]+(\.[0-9]+)?';
        if ($signed) {
            $pattern = '\-?' . $pattern;
        }

        return preg_match('/^' . $pattern . '$/', $amount) ? true : false;
    }
}