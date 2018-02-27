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

namespace BitcoinRPC;

use BitcoinRPC\Client\BlockChain;
use BitcoinRPC\Client\Wallet;
use BitcoinRPC\Exception\BitcoinRPCException;
use BitcoinRPC\Exception\ConnectionException;
use BitcoinRPC\Exception\DaemonException;
use HttpClient\Exception\HttpClientException;
use HttpClient\Request;
use HttpClient\Response\JSONResponse;

/**
 * Class BitcoinRPC
 * @package BitcoinRPC
 */
class BitcoinRPC
{
    const VERSION = "0.16.1";
    const SCALE = 8;

    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var string */
    private $username;
    /** @var string */
    private $password;
    /** @var bool */
    private $ssl;
    /** @var null|string */
    private $sslCA;

    /** @var Wallet */
    private $wallets;
    /** @var BlockChain */
    private $blockChain;

    /**
     * BitcoinRPC constructor.
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @throws BitcoinRPCException
     */
    public function __construct(string $host, int $port, string $username, string $password)
    {
        if (!extension_loaded("bcmath")) {
            throw new BitcoinRPCException('Bitcoin RPC client requires "bcmath" extension');
        }

        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->ssl = false;
        $this->wallets = [];
        $this->blockChain = new BlockChain($this);
    }

    /**
     * @param null|string $caPath
     * @return BitcoinRPC
     * @throws BitcoinRPCException
     */
    public function ssl(?string $caPath = null): self
    {
        $this->ssl = true;
        if ($caPath) {
            $caPath = realpath($caPath);
            if (!$caPath || !is_readable($caPath)) {
                throw new BitcoinRPCException('SSL CA path not found or not readable');
            }

            $this->sslCA = $caPath;
        }

        return $this;
    }

    /**
     * @param string $name
     * @return Wallet
     */
    public function wallet(string $name = "wallet.dat"): Wallet
    {
        $key = strtolower($name);
        if (array_key_exists($key, $this->wallets)) {
            return $this->wallets[$key];
        }

        $wallet = new Wallet($this, $name);
        $this->wallets[$key] = $wallet;
        return $wallet;
    }

    /**
     * @return BlockChain
     */
    public function blockChain(): BlockChain
    {
        return $this->blockChain;
    }

    /**
     * @param string $endpoint
     * @return string
     */
    private function url(string $endpoint = ""): string
    {
        $protocol = $this->ssl ? "https" : "http";
        return sprintf('%s://%s:%s/%s', $protocol, $this->host, $this->port, $endpoint);
    }

    /**
     * @param Request $request
     * @return Request
     * @throws \HttpClient\Exception\SSLException
     */
    private function prepare(Request $request): Request
    {
        $request->authentication()->basic($this->username, $this->password);
        if ($this->ssl) {
            $request->ssl()->verify(true);
            if ($this->sslCA) {
                $request->ssl()->certificateAuthority($this->sslCA);
            }
        }

        return $request;
    }

    /**
     * @param string $command
     * @param array|null $params
     * @param null|string $method
     * @return JSONResponse
     * @throws ConnectionException
     * @throws DaemonException
     */
    public function jsonRPC(string $command, ?array $params = null, ?string $method = 'POST'): JSONResponse
    {
        // Prepare Json RPC Call
        $id = sprintf('%s_%d', $command, time());
        $request = new Request($method, $this->url());
        $request->json(); // JSON request

        // Payload
        $request->payload([
            "jsonrpc" => "1.0",
            "id" => $id,
            "method" => $command,
            "params" => $params ?? []
        ]);

        // Send JSON RPC Request to Bitcoin daemon
        try {
            $this->prepare($request);
            $response = $request->send();
        } catch (HttpClientException $e) {
            throw new ConnectionException($e->getMessage(), $e->getCode());
        }

        // Is a JSONResponse?
        if (!$response instanceof JSONResponse) {
            throw new ConnectionException(sprintf('Expected a JSONResponse, got "%s"', get_class($response)));
        }

        // Cross-check response ID with request ID
        if ($response->get("id") !== $id) {
            throw new DaemonException('Response does not belong to sent request');
        }

        // Check for Error
        $error = $response->get("error");
        if (is_array($error)) {
            $errorCode = intval($error["code"] ?? 0);
            $errorMessage = $error["message"] ?? 'An error occurred';
            throw new DaemonException($errorMessage, $errorCode);
        }

        // Result
        $result = $response->get("result");
        if (!$result) {
            throw new DaemonException('No response was received');
        }

        return $response;
    }
}