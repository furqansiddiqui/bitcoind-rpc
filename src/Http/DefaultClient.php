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

use BitcoinRPC\Exception\BitcoinRPCException;
use BitcoinRPC\Exception\ConnectionException;
use BitcoinRPC\Exception\DaemonException;
use HttpClient\Exception\JSON_RPC_Exception;
use HttpClient\JSON_RPC;

/**
 * Class DefaultClient
 * @package BitcoinRPC\Http
 */
class DefaultClient extends AbstractJSONClient
{
    /** @var JSON_RPC */
    private $jsonRPC;

    /**
     * DefaultClient constructor.
     * @param string $host
     * @param int $port
     * @throws \HttpClient\Exception\JSON_RPC_Exception
     */
    public function __construct(string $host, int $port)
    {
        parent::__construct($host, $port);

        $this->jsonRPC = new JSON_RPC("1.0");
        $this->jsonRPC->server($host, $port);
    }

    /**
     * @param string $method
     * @param string|null $endpoint
     * @param array|null $params
     * @param string|null $httpMethod
     * @return DaemonResponse
     * @throws BitcoinRPCException
     * @throws ConnectionException
     * @throws DaemonException
     */
    public function jsonRPC_call(string $method, ?string $endpoint = null, ?array $params = null, ?string $httpMethod = 'POST'): DaemonResponse
    {
        // Set credentials
        $this->jsonRPC->authentication()
            ->basic($this->auth->username, $this->auth->password);

        // Prepare & Send Request
        try {
            $res = $this->jsonRPC->request($httpMethod, $endpoint ?? "")
                ->id($this->requestId($method))
                ->method($method)
                ->params($params ?? [])
                ->send();
        } catch (JSON_RPC_Exception $e) {
            throw new ConnectionException($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            throw new BitcoinRPCException(
                sprintf('[%s][#%d]: %s', get_class($e), $e->getCode(), $e->getMessage())
            );
        }

        // Has error?
        if (!$res->result) {
            $errorMessage = $res->error->message;
            if (!$errorMessage) {
                $errorMessage = 'An unknown error occurred, failed to retrieve "result"';
            }

            throw new DaemonException($errorMessage, $res->error->code);
        }

        // Compile DaemonResponse
        $resHttpQuery = $res->http();

        $daemonResponse = new DaemonResponse($resHttpQuery->headers(), $resHttpQuery->array());
        $daemonResponse->httpStatusCode = $resHttpQuery->code();
        $daemonResponse->id = $res->id;
        $daemonResponse->result = $res->result;
        return $daemonResponse;
    }
}