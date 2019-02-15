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

/**
 * Class UnspentOutputs
 * @package BitcoinRPC\Response
 */
class UnspentOutputs implements \Iterator, \Countable, BitcoindResponseInterface
{
    /** @var array */
    private $outputs;
    /** @var int */
    private $count;
    /** @var int */
    private $index;

    /**
     * UnspentOutputs constructor.
     * @param array $obj
     * @throws ResponseObjectException
     */
    public function __construct(array $obj)
    {
        $this->outputs = [];
        $this->count = 0;
        $this->index = 0;

        foreach ($obj as $output) {
            $this->outputs[] = new Output($output);
            $this->count++;
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @return Output
     */
    public function current(): Output
    {
        return $this->outputs[$this->index];
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->index;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->outputs[$this->index]);
    }
}