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
use BitcoinRPC\Exception\PrepareTransactionException;
use BitcoinRPC\Response\Output;
use BitcoinRPC\Response\SignedRawTransaction;
use BitcoinRPC\Response\UnspentOutputs;
use BitcoinRPC\Validator;

/**
 * Class PrepareTransaction
 * @package BitcoinRPC\Client\Wallet
 */
class PrepareTransaction
{
    /** @var BitcoinRPC */
    private $bitcoinRPC;
    /** @var Wallet */
    private $wallet;

    /** @var array */
    private $outputs;
    /** @var int */
    private $totalOutputsCount;
    /** @var string */
    private $totalOutputsAmount;
    /** @var null|string */
    private $fee;
    /** @var null|int */
    private $feePerByte;
    /** @var null|string */
    private $changeAddress;
    /** @var null|array|UnspentOutputs */
    private $inputs;

    /** @var null|callable */
    private $_event_changeAddressRequired;

    /**
     * PrepareTransaction constructor.
     * @param BitcoinRPC $bitcoinRPC
     * @param Wallet $wallet
     */
    public function __construct(BitcoinRPC $bitcoinRPC, Wallet $wallet)
    {
        $this->bitcoinRPC = $bitcoinRPC;
        $this->wallet = $wallet;
        $this->outputs = [];
        $this->totalOutputsCount = 0;
        $this->totalOutputsAmount = "0.00000000";
    }

    /**
     * @param string $address
     * @param string $amount
     * @return PrepareTransaction
     * @throws PrepareTransactionException
     */
    public function output(string $address, string $amount): self
    {
        if (!Validator::Address($address)) {
            throw new PrepareTransactionException('Invalid output address');
        }

        if (!Validator::BcAmount($amount, false)) {
            throw new PrepareTransactionException('Invalid output amount');
        }

        $this->outputs[$address] = $amount;
        $this->totalOutputsAmount = bcadd($this->totalOutputsAmount, $amount, 8);
        $this->totalOutputsCount++;
        return $this;
    }

    /**
     * @param string $fee
     * @return PrepareTransaction
     * @throws PrepareTransactionException
     */
    public function fee(string $fee): self
    {
        if (!Validator::BcAmount($fee)) {
            throw new PrepareTransactionException('Invalid value for transaction fee');
        }

        $this->fee = $fee;
        $this->feePerByte = null;
        return $this;
    }

    /**
     * @param int $sat
     * @return PrepareTransaction
     * @throws PrepareTransactionException
     */
    public function feePerByte(int $sat): self
    {
        if ($sat < 1 || $sat > 500) {
            throw new PrepareTransactionException('Fee per byte must be between 1 and 500 satoshis');
        }

        $this->fee = null;
        $this->feePerByte = $sat;
        return $this;
    }

    /**
     * @param string $addr
     * @return PrepareTransaction
     * @throws PrepareTransactionException
     */
    public function changeAddress(string $addr): self
    {
        if (!Validator::Address($addr)) {
            throw new PrepareTransactionException('Invalid address for transaction change return/amount');
        }

        $this->changeAddress = $addr;
        return $this;
    }

    /**
     * @param callable $callback
     * @return PrepareTransaction
     */
    public function changeAddressCallback(callable $callback): self
    {
        $this->_event_changeAddressRequired = $callback;
        return $this;
    }

    /**
     * @param $inputs
     * @return PrepareTransaction
     * @throws PrepareTransactionException
     */
    public function inputs($inputs): self
    {
        // UnspentOutputs object?
        if ($inputs instanceof UnspentOutputs) {
            $this->inputs = $inputs;
            return $this;
        }

        // Array?
        if (is_array($inputs)) {
            foreach ($inputs as $input) {
                if (!$input instanceof Output) {
                    throw new PrepareTransactionException('All inputs must be instance of "Output" object');
                }
            }

            return $this;
        }

        throw new PrepareTransactionException('Invalid argument for inputs method');
    }

