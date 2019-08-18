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

namespace BitcoinRPC\Response;

use BitcoinRPC\Exception\ResponseObjectException;
use BitcoinRPC\Validator;

/**
 * Class RawTransaction
 * @package BitcoinRPC\Response
 */
class RawTransaction implements BitcoindResponseInterface
{
    /** @var mixed|null */
    public $version;
    /** @var string */
    public $txId;
    /** @var string */
    public $hash;
    /** @var int|null */
    public $size;
    /** @var int|null */
    public $vSize;
    /** @var mixed|null */
    public $lockTime;
    /** @var null|array */
    public $vIn;
    /** @var null|array */
    public $vOut;
    /** @var string|null */
    public $blockHash;
    /** @var int|null */
    public $confirmations;
    /** @var int */
    public $time;
    /** @var int|null */
    public $blockTime;

    /**
     * RawTransaction constructor.
     * @param array $obj
     * @throws ResponseObjectException
     */
    public function __construct(array $obj)
    {
        $this->vIn = null;
        $this->vOut = null;

        // Copy
        $this->version = $obj["version"] ?? null;
        $this->txId = $obj["txid"] ?? null;
        $this->hash = $obj["hash"] ?? null;
        $this->size = $obj["size"] ?? null;
        $this->vSize = $obj["vsize"] ?? null;
        $this->lockTime = $obj["locktime"] ?? null;
        $this->blockHash = $obj["blockhash"] ?? null;
        $this->confirmations = $obj["confirmations"] ?? null;
        $this->time = $obj["time"] ?? null;
        $this->blockTime = $obj["blocktime"] ?? null;

        // Validate
        // TxID and Hash and BlockHash
        if (!Validator::Hash($this->txId, 64)) {
            throw $this->unexpectedParamValue("txId", "hash64", gettype($this->txId));
        }

        if (!is_string($this->blockHash) && !is_null($this->blockHash)) {
            throw $this->unexpectedParamValue("blockHash", "hash64|null", gettype($this->blockHash));
        }

        if (is_string($this->blockHash)) {
            if (!Validator::Hash($this->blockHash, 64)) {
                throw $this->unexpectedParamValue("blockHash", "hash64", gettype($this->blockHash));
            }
        }

        // Size & vSize
        if (!is_int($this->size) && !is_null($this->size)) {
            throw $this->unexpectedParamValue("size", "int", gettype($this->size));
        }

        // Confirmations
        if (!is_int($this->confirmations) && !is_null($this->confirmations)) {
            throw $this->unexpectedParamValue("confirmations", "int|null", gettype($this->confirmations));
        }

        // Time & BlockTime
        if (!is_int($this->time) && !is_null($this->time)) {
            throw $this->unexpectedParamValue("time", "int|null", gettype($this->time));
        } elseif (!is_int($this->blockTime) && !is_null($this->blockTime)) {
            throw $this->unexpectedParamValue("blockTime", "int|null", gettype($this->blockTime));
        }

        // Inputs
        $inputs = $obj["vin"] ?? null;
        if (is_array($inputs)) {
            $this->vIn = $inputs;
        }

        // Outputs
        $outputs = $obj["vout"] ?? null;
        if (!is_array($outputs)) {
            throw $this->unexpectedParamValue("vout", "array", gettype($outputs));
        }

        $this->vOut = $outputs;
    }

    /**
     * @param string $param
     * @param string|null $expected
     * @param string|null $got
     * @return ResponseObjectException
     */
    private function unexpectedParamValue(string $param, ?string $expected = null, ?string $got = null): ResponseObjectException
    {
        try {
            throw ResponseObjectException::badParamValueType($param, $expected, $got);
        } catch (ResponseObjectException $e) {
            $txId = "unknown-transaction";
            if (is_string($this->txId) && strlen($this->txId) === 64) {
                $txId = substr($this->txId, 0, 12) . "...";
            }

            return new ResponseObjectException(sprintf('Tx[%s]: %s', $txId, $e->getMessage()));
        }
    }
}
