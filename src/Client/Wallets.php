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
use BitcoinRPC\Client\Wallets\Wallet;
use BitcoinRPC\Exception\WalletsException;

/**
 * Class Wallets
 * @package BitcoinRPC\Client
 */
class Wallets
{
    /** @var BitcoinRPC */
    private $bitcoinRPC;
    /** @var array */
    private $wallets;
    /** @var null|array */
    private $_loadedWallets;


    /**
     * Wallets constructor.
     * @param BitcoinRPC $bitcoinRPC
     */
    public function __construct(BitcoinRPC $bitcoinRPC)
    {
        $this->bitcoinRPC = $bitcoinRPC;
        $this->wallets = [];
    }

    /**
     * @param string $name
     * @param bool $suppressWarning
     * @return Wallet
     * @throws WalletsException
     */
    public function create(string $name, bool $suppressWarning = false): Wallet
    {
        if (!preg_match('/^[\w\-]{3,40}$/i', $name)) {
            throw new WalletsException('Invalid wallet name to create');
        }

        $res = $this->bitcoinRPC->jsonRPC_client()->jsonRPC_call("createwallet", null, [$name]);
        $createdWallet = $res->result;
        if (!is_array($createdWallet) || !array_key_exists("name", $createdWallet)) {
            throw new WalletsException('Failed to create new wallet, invalid response');
        }

        if ($createdWallet["name"] !== $name) {
            throw new $createdWallet('Create wallet name does not match with request');
        }

        $warning = $loadWallet["warning"] ?? null;
        if ($warning) {
            if (!$suppressWarning) {
                throw new $createdWallet(sprintf('Create wallet [%s] warning: %s', $name, $warning));
            }
        }

        return $this->get($name);
    }

    /**
     * @param string|null $name
     * @return Wallet
     * @throws \BitcoinRPC\Exception\WalletsException
     */
    public function get(?string $name = "wallet.dat"): Wallet
    {
        $key = $name ? strtolower($name) : "_default";
        if (array_key_exists($key, $this->wallets)) {
            return $this->wallets[$key];
        }

        $wallet = new Wallet($this->bitcoinRPC, $this, $name);
        $this->wallets[$key] = $wallet;
        return $wallet;
    }

    /**
     * @param bool $forceRefreshList
     * @return array
     * @throws WalletsException
     */
    public function loadedWallets(bool $forceRefreshList = false): array
    {
        if (!is_array($this->_loadedWallets) || $forceRefreshList) {
            $loadedWallets = $this->bitcoinRPC->jsonRPC_client()->get("listwallets");
            if (!is_array($loadedWallets)) {
                throw WalletsException::unexpectedResultType("listWallets", "Array", gettype($loadedWallets));
            }

            $this->_loadedWallets = $loadedWallets;
        }

        return $this->_loadedWallets;
    }

    /**
     * @param string $name
     * @param bool $forceRefreshList
     * @return bool
     * @throws WalletsException
     */
    public function isLoaded(string $name, bool $forceRefreshList = false): bool
    {
        $loadedList = $this->loadedWallets($forceRefreshList);
        return in_array($name, $loadedList);
    }
}