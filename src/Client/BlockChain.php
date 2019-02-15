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
use BitcoinRPC\Exception\BlockChainException;
use BitcoinRPC\Response\Block;
use BitcoinRPC\Response\RawTransaction;
use BitcoinRPC\Validator;

/**
 * Class BlockChain
 * @package BitcoinRPC\Client
 */
class BlockChain
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
     * @return int
     * @throws BlockChainException
     */
    public function getBlockCount(): int
    {
        $res = $this->bitcoinRPC->jsonRPC_client()
            ->jsonRPC_call("getblockcount");
        if (!is_int($res->result)) {
            throw BlockChainException::unexpectedResultType("getBlockCount", "int", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @param int $number
     * @return string
     * @throws BlockChainException
     */
    public function getBlockHash(int $number): string
    {
        $res = $this->bitcoinRPC->jsonRPC_client()
            ->jsonRPC_call("getblockhash", null, [$number]);
        if (!Validator::Hash($res->result, 64)) {
            throw BlockChainException::unexpectedResultType("getBlockHash", "Hash64", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @param string $hash
     * @return Block
     * @throws BlockChainException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     */
    public function getBlock(string $hash): Block
    {
        $res = $this->bitcoinRPC->jsonRPC_client()
            ->jsonRPC_call("getblock", null, [$hash]);

        if (!is_array($res->result) || !$res->result) {
            throw BlockChainException::unexpectedResultType("getBlock", "Object", gettype($res->result));
        }

        return new Block($res->result);
    }

    /**
     * @param int $number
     * @return Block
     * @throws BlockChainException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     */
    public function getBlockByNumber(int $number): Block
    {
        return $this->getBlock($this->getBlockHash($number));
    }

    /**
     * @param string $txId
     * @return RawTransaction
     * @throws BlockChainException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     */
    public function getRawTransaction(string $txId): RawTransaction
    {
        $res = $this->bitcoinRPC->jsonRPC_client()
            ->jsonRPC_call("getrawtransaction", null, [$txId, true]);

        if (!is_array($res->result) || !$res->result) {
            throw BlockChainException::unexpectedResultType("getRawTransaction", "Object", gettype($res->result));
        }

        return new RawTransaction($res->result);
    }
}