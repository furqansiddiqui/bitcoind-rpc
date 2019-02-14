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
 * Class DefaultClient
 * @package BitcoinRPC\Http
 */
class DefaultClient extends AbstractJSONClient
{
    public function jsonRPC_call(string $httpMethod, string $endpoint, string $id, string $method, ?array $params = null): DaemonResponse
    {

    }
}