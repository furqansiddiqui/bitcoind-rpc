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

use HttpClient\JSON_RPC\ResponseError;

/**
 * Class DaemonResponseError
 * @package BitcoinRPC\Http
 */
class DaemonResponseError
{
    /** @var int */
    public $code;
    /** @var string */
    public $message;
    /** @var string|array */
    public $data;

    /**
     * @param ResponseError $err
     * @return DaemonResponseError
     */
    public static function FromJSON_RPC(ResponseError $err): self
    {
        $error = new self();
        $error->code = $err->code;
        $error->message = $err->message;
        $error->data = $err->data;

        return $error;
    }
}