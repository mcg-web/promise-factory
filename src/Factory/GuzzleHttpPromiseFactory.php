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

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use McGWeb\PromiseFactory\PromiseFactoryInterface;

class GuzzleHttpPromiseFactory implements PromiseFactoryInterface
{
    /**
     * @inheritdoc
     *
     * @return Promise
     */
    public static function create(&$resolve = null, &$reject = null, callable $canceller = null)
    {
        $promise = new Promise(null, $canceller);

        $reject = [$promise, 'reject'];
        $resolve = [$promise, 'resolve'];

        return $promise;
    }

    /**
     * @inheritdoc
     *
     * @return FulfilledPromise a full filed Promise
     */
    public static function createResolve($promiseOrValue)
    {
        $promise = \GuzzleHttp\Promise\promise_for($promiseOrValue);

        return $promise;
    }

    /**
     * @inheritdoc
     *
     * @return RejectedPromise a rejected promise
     */
    public static function createReject($promiseOrValue)
    {
        $promise = \GuzzleHttp\Promise\rejection_for($promiseOrValue);

        return $promise;
    }

    /**
     * @inheritdoc
     *
     * @return Promise
     */
    public static function createAll($promisesOrValues)
    {
        $promise = \GuzzleHttp\Promise\all($promisesOrValues);

        return $promise;
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
        $resolvedValue = null;

        if (null !== $promise) {
            $exception = null;
            if (!static::isPromise($promise)) {
                throw new \InvalidArgumentException(sprintf('The "%s" method must be called with a Promise ("then" method).', __METHOD__));
            }
            /** @var Promise $promise */
            $promise->then(function ($values) use (&$resolvedValue) {
                $resolvedValue = $values;
            }, function ($reason) use (&$exception) {
                $exception = $reason;
            });
            \GuzzleHttp\Promise\queue()->run();

            if ($exception instanceof \Exception) {
                if (!$unwrap) {
                    return $exception;
                }
                throw $exception;
            }
        } else {
            \GuzzleHttp\Promise\queue()->run();
        }

        return $resolvedValue;
    }
}