    /**
     * @return string
     * @throws PrepareTransactionException
     * @throws \BitcoinRPC\Exception\BitcoinRPCException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     * @throws \BitcoinRPC\Exception\WalletsException
     */
    public function send(): string
    {
        if ($this->feePerByte) {
            $pseudoFeeTx = $this->createSignedTransaction("0.0001"); // Use 0.0001 as pseudo fee
            $transactionBytes = intval(strlen($pseudoFeeTx->hex) / 2); // 2 hexits per byte
            if ($transactionBytes < 166 && $transactionBytes > 102400) {
                throw new PrepareTransactionException('Failed to determine transaction size in bytes for fee');
            }

            $this->fee = bcdiv(strval($this->feePerByte * $transactionBytes), bcpow("10", "8", 0), 8);
        }

        // Fee is set?
        if (!$this->fee) {
            throw new PrepareTransactionException('Transaction fee (or fee per byte) is not defined');
        }

        $signedTx = $this->createSignedTransaction($this->fee);

        // Send transaction
        $txId = $this->wallet->sendRawTransaction($signedTx->hex);
        return $txId;
    }

    /**
     * @param string $transactionFee
     * @return SignedRawTransaction
     * @throws PrepareTransactionException
     * @throws \BitcoinRPC\Exception\BitcoinRPCException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     * @throws \BitcoinRPC\Exception\WalletsException
     */
    private function createSignedTransaction(string $transactionFee): SignedRawTransaction
    {
        // Have inputs?
        if (!$this->inputs) {
            $this->inputs = $this->wallet->listUnspent(1);
        }

        // Total amount required
        $totalTransactionAmount = bcadd($this->totalOutputsAmount, $transactionFee, 8);
        $totalInputsAmount = "0.00000000";
        $txInputs = [];
        $txOutputs = $this->outputs;

        $amountNeeded = $totalTransactionAmount;
        /** @var Output $input */
        foreach ($this->inputs as $input) {
            if (bccomp($amountNeeded, "0", 8) !== 1) {
                break; // No more inputs required
            }

            $inputAmount = strval($input->amount);
            if (!Validator::BcAmount($inputAmount)) {
                continue; // skip
            }

            $totalInputsAmount = bcadd($totalInputsAmount, $inputAmount, 8);
            $amountNeeded = bcsub($amountNeeded, $inputAmount, 8);
            $txInputs[] = [
                "txid" => $input->txid,
                "vout" => $input->vout
            ];
        }

        // Have sufficient amounts?
        if (bccomp($amountNeeded, "0", 8) === 1) {
            throw new PrepareTransactionException('Insufficient transaction inputs');
        }

        // Change amount
        $changeAmount = bcsub($totalInputsAmount, $totalTransactionAmount, 8);
        if (bccomp($changeAmount, "0", 8) === 1) {
            // Change address required
            // Try callback first
            if ($this->_event_changeAddressRequired) {
                call_user_func($this->_event_changeAddressRequired);
            }

            // Change address have?
            if (!Validator::Address($this->changeAddress)) {
                throw new PrepareTransactionException('A transaction change address is required');
            }

            $txOutputs[$this->changeAddress] = $changeAmount;
        }

        // Create Raw Transaction
        $rawTransaction = $this->wallet->createRawTransaction($txInputs, $txOutputs);

        // Which method to use?
        $signingMethod = "signRawTransaction";
        if ($this->bitcoinRPC->config()->validateCorePrivileges) {
            if ($this->bitcoinRPC->corePrivileges()->hasDynamicWallets) {
                $signingMethod = "signRawTransactionWithWallet";
            }
        }

        // Sign created Transaction
        $signedTx = call_user_func_array([$this->wallet, $signingMethod], [$rawTransaction]);
        if ($signedTx->complete !== true) {
            throw new PrepareTransactionException('Signed TX is not complete, requires external keys');
        }

        return $signedTx;
    }
}