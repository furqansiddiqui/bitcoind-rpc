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

namespace BitcoinRPC;

use BitcoinRPC\Client\BlockChain;
use BitcoinRPC\Client\Wallet;
use BitcoinRPC\Exception\BitcoinRPCException;
use BitcoinRPC\Http\AbstractJSONClient;
use BitcoinRPC\Http\DefaultClient;

/**
 * Class BitcoinRPC
 * @package BitcoinRPC
 */
class BitcoinRPC
{
    public const VERSION = "0.16.2";
    public const SCALE = 8;

    /** @var AbstractJSONClient */
    private $_jsonRPC_client;
    /** @var array */
    private $_wallets;
    /** @var BlockChain */
    private $_blockChain;

    /**
     * @param string $host
     * @param int $port
     * @return BitcoinRPC
     * @throws BitcoinRPCException
     * @throws \HttpClient\Exception\JSON_RPC_Exception
     */
    public static function Node(string $host, int $port): self
    {
        $jsonRPC_Client = new DefaultClient($host, $port);
        return new self($jsonRPC_Client);
    }

    /**
     * BitcoinRPC constructor.
     * @param AbstractJSONClient $jsonRPC_client
     * @throws BitcoinRPCException
     */
    public function __construct(AbstractJSONClient $jsonRPC_client)
    {
        if (!extension_loaded("bcmath")) {
            throw new BitcoinRPCException('Bitcoin RPC client requires "bcmath" extension');
        }

        $this->_jsonRPC_client = $jsonRPC_client;
        $this->_wallets = [];
        $this->_blockChain = new BlockChain($this);
    }

    /**
     * @param string $username
     * @param string $password
     * @return BitcoinRPC
     */
    public function auth(string $username, string $password): self
    {
        $this->_jsonRPC_client->auth()
            ->username($username)
            ->password($password);

        return $this;
    }

    /**
     * @return AbstractJSONClient
     */
    public function jsonRPC_client(): AbstractJSONClient
    {
        return $this->_jsonRPC_client;
    }

    /**
     * @param null|string $name
     * @return Wallet
     * @throws Exception\WalletException
     */
    public function wallet(?string $name = "wallet.dat"): Wallet
    {
        $key = $name ? strtolower($name) : "_default";
        if (array_key_exists($key, $this->_wallets)) {
            return $this->_wallets[$key];
        }

        $wallet = new Wallet($this, $name);
        $this->_wallets[$key] = $wallet;
        return $wallet;
    }

    /**
     * @return BlockChain
     */
    public function blockChain(): BlockChain
    {
        return $this->_blockChain;
    }
}