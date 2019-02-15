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

namespace BitcoinRPC\Exception;

/**
 * Class BitcoinRPCException
 */
class BitcoinRPCException extends \Exception
{
    /**
     * @param string $method
     * @param string|null $expected
     * @param string|null $got
     * @return string
     */
    public static function unexpectedMethodResultTypeString(string $method, ?string $expected = null, ?string $got = null): string
    {
        $message = sprintf('Method "%s" unexpected result data type', $method);
        if ($expected) {
            $message .= sprintf(', expected "%s"', $expected);
            if ($got) {
                $message .= sprintf(', got "%s"', $got);
            }
        }

        return $message;
    }
}