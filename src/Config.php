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
 * Class Config
 * @package BitcoinRPC
 * @property-read int $scale
 * @property-read bool $validateCorePrivileges
 */
class Config
{
    /** @var BitcoinRPC */
    private $bitcoinRPC;
    /** @var int */
    private $scale;
    /** @var bool */
    private $validateCorePrivileges;

    /**
     * Config constructor.
     * @param BitcoinRPC $bitcoinRPC
     */
    public function __construct(BitcoinRPC $bitcoinRPC)
    {
        $this->bitcoinRPC = $bitcoinRPC;
        $this->scale = BitcoinRPC::SCALE;
        $this->validateCorePrivileges = true;
    }

    /**
     * @param $prop
     * @return mixed
     * @throws BitcoinRPCException
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "scale":
            case "validateCorePrivileges":
                return $this->$prop;
        }

        throw new BitcoinRPCException('Cannot retrieve value of inaccessible config property');
    }

    /**
     * @param int $scale
     * @return Config
     */
    public function scale(int $scale): self
    {
        $this->scale = $scale;
        return $this;
    }

    /**
     * @param bool $validate
     * @return Config
     */
    public function validateCorePrivileges(bool $validate): self
    {
        $this->validateCorePrivileges = $validate;
        return $this;
    }
}