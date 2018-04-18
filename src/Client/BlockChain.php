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

namespace BitcoinRPC\Client;

use BitcoinRPC\BitcoinRPC;
use BitcoinRPC\Exception\BlockChainException;
use BitcoinRPC\Response\Block;

/**
 * Class BlockChain
 * @package BitcoinRPC\Client
 */
class BlockChain
{
    /** @var BitcoinRPC */
    private $client;

    /**
     * BlockChain constructor.
     * @param BitcoinRPC $client
     */
    public function __construct(BitcoinRPC $client)
    {
        $this->client = $client;
    }

    /**
     * @return int
     * @throws BlockChainException
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function getBlockCount(): int
    {
        $request = $this->client->jsonRPC("getblockcount");
        $blockCount = $request->get("result");
        if (!is_int($blockCount)) {
            throw BlockChainException::unexpectedResultType(__METHOD__, "int", gettype($blockCount));
        }

        return $blockCount;
    }

    /**
     * @param int $number
     * @return string
     * @throws BlockChainException
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function getBlockHash(int $number): string
    {
        $request = $this->client->jsonRPC("getblockhash", null, [$number]);
        $hash = $request->get("result");
        if (!is_string($hash) || !preg_match('/^[a-f0-9]{64}$/i', $hash)) {
            throw BlockChainException::unexpectedResultType("getblockhash", "hash", gettype($hash));
        }

        return $hash;
    }

    /**
     * @param string $hash
     * @return Block
     * @throws BlockChainException
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function getBlock(string $hash): Block
    {
        $request = $this->client->jsonRPC("getblock", null, [$hash]);
        $block = $request->get("result");
        if (!is_array($block) || !count($block)) {
            throw BlockChainException::unexpectedResultType("getblock", "object", gettype($block));
        }

        return new Block($block);
    }

    /**
     * @param int $number
     * @return Block
     * @throws BlockChainException
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function getBlockByNumber(int $number): Block
    {
        return $this->getBlock($this->getBlockHash($number));
    }
}