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

use HttpClient\Response\JSONResponse;

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
     * @param JSONResponse|null $res
     */
    public function __construct(?JSONResponse $res = null)
    {
        if ($res) {
            $this->headers = $res->headers();
            $this->body = $res->array();
        }
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