<?php
declare(strict_types=1);

namespace BitcoinRPC\Client;

use BitcoinRPC\BitcoinRPC;
use BitcoinRPC\Exception\BlockChainException;

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
}