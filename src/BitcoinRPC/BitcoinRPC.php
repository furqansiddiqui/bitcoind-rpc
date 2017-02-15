<?php
declare(strict_types=1);

namespace BitcoinRPC;

use BitcoinRPC\Client\BlockChain;
use BitcoinRPC\Client\Wallet;
use BitcoinRPC\Exception\ConnectionException;
use BitcoinRPC\Exception\DaemonException;
use HttpClient\Request;

/**
 * Class BitcoinRPC
 * @package BitcoinRPC
 */
class BitcoinRPC
{
    const BCMATH_SCALE  =   8;

    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var string */
    private $username;
    /** @var string */
    private $password;
    /** @var bool */
    private $sslUse;
    /** @var null|string */
    private $sslCA;

    /** @var Wallet */
    private $wallet;
    /** @var BlockChain */
    private $blockChain;

    /**
     * BitcoinRPC constructor.
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @throws BitcoinRPCException
     */
    public function __construct(string $host, int $port, string $username, string $password)
    {
        if(!extension_loaded("bcmath")) {
            throw new BitcoinRPCException('Bitcoin RPC client requires "bcmath" extension');
        }

        $this->host =   $host;
        $this->port =   $port;
        $this->username =   $username;
        $this->password =   $password;
        $this->sslUse   =   false;

        $this->wallet   =   new Wallet($this);
        $this->blockChain   =   new BlockChain($this);
    }

    /**
     * @param string $caPath
     * @return BitcoinRPC
     * @throws BitcoinRPCException
     */
    public function useSSL(string $caPath = null) : self
    {
        $this->sslUse   =   true;

        if(is_string($caPath)) {
            if(!@is_readable($caPath)) {
                throw new BitcoinRPCException(
                    sprintf(
                        'CA bundle file "%1$s" not found in "%2$s',
                        basename($caPath),
                        dirname($caPath)
                    )
                );
            }

            $this->sslCA    =   $caPath;
        }

        return $this;
    }

    /**
     * @return Wallet
     */
    public function wallet() : Wallet
    {
        return $this->wallet;
    }

    /**
     * @return BlockChain
     */
    public function blockChain() : BlockChain
    {
        return $this->blockChain;
    }

    /**
     * @param string $endpoint
     * @return string
     */
    private function url(string $endpoint = "") : string
    {
        return sprintf(
            '%s://%s:%s/%s',
            $this->sslUse ? "https" : "http",
            $this->host,
            $this->port,
            $endpoint
        );
    }

    /**
     * @param Request $request
     * @return Request
     */
    private function prepare(Request $request) : Request
    {
        $request->authentication()->basic($this->username, $this->password);
        $request->ssl()->check($this->sslUse);
        if(isset($this->sslCA)) {
            $request->ssl()->setCA($this->sslCA);
        }

        return $request;
    }

    /**
     * @param Request $request
     * @param string $command
     * @param array|null $params
     * @return mixed
     * @throws ConnectionException
     * @throws DaemonException
     */
    public function jsonRPC(Request $request, string $command, array $params = null)
    {
        // Prepare Json RPC Call
        $requestId  =   sprintf('%s_%d', $command, time());
        $request->setUrl($this->url());
        $request->payload(
            [
                "jsonrpc"   =>  "1.0",
                "id"    =>  $requestId,
                "method"    =>  $command,
                "params"    =>  $params ?? []
            ],
            "json"
        );

        // Send JSON RPC Request to Bitcoin daemon
        try {
            $this->prepare($request);
            $response   =   $request->send();
        } catch (\Exception $e) {
            throw new ConnectionException($e->getMessage(), $e->getCode());
        }

        // Check HTTP response code
        if($response->responseCode()    !== 200) {
            // Todo: Throw error as per response code
            //throw new DaemonException(sprintf('Unexpected HTTP response code: %d', $response->responseCode()));
        }

        // Check response body
        $result =   $response->getBody();
        if(!is_array($result)   ||  !count($result)) {
            throw new DaemonException('Unexpected response from server');
        }

        // Cross-check response ID with request ID
        $responseId =   $result["id"] ?? null;
        if($responseId  !== $requestId) {
            throw new DaemonException('Response does not belong to sent request');
        }

        // Check for Daemon Error
        $error  =   $result["error"] ?? null;
        $result =   $result["result"] ?? null;
        if(!is_null($error) ||  is_null($result)) {
            $errorCode  =   intval($error["code"] ?? 0);
            $errorMessage   =   $error["message"] ?? 'An error occurred';
            throw new DaemonException($errorMessage, $errorCode);
        }

        return $result;
    }
}