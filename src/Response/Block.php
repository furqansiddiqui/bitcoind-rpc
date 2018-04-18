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
 * Class Block
 * @package BitcoinRPC\Response
 */
class Block
{
    /** @var string */
    public $hash;
    /** @var int */
    public $confirmations;
    /** @var mixed|null */
    public $strippedSize;
    /** @var mixed|null */
    public $size;
    /** @var int */
    public $weight;
    /** @var int */
    public $height;
    /** @var mixed|null */
    public $version;
    /** @var mixed|null */
    public $versionHex;
    /** @var mixed|null */
    public $merkleRoot;
    /** @var array */
    public $tx;
    /** @var int */
    public $time;
    /** @var int */
    public $medianTime;
    /** @var int */
    public $nonce;
    /** @var mixed|null */
    public $bits;
    /** @var int|float */
    public $difficulty;
    /** @var mixed|null */
    public $chainWork;
    /** @var mixed|null */
    public $previousBlockHash;
    /** @var mixed|null */
    public $nextBlockHash;

    /**
     * Block constructor.
     * @param array $obj
     * @throws ResponseObjectException
     */
    public function __construct(array $obj)
    {
        $this->hash = $obj["hash"] ?? null;
        $this->confirmations = $obj["confirmations"] ?? null;
        $this->strippedSize = $obj["strippedsize"] ?? null;
        $this->size = $obj["size"] ?? null;
        $this->weight = $obj["weight"] ?? null;
        $this->height = $obj["height"] ?? null;
        $this->version = $obj["version"] ?? null;
        $this->versionHex = $obj["versionhex"] ?? null;
        $this->merkleRoot = $obj["merkleroot"] ?? null;
        $this->tx = $obj["tx"] ?? null;
        $this->time = $obj["time"] ?? null;
        $this->medianTime = $obj["mediantime"] ?? null;
        $this->nonce = $obj["none"] ?? null;
        $this->bits = $obj["bits"] ?? null;
        $this->difficulty = $obj["difficulty"] ?? null;
        $this->chainWork = $obj["chainWork"] ?? null;
        $this->previousBlockHash = $obj["previousBlockHash"] ?? null;
        $this->nextBlockHash = $obj["nextBlockHash"] ?? null;

        // Validate
        // Hash
        if (!Validator::Hash($this->hash, 64)) {
            throw $this->unexpectedParamValue("hash", "hash64", gettype($this->hash));
        }

        // Confirmations
        if (!is_int($this->confirmations)) {
            throw $this->unexpectedParamValue("confirmations", "int", gettype($this->confirmations));
        }

        // Weight
        if (!is_int($this->weight)) {
            throw $this->unexpectedParamValue("weight", "int", gettype($this->weight));
        }

        // Height
        if (!is_int($this->height)) {
            throw $this->unexpectedParamValue("height", "int", gettype($this->height));
        }

        // Transactions
        if (!is_array($this->tx)) {
            throw $this->unexpectedParamValue("tx", "array", gettype($this->tx));
        }

        // Time & Median Time
        if (!is_int($this->time)) {
            throw $this->unexpectedParamValue("time", "int", gettype($this->time));
        } elseif (!is_int($this->medianTime)) {
            throw $this->unexpectedParamValue("medianTime", "int", gettype($this->medianTime));
        }

        // Nonce
        if (!is_int($this->nonce)) {
            throw $this->unexpectedParamValue("nonce", "int", gettype($this->nonce));
        }

        // Difficulty
        if (!is_numeric($this->difficulty)) {
            throw $this->unexpectedParamValue("difficulty", "numeric", gettype($this->nonce));
        }
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
    private function exception(string $message): ResponseObjectException
    {
        $blockId = is_int($this->height) ? $this->height : "unknown";
        return new ResponseObjectException(
            sprintf('Block [%s]: %s', $blockId, $message)
        );
    }
}