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

namespace BitcoinRPC\Http;

use BitcoinRPC\Exception\BitcoinRPCException;

/**
 * Class AbstractJSONClient
 * @package BitcoinRPC\Http
 * @property-read null|string $host
 * @property-read null|int $port
 */
abstract class AbstractJSONClient
{
    /** @var null|string */
    protected $host;
    /** @var null|int */
    protected $port;
    /** @var AuthBasic */
    protected $auth;

    /** @var null|int */
    protected $_uniqueRequestIdNonce;

    /**
     * AbstractHttpClient constructor.
     * @param string $host
     * @param int $port
     */
    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
        $this->auth = new AuthBasic();
    }

    /**
     * @param $prop
     * @return string
     * @throws BitcoinRPCException
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "host":
                return $this->host;
            case "port":
                return $this->port;
        }

        throw new BitcoinRPCException('Cannot retrieve value of inaccessible property');
    }

    /**
     * @return AuthBasic
     */
    final public function auth(): AuthBasic
    {
        return $this->auth;
    }

    /**
     * @param int $nonce
     * @return AbstractJSONClient
     */
    final public function uniqueRequestIdNonce(int $nonce): self
    {
        $this->_uniqueRequestIdNonce = $nonce;
        return $this;
    }

    /**
     * @param string $method
     * @return string
     */
    final protected function requestId(string $method): string
    {
        return sprintf('%d_%s_%d', $this->_uniqueRequestIdNonce ?? 0, strtolower($method), time());
    }

    /**
     * @param string $httpMethod
     * @param string $endpoint
     * @param string $id
     * @param string $method
     * @param array|null $params
     * @return DaemonResponse
     */
    abstract public function jsonRPC_call(
        string $httpMethod,
        string $endpoint,
        string $id,
        string $method,
        ?array $params = null): DaemonResponse;

    /**
     * @param string $endpoint
     * @param string $id
     * @param string $method
     * @param array|null $params
     * @return DaemonResponse
     */
    public function get(string $endpoint, string $id, string $method, ?array $params = null): DaemonResponse
    {
        return $this->jsonRPC_call("GET", $endpoint, $id, $method, $params);
    }

    /**
     * @param string $endpoint
     * @param string $id
     * @param string $method
     * @param array|null $params
     * @return DaemonResponse
     */
    public function post(string $endpoint, string $id, string $method, ?array $params = null): DaemonResponse
    {
        return $this->jsonRPC_call("POST", $endpoint, $id, $method, $params);
    }

    /**
     * @param string $endpoint
     * @param string $id
     * @param string $method
     * @param array|null $params
     * @return DaemonResponse
     */
    public function put(string $endpoint, string $id, string $method, ?array $params = null): DaemonResponse
    {
        return $this->jsonRPC_call("PUT", $endpoint, $id, $method, $params);
    }

    /**
     * @param string $endpoint
     * @param string $id
     * @param string $method
     * @param array|null $params
     * @return DaemonResponse
     */
    public function delete(string $endpoint, string $id, string $method, ?array $params = null): DaemonResponse
    {
        return $this->jsonRPC_call("DELETE", $endpoint, $id, $method, $params);
    }
}