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
    protected $timeOut;
    /** @var null|int */
    protected $connectTimeout;

    /** @var null|int */
    protected $_uniqueRequestIdNonce;
    /** @var null|DaemonResponseError */
    protected $_lastCommandError;

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
     * @param int|null $timeOut
     * @param int|null $connectTimeout
     * @return $this
     */
    public function setTimeout(?int $timeOut = null, ?int $connectTimeout = null): self
    {
        $this->timeOut = $timeOut > 0 ? $timeOut : null;
        $this->connectTimeout = $connectTimeout > 0 ? $connectTimeout : null;
        return $this;
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
     * @return DaemonResponseError|null
     */
    final public function lastCommandError(): ?DaemonResponseError
    {
        return $this->_lastCommandError;
    }

    /**
     * @param string $method
     * @param string|null $endpoint
     * @param array|null $params
     * @param string|null $httpMethod
     * @return DaemonResponse
     */
    abstract public function jsonRPC_call(
        string $method,
        ?string $endpoint = null,
        ?array $params = null,
        ?string $httpMethod = 'POST'
    ): DaemonResponse;

    /**
     * @param string $method
     * @param string|null $endpoint
     * @param array|null $params
     * @return DaemonResponse
     */
    public function get(string $method, ?string $endpoint = null, ?array $params = null): DaemonResponse
    {
        return $this->jsonRPC_call($method, $endpoint, $params, "GET");
    }

    /**
     * @param string $method
     * @param string|null $endpoint
     * @param array|null $params
     * @return DaemonResponse
     */
    public function post(string $method, ?string $endpoint = null, ?array $params = null): DaemonResponse
    {
        return $this->jsonRPC_call($method, $endpoint, $params, "POST");
    }

    /**
     * @param string $method
     * @param string|null $endpoint
     * @param array|null $params
     * @return DaemonResponse
     */
    public function put(string $method, ?string $endpoint = null, ?array $params = null): DaemonResponse
    {
        return $this->jsonRPC_call($method, $endpoint, $params, "PUT");
    }

    /**
     * @param string $method
     * @param string|null $endpoint
     * @param array|null $params
     * @return DaemonResponse
     */
    public function delete(string $method, ?string $endpoint = null, ?array $params = null): DaemonResponse
    {
        return $this->jsonRPC_call($method, $endpoint, $params, "DELETE");
    }
}