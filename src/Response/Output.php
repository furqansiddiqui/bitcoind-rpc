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
 * Class Output
 * @package BitcoinRPC\Response
 * @property string $txid
 * @property int $vout
 * @property null|string $address
 * @property null|string $account
 * @property string $scriptPubKey
 * @property null|string $redeemScript
 * @property int|float $amount
 * @property int $confirmations
 * @property bool $spendable
 * @property bool $solvable
 *
 */
class Output
{
    /**
     * Output constructor.
     * @param $obj
     * @throws ResponseObjectException
     */
    public function __construct($obj)
    {
        if (!is_array($obj)) {
            throw new ResponseObjectException(
                sprintf('TransactionOutput object requires first argument Array, got "%s"', gettype($obj))
            );
        }

        foreach ($obj as $key => $value) {
            $this->$key = $value;
        }
    }
}