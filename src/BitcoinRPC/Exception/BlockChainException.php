<?php
declare(strict_types=1);

namespace BitcoinRPC\Exception;

use BitcoinRPC\BitcoinRPCException;

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
    public static function unexpectedResultType(string $method, string $expected, string $got) : self
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