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

namespace BitcoinRPC\Events;

use BitcoinRPC\Exception\EventsException;

/**
 * Class AbstractEventsLib
 * @package BitcoinRPC\Events
 */
abstract class AbstractEventsLib
{
    /** @var array */
    private $events;

    /**
     * AbstractEventsLib constructor.
     */
    public function __construct()
    {
        $this->events = [];
    }

    /**
     * @param string $tag
     * @param callable $func
     * @throws EventsException
     */
    protected function on(string $tag, callable $func): void
    {
        if (!preg_match('/^[\w\.]+$/', $tag)) {
            throw new EventsException('Invalid event tag');
        }

        $this->events[strtolower($tag)] = $func;
    }

    /**
     * @param string $tag
     * @return bool
     */
    protected function has(string $tag): bool
    {
        return array_key_exists(strtolower($tag), $this->events);
    }

    /**
     * @param $tag
     * @param array|null $args
     * @throws EventsException
     */
    protected function trigger($tag, ?array $args = null): void
    {
        $tag = strtolower($tag);
        $func = $this->events[$tag] ?? null;

        if (!is_callable($func)) {
            throw new EventsException(sprintf('Event "%s" is not registered/callable', $tag));
        }

        call_user_func_array($func, $args ?? []);
    }
}