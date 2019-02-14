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
 * Class BlockChainException
 * @package BitcoinRPC\Exception
 */
class BlockChainException extends BitcoinRPCException
{
    /**
     * @param string $method
     * @param string $expected
     * @param string $got
     * @return BlockChainException
     */
    public static function unexpectedResultType(string $method, string $expected, string $got): self
    {
        return new self(
            sprintf(
                'Method [%1$s] expects result type %2$s, got %3$s',
                $method,
                strtoupper($expected),
                strtoupper($got)
            )
        );
    }
}