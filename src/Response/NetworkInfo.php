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
 * Class NetworkInfo
 * @package BitcoinRPC\Response
 * @property-read int $version
 * @property-read string $subVersion
 * @property-read int $protocolVersion
 * @property-read int $connections
 */
class NetworkInfo
{
    /** @var int */
    private $version;
    /** @var string */
    private $subVersion;
    /** @var int */
    private $protocolVersion;
    /** @var int */
    private $connections;

    /**
     * NetworkInfo constructor.
     * @param array $obj
     * @throws ResponseObjectException
     */
    public function __construct(array $obj)
    {
        // Version
        $this->version = $obj["version"] ?? null;
        if (!is_int($this->version)) {
            throw ResponseObjectException::badParamValueType("NetworkInfo.version", "int", gettype($this->version));
        } elseif (!$this->version) {
            throw ResponseObjectException::badParamValueType("NetworkInfo.version");
        }

        // SubVersion
        $this->subVersion = $obj["subversion"] ?? null;
        if (!is_string($this->subVersion)) {
            throw ResponseObjectException::badParamValueType("NetworkInfo.subVersion", "String", gettype($this->subVersion));
        } elseif (!preg_match('/^\/[\w\s]+\:[\w\.\(\)]+\/$/', $this->subVersion)) {
            throw ResponseObjectException::badParamValueType("NetworkInfo.subVersion");
        }

        // Protocol Version
        $this->protocolVersion = $obj["protocolversion"] ?? null;
        if (!is_int($this->protocolVersion)) {
            throw ResponseObjectException::badParamValueType("NetworkInfo.protocolVersion", "int", gettype($this->protocolVersion));
        } elseif (!$this->protocolVersion) {
            throw ResponseObjectException::badParamValueType("NetworkInfo.protocolVersion");
        }

        // Protocol Version
        $this->connections = $obj["connections"] ?? null;
        if (!is_int($this->connections)) {
            throw ResponseObjectException::badParamValueType("NetworkInfo.connections", "int", gettype($this->connections));
        }
    }

    /**
     * @param $prop
     * @return mixed
     * @throws ResponseObjectException
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "version":
            case "subVersion":
            case "protocolVersion":
            case "connections":
                return $this->$prop;
        }

        throw new ResponseObjectException('Cannot retrieve value for inaccessible NetworkInfo prop');
    }
}