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

namespace BitcoinRPC;

use BitcoinRPC\Exception\BitcoinRPCException;

/**
 * Class CorePrivileges
 * @package BitcoinRPC\Client
 * @property-read string $name
 * @property-read int $version
 * @property-read null|string $coin
 * @property-read bool $hasDynamicWallets
 */
class CorePrivileges
{
    /** @var BitcoinRPC */
    private $bitcoinRPC;
    /** @var string */
    private $name;
    /** @var int */
    private $version;

    /** @var null|string */
    private $coin;
    /** @var bool */
    private $hasDynamicWallets;

    /**
     * CorePrivileges constructor.
     * @param BitcoinRPC $bitcoinRPC
     * @throws \BitcoinRPC\Exception\BitcoinRPCException
     * @throws \BitcoinRPC\Exception\ResponseObjectException
     */
    public function __construct(BitcoinRPC $bitcoinRPC)
    {
        $this->bitcoinRPC = $bitcoinRPC;
        $networkInfo = $this->bitcoinRPC->getNetworkInfo();

        // Name & Version
        $networkInfoName = explode(":", trim($networkInfo->subVersion, "/"));
        $this->name = trim($networkInfoName[0]);
        $this->version = $networkInfo->version;

        // Detect coin identifier
        $this->deduceCoin();

        // Detect Privileges
        $this->deducePrivileges();
    }

    /**
     * @param $prop
     * @return mixed
     * @throws BitcoinRPCException
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "name":
            case "version":
            case "coin":
            case "hasDynamicWallets":
                return $this->$prop;
        }

        throw new BitcoinRPCException('Cannot retrieve value of inaccessible CorePrivileges property');
    }

    /**
     * @return void
     */
    private function deduceCoin(): void
    {
        switch (strtolower($this->name)) {
            case "satoshi":
                $this->coin = "BTC";
                break;
            case "bitcoin abc":
                $this->coin = "BCH";
                break;
            case "dash core":
                $this->coin = "DASH";
                break;
            case "litecoin core":
                $this->coin = "LTC";
                break;
        }
    }

    /**
     * @return void
     */
    private function deducePrivileges(): void
    {
        $this->hasDynamicWallets = false;

        // Dynamic wallets
        if (in_array($this->coin, ["BTC"])) {
            if ($this->version >= 170000) {
                $this->hasDynamicWallets = true;
            }
        }
    }
}