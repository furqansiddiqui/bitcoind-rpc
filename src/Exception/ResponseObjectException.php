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
 * Class ResponseObjectException
 * @package BitcoinRPC\Exception
 */
class ResponseObjectException extends BitcoinRPCException
{
    /**
     * @param string $class
     * @param string $errorMessage
     * @return ResponseObjectException
     */
    public static function ObjectConstructError(string $class, string $errorMessage): self
    {
        return new self(
            sprintf('Failed to construct response object "%s": %s', $class, $errorMessage)
        );
    }
}