<?php

/*
 * This file is part of the PromiseFactory package.
 *
 * (c) McGWeb <http://github.com/mcg-web>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace McGWeb\PromiseFactory\Factory;

use McGWeb\PromiseFactory\PromiseFactoryInterface;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;

class ReactPromiseFactory implements PromiseFactoryInterface
{
    /**
     * @inheritdoc
     *
     * @return Promise
     */
    public static function create(&$resolve = null, &$reject = null, callable $canceller = null)
    {
        $deferred = new Deferred($canceller);

        $reject = [$deferred, 'reject'];
        $resolve = [$deferred, 'resolve'];

        return $deferred->promise();
    }

    /**
     * @inheritdoc
     *
     * @return FulfilledPromise a full filed Promise
     */
    public static function createResolve($promiseOrValue)
    {
        return \React\Promise\resolve($promiseOrValue);
    }

    /**
     * @inheritdoc
     *
     * @return RejectedPromise a rejected promise
     */
    public static function createReject($promiseOrValue)
    {
        return \React\Promise\reject($promiseOrValue);
    }

    /**
     * @inheritdoc
     *
     * @return Promise
     */
    public static function createAll($promisesOrValues)
    {
        return \React\Promise\all($promisesOrValues);
    }

    /**
     * @inheritdoc
     */
    public static function isPromise($value, $strict = false)
    {
        $isStrictPromise = $value instanceof PromiseInterface;

        if ($strict) {
            return $isStrictPromise;
        }

        return $isStrictPromise || is_callable([$value, 'then']);
    }

    /**
     * @inheritdoc
     */
    public static function await($promise = null, $unwrap = false)
    {
        if (null === $promise) {
            return null;
        }

        $resolvedValue = null;
        $exception = null;
        if (!static::isPromise($promise)) {
            throw new \InvalidArgumentException(sprintf('The "%s" method must be called with a Promise ("then" method).', __METHOD__));
        }
        $promise->then(function ($values) use (&$resolvedValue) {
            $resolvedValue = $values;
        }, function ($reason) use (&$exception) {
            $exception = $reason;
        });
        if ($exception instanceof \Exception) {
            if (!$unwrap) {
                return $exception;
            }
            throw $exception;
        }

        return $resolvedValue;
    }
}
