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

namespace BitcoinRPC\Response;

use BitcoinRPC\Exception\ResponseObjectException;
use BitcoinRPC\Validator;

/**
 * Class RawTransaction
 * @package BitcoinRPC\Response
 */
class RawTransaction
{
    /** @var mixed|null */
    public $version;
    /** @var string */
    public $txId;
    /** @var string */
    public $hash;
    /** @var int */
    public $size;
    /** @var int */
    public $vSize;
    /** @var mixed|null */
    public $lockTime;
    /** @var null|array */
    public $vIn;
    /** @var null|array */
    public $vOut;
    /** @var string */
    public $blockHash;
    /** @var int */
    public $confirmations;
    /** @var int */
    public $time;
    /** @var int */
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
        } elseif (!Validator::Hash($this->hash, 64)) {
            throw $this->unexpectedParamValue("hash", "hash64", gettype($this->hash));
        } elseif (!is_string($this->blockHash) && !is_null($this->blockHash)) {
            throw $this->unexpectedParamValue("blockHash", "hash64|null", gettype($this->hash));
        }

        if (is_string($this->blockHash)) {
            if (!Validator::Hash($this->blockHash, 64)) {
                throw $this->unexpectedParamValue("blockHash", "hash64", gettype($this->blockHash));
            }
        }

        // Size & vSize
        if (!is_int($this->size)) {
            throw $this->unexpectedParamValue("size", "int", gettype($this->size));
        } elseif (!is_int($this->vSize)) {
            throw $this->unexpectedParamValue("vSize", "int", gettype($this->vSize));
        }

        // Confirmations
        if (!is_int($this->confirmations)) {
            throw $this->unexpectedParamValue("confirmations", "int", gettype($this->confirmations));
        }

        // Time & BlockTime
        if (!is_int($this->time)) {
            throw $this->unexpectedParamValue("time", "int", gettype($this->time));
        } elseif (!is_int($this->blockTime)) {
            throw $this->unexpectedParamValue("blockTime", "int", gettype($this->blockTime));
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
     * @param null|string $expected
     * @param null|string $got
     * @return ResponseObjectException
     */
    private function unexpectedParamValue(string $param, ?string $expected = null, ?string $got = null): ResponseObjectException
    {
        $message = sprintf('Bad/unexpected value for param "%s"', $param);
        if ($expected) {
            $message .= sprintf(', expected "%s"', $expected);
        }

        if ($got) {
            $message .= sprintf(', got "%s"', $got);
        }


        return $this->exception($message);
    }

    /**
     * @param string $message
     * @return ResponseObjectException
     */
    public function exception(string $message): ResponseObjectException
    {
        $txId = $this->txId ?? "unknownTransaction";
        return new ResponseObjectException(
            sprintf('Transaction ["%s..."]: %s', substr($txId, 0, 8), $message)
        );
    }
}