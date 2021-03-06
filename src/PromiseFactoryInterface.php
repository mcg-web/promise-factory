<?php

/*
 * This file is part of the PromiseFactory package.
 *
 * (c) McGWeb <http://github.com/mcg-web>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace McGWeb\PromiseFactory;

interface PromiseFactoryInterface
{
    /**
     * Creates a Promise
     *
     * @param $resolve
     * @param $reject
     * @param callable $canceller
     *
     * @return mixed a Promise
     */
    public static function create(&$resolve = null, &$reject = null, callable $canceller = null);

    /**
     * Creates a full filed Promise for a value if the value is not a promise.
     *
     * @param mixed $promiseOrValue
     *
     * @return mixed a full filed Promise
     */
    public static function createResolve($promiseOrValue = null);

    /**
     * Creates a rejected promise for a reason if the reason is not a promise. If
     * the provided reason is a promise, then it is returned as-is.
     *
     * @param mixed $promiseOrValue
     *
     * @return mixed a rejected promise
     */
    public static function createReject($promiseOrValue);

    /**
     * Given an array of promises, return a promise that is fulfilled when all the
     * items in the array are fulfilled.
     *
     * @param mixed $promisesOrValues Promises or values.
     *
     * @return mixed a Promise
     */
    public static function createAll($promisesOrValues);

    /**
     * Check if value is a promise
     *
     * @param mixed $value
     * @param bool $strict
     *
     * @return bool
     */
    public static function isPromise($value, $strict = false);

    /**
     * wait for Promise to complete
     * @param mixed $promise
     * @param bool  $unwrap
     *
     * @return mixed
     */
    public static function await($promise = null, $unwrap = false);

    /**
     * Cancel a promise
     *
     * @param $promise
     */
    public static function cancel($promise);
}
