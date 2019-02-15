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

namespace BitcoinRPC\Client\Wallets;

use BitcoinRPC\BitcoinRPC;
use BitcoinRPC\Client\Wallets;
use BitcoinRPC\DataTypes;
use BitcoinRPC\Exception\WalletsException;
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
    /** @var Wallets */
    private $wallets;

    /** @var null|string */
    private $name;
    /** @var null|string */
    private $passPhrase;
    /** @var bool */
    private $_isLoaded;
    /** @var null|int */
    private $_unlockedUntil;

    /**
     * Wallet constructor.
     * @param BitcoinRPC $bitcoinRPC
     * @param Wallets $wallets
     * @param string|null $name
     * @throws WalletsException
     */
    public function __construct(BitcoinRPC $bitcoinRPC, Wallets $wallets, ?string $name = "wallet.dat")
    {
        if (is_string($name) && !preg_match('/[\w\-]+(\.[a-z]{2,8})?/', $name)) {
            throw new WalletsException('Invalid wallet file/name');
        }

        $this->bitcoinRPC = $bitcoinRPC;
        $this->wallets = $wallets;

        $this->name = $name;
        $this->_isLoaded = false;
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
     * @throws WalletsException
     */
    public function unlock(int $seconds): bool
    {
        if (!$this->passPhrase) {
            throw new WalletsException('Wallet passphrase not set');
        }

        $res = $this->walletRPC("walletpassphrase", [$this->passPhrase, $seconds]);
        if ($res->httpStatusCode !== 200) {
            throw new WalletsException('Failed to unlock wallet');
        }

        $this->_unlockedUntil = time() + $seconds;
        return true;
    }

    /**
     * @return bool
     */
    public function isUnlocked(): bool
    {
        if (is_int($this->_unlockedUntil)) {
            if ($this->_unlockedUntil > time()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|null $addr
     * @param int $confirmations
     * @return string
     * @throws WalletsException
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

        $res = $this->walletRPC("getbalance", $params);
        $balance = DataTypes::AmountAsString($res->result, $this->bitcoinRPC->config()->scale);
        if (!$balance) {
            throw WalletsException::unexpectedResultType("getBalance");
        }

        return $balance;
    }

    /**
     * @return string
     * @throws WalletsException
     */
    public function getNewAddress(): string
    {
        $res = $this->walletRPC("getnewaddress");
        if (!is_string($res->result)) {
            throw WalletsException::unexpectedResultType("getNewAddress", "String", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @param string $txHash
     * @return array
     * @throws WalletsException
     */
    public function getTransaction(string $txHash): array
    {
        $res = $this->walletRPC("gettransaction", [$txHash]);
        if (!is_array($res->result)) {
            throw WalletsException::unexpectedResultType("getTransaction", "Array", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @param string $addr
     * @param string $amount
     * @return string
     * @throws WalletsException
     */
    public function sendToAddress(string $addr, string $amount): string
    {
        $res = $this->walletRPC("sendtoaddress", [$addr, $amount]);
        if (!is_string($res->result)) {
            throw WalletsException::unexpectedResultType("sendToAddress", "String", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @param int $minConfirmations
     * @param int|null $maxConfirmations
     * @param array|null $addresses
     * @return UnspentOutputs
     * @throws WalletsException
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
            throw WalletsException::unexpectedResultType("listUnspent", "Array", gettype($res->result));
        }

        return new UnspentOutputs($res->result);
    }

    /**
     * @param array $inputs
     * @param array $outputs
     * @return string
     * @throws WalletsException
     */
    public function createRawTransaction(array $inputs, array $outputs): string
    {
        $res = $this->walletRPC("createrawtransaction", [$inputs, $outputs]);
        if (!is_string($res->result)) {
            throw WalletsException::unexpectedResultType("createRawTransaction", "String", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @param string $encodedRawTransaction
     * @return SignedRawTransaction
     * @throws WalletsException
     */
    public function signRawTransaction(string $encodedRawTransaction): SignedRawTransaction
    {
        $res = $this->walletRPC("signrawtransaction", [$encodedRawTransaction]);
        if (!is_array($res->result)) {
            throw WalletsException::unexpectedResultType("signRawTransaction", "Array", gettype($res->result));
        }

        return new SignedRawTransaction($res->result);
    }

    /**
     * @param string $encodedRawTransaction
     * @return SignedRawTransaction
     * @throws WalletsException
     */
    public function signRawTransactionWithWallet(string $encodedRawTransaction): SignedRawTransaction
    {
        $res = $this->walletRPC("signrawtransactionwithwallet", [$encodedRawTransaction]);
        if (!$res->result) {
            throw WalletsException::unexpectedResultType("signRawTransactionWithWallet", "Array", gettype($res->result));
        }

        return new SignedRawTransaction($res->result);
    }

    /**
     * @param string $signedTransaction
     * @return string
     * @throws WalletsException
     */
    public function sendRawTransaction(string $signedTransaction): string
    {
        $res = $this->walletRPC("sendrawtransaction", [$signedTransaction]);
        if (!Validator::Hash($res->result, 64)) {
            throw WalletsException::unexpectedResultType("sendRawTransaction", "Hash64", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @return PrepareTransaction
     */
    public function prepareTransaction(): PrepareTransaction
    {
        return new PrepareTransaction($this->bitcoinRPC, $this);
    }

    /**
     * @param bool $checkAtNode
     * @param bool $forceRefreshList
     * @return bool
     * @throws WalletsException
     * @throws \BitcoinRPC\Exception\BitcoinRPCException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     */
    public function isLoaded(bool $checkAtNode = false, bool $forceRefreshList = false): bool
    {
        $this->hasDynamicLoading();

        if ($checkAtNode) {
            $this->wallets->isLoaded($this->name, $forceRefreshList);
        }

        return $this->_isLoaded;
    }

    /**
     * @throws WalletsException
     * @throws \BitcoinRPC\Exception\BitcoinRPCException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     */
    private function hasDynamicLoading(): void
    {
        if ($this->bitcoinRPC->config()->validateCorePrivileges) {
            if (!$this->bitcoinRPC->corePrivileges()->hasDynamicWallets) {
                throw new WalletsException('This node does not support dynamic loading/unloading of wallets');
            }
        }
    }

    /**
     * @param bool $suppressWarning
     * @throws WalletsException
     * @throws \BitcoinRPC\Exception\BitcoinRPCException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     */
    public function load(bool $suppressWarning = false): void
    {
        // Check if node supports this method?
        $this->hasDynamicLoading();

        $jsonRPC_client = $this->bitcoinRPC->jsonRPC_client();

        try {
            $res = $jsonRPC_client->jsonRPC_call("loadwallet", null, [$this->name]);
        } catch (\Exception $e) {
            $lastCommandError = $jsonRPC_client->lastCommandError();
            if (!$lastCommandError) {
                throw new WalletsException('JSON RPC client did not set lastCommandError');
            }

            if (preg_match('/duplicate \-wallet filename specified/i', $lastCommandError->message)) {
                $this->_isLoaded = true;
                return;
            }
        }

        if (!isset($res)) {
            throw new WalletsException('Wallet load RPC command error');
        }

        if (!is_array($res->result) || !array_key_exists("name", $res->result)) {
            throw WalletsException::unexpectedResultType("loadWallet", "Array", gettype($res->result));
        }

        if ($res->result["name"] !== $this->name) {
            throw new WalletsException('Loaded wallet name does not match');
        }

        $warning = $res->result["warning"] ?? null;
        if ($warning) {
            if (!$suppressWarning) {
                throw new WalletsException(sprintf('Load wallet [%s] warning: %s', $this->name, $warning));
            }
        }

        $this->_isLoaded = true;
    }

    /**
     * @throws WalletsException
     * @throws \BitcoinRPC\Exception\BitcoinRPCException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     */
    public function unload(): void
    {
        $this->hasDynamicLoading();

        if ($this->_unlockedUntil) {
            if (time() <= $this->_unlockedUntil) {
                // Cannot allow since it will crash daemon RPC server
                throw new WalletsException(
                    sprintf('Wallet [%s] was unlocked until %d, currently %d, aborting unload as it may potentially crash daemon RPC server', $this->name, $this->_unlockedUntil, time())
                );
            }
        }

        $res = $this->bitcoinRPC->jsonRPC_client()
            ->jsonRPC_call("unloadwallet", null, [$this->name]);
        if ($res->httpStatusCode !== 200) {
            throw WalletsException::unexpectedResultType("unloadWallet", "NULL");
        }
    }

    /**
     * @param string $passphrase
     * @return string
     * @throws WalletsException
     */
    public function encrypt(string $passphrase): string
    {
        if (!preg_match('/^[a-zA-Z0-9\-\_\.]{3,64}$/', $passphrase)) {
            throw new WalletsException('Invalid encryption passphrase');
        }

        $res = $this->walletRPC("encryptwallet", [$passphrase]);
        if (!is_string($res->result)) {
            throw WalletsException::unexpectedResultType("encryptWallet", "String", gettype($res->result));
        }

        return $res->result;
    }

    /**
     * @param string $command
     * @param array|null $params
     * @return DaemonResponse
     */
    private function walletRPC(string $command, ?array $params = null): DaemonResponse
    {
        $endpoint = null;
        if ($this->name) {
            $endpoint = sprintf('/wallet/%s', $this->name);
        }

        return $this->bitcoinRPC->jsonRPC_client()
            ->jsonRPC_call($command, $endpoint, $params);
    }
}