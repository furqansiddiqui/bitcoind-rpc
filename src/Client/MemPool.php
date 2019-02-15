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

namespace BitcoinRPC\Client;

use BitcoinRPC\BitcoinRPC;
use BitcoinRPC\Exception\MemPoolException;

/**
 * Class MemPool
 * @package BitcoinRPC\Client
 */
class MemPool
{
    /** @var BitcoinRPC */
    private $bitcoinRPC;

    /**
     * BlockChain constructor.
     * @param BitcoinRPC $client
     */
    public function __construct(BitcoinRPC $client)
    {
        $this->bitcoinRPC = $client;
    }

    /**
     * @return array
     * @throws MemPoolException
     */
    public function getRaw(): array
    {
        $res = $this->bitcoinRPC->jsonRPC_client()
            ->jsonRPC_call("getrawmempool");

        if (!is_array($res->result)) {
            throw MemPoolException::unexpectedResultType("getRawMempool", "Object", gettype($res->result));
        }

        return $res->result;
    }
}