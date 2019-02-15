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

/**
 * Class SignedRawTransaction
 * @package BitcoinRPC\Response
 */
class SignedRawTransaction implements BitcoindResponseInterface
{
    /** @var string */
    public $hex;
    /** @var bool */
    public $complete;

    /**
     * SignedRawTransaction constructor.
     * @param array $obj
     */
    public function __construct(array $obj)
    {
        $this->hex = $obj["hex"];
        $this->complete = $obj["complete"];
    }
}