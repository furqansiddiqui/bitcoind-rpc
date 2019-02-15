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

namespace BitcoinRPC\Client;

use BitcoinRPC\BitcoinRPC;
use BitcoinRPC\Client\Wallet\PrepareTransaction;
use BitcoinRPC\DataTypes;
use BitcoinRPC\Exception\WalletException;
use BitcoinRPC\Http\DaemonResponse;
use BitcoinRPC\Response\SignedRawTransaction;
use BitcoinRPC\Response\UnspentOutputs;
use BitcoinRPC\Validator;

/**
 * Class Wallet
 * @package BitcoinRPC\Client
 */
class Wallet
{
    /** @var BitcoinRPC */
    private $bitcoinRPC;
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

        $this->bitcoinRPC = $client;
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
     */
    public function unlock(int $seconds): bool
    {
        if (!$this->passPhrase) {
            throw new WalletException('Wallet passphrase not set');
        }


        $res = $this->walletRPC("walletpassphrase", [$this->passPhrase, $seconds]);
        if ($res->httpStatusCode !== 200) {
            throw new WalletException('Failed to unlock wallet');
        }

        return true;
    }

    /**
     * @param string|null $addr
     * @param int $confirmations
     * @return string
     * @throws WalletException
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

        $res = $this->walletRPC("getBalance", $params);
        $balance = DataTypes::AmountAsString($res->result, BitcoinRPC::SCALE);
        if (!$balance) {
            throw WalletException::unexpectedResultType("getBalance", "balance");
        }

        return $balance;
    }

    /**
     * @return string
     * @throws WalletException
     */
    public function getNewAddress(): string
    {
        $res = $this->walletRPC("getNewAddress");
        if (!is_string($res->result)) {
            throw WalletException::unexpectedResultType("getNewAddress", "String", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @param string $txHash
     * @return array
     * @throws WalletException
     */
    public function getTransaction(string $txHash): array
    {
        $res = $this->walletRPC("getTransaction", [$txHash]);
        if (!is_array($res->result)) {
            throw WalletException::unexpectedResultType("getTransaction", "Object", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @param string $addr
     * @param string $amount
     * @return string
     * @throws WalletException
     */
    public function sendToAddress(string $addr, string $amount): string
    {
        $res = $this->walletRPC("sendtoaddress", [$addr, $amount]);
        if (!is_string($res->result)) {
            throw WalletException::unexpectedResultType("sendtoaddress", "String", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @param int $minConfirmations
     * @param int|null $maxConfirmations
     * @param array|null $addresses
     * @return UnspentOutputs
     * @throws WalletException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
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

        $res = $this->walletRPC("listunspent", $args);
        if (!is_array($res->result)) {
            throw WalletException::unexpectedResultType("listunspent", "Object", gettype($res->result));
        }

        return new UnspentOutputs($res->result);
    }

    /**
     * @param array $inputs
     * @param array $outputs
     * @return string
     * @throws WalletException
     */
    public function createRawTransaction(array $inputs, array $outputs): string
    {
        $res = $this->walletRPC("createRawTransaction", [$inputs, $outputs]);
        if (!is_string($res->result)) {
            throw WalletException::unexpectedResultType("createRawTransaction", "String", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @param string $encodedRawTransaction
     * @return SignedRawTransaction
     * @throws WalletException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     */
    public function signRawTransaction(string $encodedRawTransaction): SignedRawTransaction
    {
        $res = $this->walletRPC("signRawTransaction", [$encodedRawTransaction]);
        if (!is_array($res->result)) {
            throw WalletException::unexpectedResultType("signRawTransaction", "Object", gettype($res->result));
        }

        return new SignedRawTransaction($res->result);
    }

    /**
     * @param string $signedTransaction
     * @return string
     * @throws WalletException
     */
    public function sendRawTransaction(string $signedTransaction): string
    {
        $res = $this->walletRPC("sendRawTransaction", [$signedTransaction]);
        if (!is_string($res->result) || !Validator::Hash($res->result, 64)) {
            throw WalletException::unexpectedResultType("sendRawTransaction", "Hash64", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @return PrepareTransaction
     */
    public function prepareTransaction(): PrepareTransaction
    {
        return new PrepareTransaction($this);
    }

    /**
     * @param string $command
     * @param array|null $params
     * @param string|null $httpMethod
     * @return DaemonResponse
     */
    private function walletRPC(string $command, ?array $params = null, ?string $httpMethod = 'POST'): DaemonResponse
    {
        $endpoint = $this->name ?
            sprintf('/wallet/%s', $this->name) : "";

        return $this->bitcoinRPC->jsonRPC_client()
            ->jsonRPC_call($command, $endpoint, $params, $httpMethod);
    }
}