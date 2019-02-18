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
use BitcoinRPC\Client\MemPool;
use BitcoinRPC\Client\Wallets;
use BitcoinRPC\Exception\BitcoinRPCException;
use BitcoinRPC\Http\AbstractJSONClient;
use BitcoinRPC\Http\DefaultClient;
use BitcoinRPC\Response\NetworkInfo;

/**
 * Class BitcoinRPC
 * @package BitcoinRPC
 */
class BitcoinRPC
{
    public const VERSION = "0.20.2";
    public const SCALE = 8;

    /** @var AbstractJSONClient */
    private $_jsonRPC_client;
    /** @var Config */
    private $_config;
    /** @var Wallets */
    private $_wallets;
    /** @var BlockChain */
    private $_blockChain;
    /** @var MemPool */
    private $_memPool;

    /** @var NetworkInfo */
    private $_networkInfo;
    /** @var null|CorePrivileges */
    private $_corePrivileges;

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
        $this->_config = new Config($this);
        $this->_wallets = new Wallets($this);
        $this->_blockChain = new BlockChain($this);
        $this->_memPool = new MemPool($this);
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
     * @return Config
     */
    public function config(): Config
    {
        return $this->_config;
    }

    /**
     * @return Wallets
     */
    public function wallets(): Wallets
    {
        return $this->_wallets;
    }

    /**
     * @return MemPool
     */
    public function mempool(): MemPool
    {
        return $this->_memPool;
    }

    /**
     * @return NetworkInfo
     * @throws BitcoinRPCException
     * @throws Exception\ResponseObjectException
     */
    public function getNetworkInfo(): NetworkInfo
    {
        if (!$this->_networkInfo) {
            $res = $this->jsonRPC_client()->post("getnetworkinfo");
            if (!is_array($res->result)) {
                throw new BitcoinRPCException(
                    BitcoinRPCException::unexpectedMethodResultTypeString("getNetworkInfo", "Object", gettype($res->result))
                );
            }

            $this->_networkInfo = new NetworkInfo($res->result);
        }

        return $this->_networkInfo;
    }

    /**
     * @return CorePrivileges
     * @throws BitcoinRPCException
     * @throws Exception\ResponseObjectException
     */
    public function corePrivileges(): CorePrivileges
    {
        if (!$this->_corePrivileges) {
            $this->_corePrivileges = new CorePrivileges($this);
        }

        return $this->_corePrivileges;
    }

    /**
     * @return AbstractJSONClient
     */
    public function jsonRPC_client(): AbstractJSONClient
    {
        return $this->_jsonRPC_client;
    }

    /**
     * @return BlockChain
     */
    public function blockChain(): BlockChain
    {
        return $this->_blockChain;
    }
}