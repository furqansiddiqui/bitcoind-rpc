<?php
declare(strict_types=1);

namespace BitcoinRPC\Client;

use BitcoinRPC\BitcoinRPC;
use BitcoinRPC\Exception\WalletException;

/**
 * Class Wallet
 * @package BitcoinRPC\Client
 */
class Wallet
{
    /** @var BitcoinRPC */
    private $client;

    /**
     * Wallet constructor.
     * @param BitcoinRPC $client
     */
    public function __construct(BitcoinRPC $client)
    {
        $this->client   =   $client;
    }

    /**
     * @return string
     * @throws WalletException
     */
    public function getBalance() : string
    {
        $balance  =   $this->client->jsonRPC(\HttpClient::Post(), "getbalance");
        if(!is_float($balance)) {
            throw WalletException::unexpectedResultType(__METHOD__, "float", gettype($balance));
        }

        return bcadd(strval($balance), "0", BitcoinRPC::BCMATH_SCALE);
    }

    /**
     * @return string
     * @throws WalletException
     */
    public function getNewAddress() : string
    {
        $address    =   $this->client->jsonRPC(\HttpClient::Post(), "getnewaddress");
        if(!is_string($address)) {
            throw WalletException::unexpectedResultType(__METHOD__, "string", gettype($address));
        }

        return $address;
    }
}