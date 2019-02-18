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

namespace BitcoinRPC\Events;

use BitcoinRPC\Client\Wallets;

/**
 * Class WalletsEvents
 * @package BitcoinRPC\Events
 */
class WalletsEvents extends AbstractEventsLib
{
    /** @var Wallets */
    private $wallets;

    /**
     * WalletsEvents constructor.
     * @param Wallets $wallets
     */
    public function __construct(Wallets $wallets)
    {
        parent::__construct();
        $this->wallets = $wallets;
    }

    /**
     * @param callable $func
     * @return WalletsEvents
     * @throws \BitcoinRPC\Exception\EventsException
     */
    public function onLoad(callable $func): self
    {
        $this->on("wallet.load", $func);
        return $this;
    }

    /**
     * @param callable $func
     * @return WalletsEvents
     * @throws \BitcoinRPC\Exception\EventsException
     */
    public function onUnload(callable $func): self
    {
        $this->on("wallet.unload", $func);
        return $this;
    }

    /**
     * @param callable $func
     * @return WalletsEvents
     * @throws \BitcoinRPC\Exception\EventsException
     */
    public function onUnlock(callable $func): self
    {
        $this->on("wallet.unlock", $func);
        return $this;
    }
}