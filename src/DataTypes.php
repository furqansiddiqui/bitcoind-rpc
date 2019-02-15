<?php
/**
 * This file is a part of "furqansiddiqui/bitcoind-rpc" package.
 * https://github.com/furqansiddiqui/bitcoind-rpc
 *
 * Copyright (c) 2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/furqansiddiqui/bitcoind-rpc/blob/master/LICENSE
 */

declare(strict_types=1);

namespace BitcoinRPC;

/**
 * Class DataTypes
 * @package BitcoinRPC
 */
class DataTypes
{
    /**
     * @param $amount
     * @param int $scale
     * @return string|null
     */
    public static function AmountAsString($amount, int $scale = 0): ?string
    {
        $amount = strval($amount);

        // Check for scientific E-notations
        if (preg_match('/e(\-|\+)/i', $amount)) {
            $amount = number_format($amount, $scale, ".", ""); // Resolve
        }

        if (!Validator::BcAmount($amount)) {
            return null; // Not a valid amount value
        }

        return bcmul($amount, "1", $scale);
    }
}