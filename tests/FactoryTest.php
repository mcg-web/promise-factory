<?php

/*
 * This file is part of the PromiseFactory package.
 *
 * (c) McGWeb <http://github.com/mcg-web>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace McGWeb\PromiseFactory\Tests;

use McGWeb\PromiseFactory\Factory\GuzzleHttpPromiseFactory;
use McGWeb\PromiseFactory\Factory\ReactPromiseFactory;
use McGWeb\PromiseFactory\Factory\WebonyxGraphQLSyncPromiseFactory;
use McGWeb\PromiseFactory\PromiseFactoryInterface;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    const CONTEXT_WEBONYX_GRAPHQL_SYNC ='webonyxgraphqlsync';
    const CONTEXT_GUZZLE = 'guzzle';
    const CONTEXT_REACT = 'react';

    /**
     * @dataProvider factoryDataProvider
     * @param string $promiseClass
     * @param PromiseFactoryInterface $factory
     * @param string $context
     */
    public function testCreate(PromiseFactoryInterface $factory, $context, $promiseClass)
    {
        $promise = $factory->create($resolve, $reject);

        $this->assertInstanceOf($promiseClass, $promise, $context);
        $this->assertTrue(is_callable($resolve), $context);
        $this->assertTrue(is_callable($reject), $context);
    }

    /**
     * @dataProvider factoryDataProvider
     * @param PromiseFactoryInterface $factory
     * @param $message
     */
    public function testResolveCreatedPromise(PromiseFactoryInterface $factory, $message)
    {
        $promise = $factory->create($resolve, $reject);
        $expectResolvedValue = 'Resolve value';
        $resolve($expectResolvedValue);
        $resolvedValue = $factory->await($promise);

        $this->assertEquals($expectResolvedValue, $resolvedValue, $message);
    }

    /**
     * @dataProvider factoryDataProvider
     * @param PromiseFactoryInterface $factory
     * @param string $context
     */
    public function testRejectCreatedPromise(PromiseFactoryInterface $factory, $context)
    {
        $promise = $factory->create($resolve, $reject);

        $expectRejectionReason = new \Exception('Error!');
        $reject($expectRejectionReason);

        $rejectionReason = $factory->await($promise, false);
        $this->assertEquals($expectRejectionReason, $rejectionReason, $context);
    }

    /**
     * @dataProvider factoryDataProvider
     * @param PromiseFactoryInterface $factory
     * @param string $context
     * @param string $promiseClass
     */
    public function testCreateAll(PromiseFactoryInterface $factory, $context, $promiseClass)
    {
        $values = ['A', 'B', 'C'];
        $promise = $factory->createAll($values);
        $this->assertInstanceOf($promiseClass, $promise, $context);

        $resolvedValue = $factory->await($promise);

        $this->assertEquals($values, $resolvedValue, $context);
    }

    /**
     * @dataProvider factoryDataProvider
     * @param PromiseFactoryInterface $factory
     * @param string $context
     * @param string $promiseClass
     */
    public function testCreateResolve(PromiseFactoryInterface $factory, $context, $promiseClass)
    {
        $value = 'resolved!';
        $promise = $factory->createResolve($value);
        $this->assertInstanceOf($promiseClass, $promise, $context);

        $resolvedValue = $factory->await($promise);
        $this->assertEquals($value, $resolvedValue, $context);
    }

    /**
     * @dataProvider factoryDataProvider
     * @param PromiseFactoryInterface $factory
     * @param string $context
     * @param string $promiseClass
     */
    public function testCreatedReject(PromiseFactoryInterface $factory, $context, $promiseClass)
    {
        $expectRejectionReason = new \Exception('Error!');
        $promise = $factory->createReject($expectRejectionReason);
        $this->assertInstanceOf($promiseClass, $promise, $context);

        $rejectionReason = $factory->await($promise, false);
        $this->assertEquals($expectRejectionReason, $rejectionReason, $context);
    }

    /**
     * @dataProvider factoryDataProvider
     * @param PromiseFactoryInterface $factory
     * @param string $context
     */
    public function testIsPromise(PromiseFactoryInterface $factory, $context)
    {
        $promise = $factory->create();

        $this->assertTrue($factory->isPromise($promise, true), $context);
        $this->assertFalse($factory->isPromise([]), $context);
        $this->assertFalse($factory->isPromise(new \stdClass()), $context);
    }

    /**
     * @dataProvider factoryDataProvider
     * @param PromiseFactoryInterface $factory
     * @param string $context
     */
    public function testAwaitWithoutPromise(PromiseFactoryInterface $factory, $context)
    {
        if ($context === self::CONTEXT_WEBONYX_GRAPHQL_SYNC) {
            $this->markTestSkipped('This feature is not supported for the moment.');
        }
        $expected = 'expected value';
        $promise = $factory->createResolve($expected);
        $actual = null;

        $promise->then(function ($value) use (&$actual) {
            $actual = $value;
        });

        $factory->await();

        $this->assertEquals($expected, $actual, $context);
    }

    /**
     * @dataProvider factoryDataProvider
     * @param PromiseFactoryInterface $factory
     *
     * @expectedException \Exception
     * @expectedExceptionMessage error!
     */
    public function testAwaitWithUnwrap(PromiseFactoryInterface $factory)
    {
        $expected = new \Exception('error!');
        $promise = $factory->createReject($expected);

        $factory->await($promise, true);
    }

    /**
     * @dataProvider factoryDataProvider
     * @param PromiseFactoryInterface $factory
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ::await" method must be called with a Promise ("then" method).
     */
    public function testAwaitWithInvalidPromise(PromiseFactoryInterface $factory)
    {
        $factory->await(new \stdClass(), true);
    }

    /**
     * @dataProvider factoryDataProvider
     * @param PromiseFactoryInterface $factory
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Cancel promise!
     */
    public function testCancel(PromiseFactoryInterface $factory)
    {
        $promise = $factory->create($resolve, $reject, function () {
            throw new \Exception('Cancel promise!');
        });

        $factory->cancel($promise);
        $factory->await($promise, true);
    }

    /**
     * @dataProvider factoryDataProvider
     * @param PromiseFactoryInterface $factory
     *
     * @expectedException \Exception
     * @expectedExceptionMessage ::cancel" method must be called with a compatible Promise.
     */
    public function testCancelInvalidPromise(PromiseFactoryInterface $factory)
    {
        $factory->create($resolve, $reject, function () {
            throw new \Exception('Cancel will never be called!');
        });

        $factory->cancel(new \stdClass());
    }

    public function factoryDataProvider()
    {
        return [
            [new WebonyxGraphQLSyncPromiseFactory(), self::CONTEXT_WEBONYX_GRAPHQL_SYNC, 'GraphQL\\Executor\\Promise\\Promise'],
            [new GuzzleHttpPromiseFactory(), self::CONTEXT_GUZZLE, 'GuzzleHttp\\Promise\\PromiseInterface'],
            [new ReactPromiseFactory(), self::CONTEXT_REACT, 'React\\Promise\\PromiseInterface'],
        ];
    }
}
