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
use HttpClient\Response\JSONResponse;

/**
 * Class Wallet
 * @package BitcoinRPC\Client
 */
class Wallet
{
    /** @var BitcoinRPC */
    private $client;
    /** @var string */
    private $name;
    /** @var null|string */
    private $passPhrase;

    /**
     * Wallet constructor.
     * @param BitcoinRPC $client
     * @param string $name
     * @throws WalletException
     */
    public function __construct(BitcoinRPC $client, string $name)
    {
        if (!preg_match('/[\w\-]+(\.[a-z]{2,8})?/', $name)) {
            throw new WalletException('Invalid wallet file/name');
        }

        $this->client = $client;
        $this->name = $name;
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
     * @param string $command
     * @param array|null $params
     * @return JSONResponse
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     */
    private function walletRPC(string $command, ?array $params = null): JSONResponse
    {
        return $this->client->jsonRPC($command, sprintf('/wallet/%s', $this->name), $params);
    }

    /**
     * @param int $confirmations
     * @param null|string $addr
     * @return string
     * @throws WalletException
     * @throws \BitcoinRPC\Exception\ConnectionException
     * @throws \BitcoinRPC\Exception\DaemonException
     */
    public function getBalance(int $confirmations = 1, ?string $addr = null): string
    {
        $params = [];
        if ($addr) {
            $params[] = $addr;
        }

        if ($confirmations > 0) {
            $params[] = $confirmations;
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
}