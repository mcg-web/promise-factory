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

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use McGWeb\PromiseFactory\PromiseFactoryInterface;

class WebonyxGraphQLSyncPromiseFactory implements PromiseFactoryInterface
{
    /** @var callable[] */
    private static $cancellers = [];

    /**
     * @var SyncPromiseAdapter
     */
    private static $webonyxPromiseAdapter;

    /**
     * @return SyncPromiseAdapter
     */
    protected static function getWebonyxPromiseAdapter()
    {
        if (null === self::$webonyxPromiseAdapter) {
            static::setWebonyxPromiseAdapter(new SyncPromiseAdapter());
        }

        return self::$webonyxPromiseAdapter;
    }

    /**
     * @param PromiseAdapter $webonyxPromiseAdapter
     */
    protected static function setWebonyxPromiseAdapter($webonyxPromiseAdapter)
    {
        self::$webonyxPromiseAdapter = $webonyxPromiseAdapter;
    }

    /**
     * @inheritdoc
     */
    public static function create(&$resolve = null, &$reject = null, callable $canceller = null)
    {
        $promise = static::getWebonyxPromiseAdapter()->createPromise(function(callable $promiseResolver, callable $promiseReject = null) use (&$resolve, &$reject, &$canceller){
            $resolve = $promiseResolver;
            $reject = $promiseReject;
        });
        self::$cancellers[spl_object_hash($promise)] = $canceller;

        return $promise;
    }

    /**
     * @inheritdoc
     */
    public static function createResolve($promiseOrValue = null)
    {
        return static::getWebonyxPromiseAdapter()->createResolvedPromise($promiseOrValue);
    }

    /**
     * @inheritdoc
     */
    public static function createReject($reason)
    {
        return static::getWebonyxPromiseAdapter()->createRejectedPromise($reason);
    }

    /**
     * @inheritdoc
     */
    public static function createAll($promisesOrValues)
    {
        return static::getWebonyxPromiseAdapter()->createPromiseAll($promisesOrValues);
    }

    /**
     * @inheritdoc
     */
    public static function isPromise($value, $strict = false)
    {
        $isStrictPromise = $value instanceof Promise;
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
        $promiseAdapter = self::getWebonyxPromiseAdapter();

        $resolvedValue = null;
        $exception = null;
        if (!static::isPromise($promise)) {
            throw new \InvalidArgumentException(sprintf('The "%s" method must be called with a Promise ("then" method).', __METHOD__));
        }

        try {
            $resolvedValue = $promiseAdapter->wait($promise);
        } catch (\Exception $reason) {
            $exception = $reason;
        }
        if ($exception instanceof \Exception) {
            if (!$unwrap) {
                return $exception;
            }
            throw $exception;
        }

        return $resolvedValue;
    }

    /**
     * @inheritdoc
     */
    public static function cancel($promise)
    {
        $hash = spl_object_hash($promise);
        if (!static::isPromise($promise) || !isset(self::$cancellers[$hash])) {
            throw new \InvalidArgumentException(sprintf('The "%s" method must be called with a compatible Promise.', __METHOD__));
        }
        $canceller = self::$cancellers[$hash];
        $adoptedPromise = $promise->adoptedPromise;
        try {
            $canceller([$adoptedPromise, 'resolve'], [$adoptedPromise, 'reject']);
        } catch (\Exception $reason) {
            $adoptedPromise->reject($reason);
        }
    }
}
