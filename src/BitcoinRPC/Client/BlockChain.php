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
        $this->client   =   $client;
    }

    /**
     * @return int
     * @throws BlockChainException
     */
    public function getBlockCount() : int
    {
        $count  =   $this->client->jsonRPC(\HttpClient::Post(), "getblockcount");
        if(!is_int($count)) {
            throw BlockChainException::unexpectedResultType(__METHOD__, "int", gettype($count));
        }

        return $count;
    }
}