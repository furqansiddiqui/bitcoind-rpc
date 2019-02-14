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

namespace BitcoinRPC\Http;


/**
 * Class DaemonResponse
 * @package BitcoinRPC\Http
 */
class DaemonResponse
{
    /** @var null|array */
    private $headers;
    /** @var null|array */
    private $body;

    /** @var int */
    public $httpStatusCode;
    /** @var string */
    public $id;
    /** @var mixed */
    public $result;

    /**
     * DaemonResponse constructor.
     * @param array|null $headers
     * @param array|null $body
     */
    public function __construct(?array $headers = null, ?array $body = null)
    {
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * @return array|null
     */
    public function headers(): ?array
    {
        return $this->headers;
    }

    /**
     * @return array|null
     */
    public function body(): ?array
    {
        return $this->body;
    }
}