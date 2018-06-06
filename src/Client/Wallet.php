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
use BitcoinRPC\Exception\WalletException;
use BitcoinRPC\Response\UnspentOutputs;
use HttpClient\Response\JSONResponse;

/**
 * Class Wallet
 * @package BitcoinRPC\Client
 */
class Wallet
{
    /** @var BitcoinRPC */
    private $client;
    /** @var null|string */
    private $name;
    /** @var null|string */
    private $passPhrase;

    /**
     * Wallet constructor.
     * @param BitcoinRPC $client
     * @param null|string $name
     * @throws WalletException
     */
    public function __construct(BitcoinRPC $client, ?string $name = "wallet.dat")
    {
        if (is_string($name) && !preg_match('/[\w\-]+(\.[a-z]{2,8})?/', $name)) {
            throw new WalletException('Invalid wallet file/name');
        }

        $this->client = $client;
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return ['Wallet ' . $this->name];
    }

    /**
     * @param string $passPhrase
     * @return Wallet
     */
    public function passPhrase(string $passPhrase): self
    {
        $this->passPhrase = $passPhrase;
        return $this;
    }

    /**
     * @param int $seconds
     * @return bool
     * @throws WalletException
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function unlock(int $seconds): bool
    {
        if (!$this->passPhrase) {
            throw new WalletException('Wallet passphrase not set');
        }

        $request = $this->walletRPC("walletpassphrase", [$this->passPhrase, $seconds]);
        if ($request->code() !== 200) {
            throw new WalletException('Failed to unlock wallet');
        }

        return true;
    }

    /**
     * @param null|string $addr
     * @param int $confirmations
     * @return string
     * @throws WalletException
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function getBalance(?string $addr = null, int $confirmations = 1): string
    {
        $params = [];
        if ($addr) {
            $params[] = $addr;

            if ($confirmations > 0) {
                $params[] = $confirmations;
            }
        }

        $request = $this->walletRPC("getbalance", $params);
        $balance = strval($request->get("result"));
        if (!preg_match('/^[0-9]+(\.[0-9]+)?$/', $balance)) {
            throw WalletException::unexpectedResultType(__METHOD__, "float", "invalid");
        }

        return bcmul($balance, "1", BitcoinRPC::SCALE);
    }

    /**
     * @return string
     * @throws WalletException
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function getNewAddress(): string
    {
        $request = $this->walletRPC("getnewaddress");
        $address = $request->get("result");
        if (!is_string($address)) {
            throw WalletException::unexpectedResultType("getnewaddress", "string", gettype($address));
        }

        return $address;
    }

    /**
     * @param string $txHash
     * @return array
     * @throws WalletException
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function getTransaction(string $txHash): array
    {
        $request = $this->walletRPC("gettransaction", [$txHash]);
        $tx = $request->get("result");
        if (!is_array($tx)) {
            throw WalletException::unexpectedResultType("gettransaction", "object", gettype($tx));
        }

        return $tx;
    }

    /**
     * @param string $addr
     * @param string $amount
     * @return string
     * @throws WalletException
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function sendToAddress(string $addr, string $amount): string
    {
        $request = $this->walletRPC("sendtoaddress", [$addr, $amount]);
        $txId = $request->get("result");
        if (!is_string($txId)) {
            throw WalletException::unexpectedResultType("sendtoaddress", "string", gettype($txId));
        }

        return $txId;
    }

    /**
     * @param int $minConfirmations
     * @param int|null $maxConfirmations
     * @param array|null $addresses
     * @return UnspentOutputs
     * @throws WalletException
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     * @throws \HttpClient\Exception\HttpClientException
     */
    public function listUnspent(int $minConfirmations = 1, ?int $maxConfirmations = null, ?array $addresses = null): UnspentOutputs
    {
        $args = [$minConfirmations];
        if ($maxConfirmations) {
            $args[] = $maxConfirmations;
        }

        if ($addresses) {
            $args[] = $addresses;
        }

        $request = $this->walletRPC("listunspent", $args);
        $outputs = $request->get("result");
        if (!is_array($outputs)) {
            throw WalletException::unexpectedResultType("listunspent", "array", gettype($outputs));
        }

        return new UnspentOutputs($outputs);
    }

    /**
     * @param string $command
     * @param array|null $params
     * @return JSONResponse
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     * @throws \HttpClient\Exception\HttpClientException
     */
    private function walletRPC(string $command, ?array $params = null): JSONResponse
    {
        $endpoint = null;
        if ($this->name) {
            $endpoint = sprintf('/wallet/%s', $this->name);
        }

        return $this->client->jsonRPC($command, $endpoint, $params);
    }
}