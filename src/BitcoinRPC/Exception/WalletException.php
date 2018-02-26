<?php
declare(strict_types=1);

namespace BitcoinRPC\Exception;
/**
 * Class WalletException
 * @package BitcoinRPC\Exception
 */
class WalletException extends BitcoinRPCException
{
    /**
     * @param string $method
     * @param string $expected
     * @param string $got
     * @return WalletException
     */
    public static function unexpectedResultType(string $method, string $expected, string $got): self
    {
        return new self(
            sprintf('Method [%s] expects result type %s, got %s', $method, strtoupper($expected), strtoupper($got))
        );
    }
}