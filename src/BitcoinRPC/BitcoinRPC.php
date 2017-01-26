<?php
declare(strict_types=1);

namespace BitcoinRPC;

use HttpClient\Request;

/**
 * Class BitcoinRPC
 * @package BitcoinRPC
 */
class BitcoinRPC
{
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

    /**
     * BitcoinRPC constructor.
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     */
    public function __construct(string $host, int $port, string $username, string $password)
    {
        $this->host =   $host;
        $this->port =   $port;
        $this->username =   $username;
        $this->password =   $password;
        $this->sslUse   =   false;
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
     * @param string $endpoint
     * @return string
     */
    private function url(string $endpoint) : string
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
        $request->checkSSL($this->sslUse);
        if(isset($this->sslCA)) {

        }

        return $request;
    }
}