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
     * @param string $param
     * @param string|null $expected
     * @param string|null $got
     * @return ResponseObjectException
     */
    public static function badParamValueType(string $param, ?string $expected = null, ?string $got = null): self
    {
        $message = sprintf('Bad value for param "%s"', $param);
        if ($expected) {
            $message .= sprintf(', expected "%s"', $expected);
        }

        if ($got) {
            $message .= sprintf(', got "%s"', $got);
        }

        return new self($message);
    }
}