<?php

namespace Gnikyt;

use Exception;
use PHPUnit\Framework\TestCase;
use StdClass;

class WithTest extends TestCase
{
    /**
     * This will test to make sure arg1 of with() is callable.
     */
    public function testItShouldRejectForNoObject()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('"something" must be a callable object');

        $something = 'something';
        with($something, function () {
            $this->fail('callable must not be called');
        });
    }

    /**
     * This will test to make sure arg1 of with() has a public __enter() method.
     */
    public function testItShouldRejectObjectsWithoutEnterMethod()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Class "stdClass" must have a public __enter() method.');

        $emptyObject = new StdClass();
        with($emptyObject, function () {
            $this->fail('callable must not be called');
        });
    }

    /**
     * This will test to make sure arg1 of with() has a public __exit() method.
     */
    public function testItShouldRejectObjectsWithoutExitMethod()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Class "Gnikyt\EnterableStub" must have a public __exit() method.');

        $enterObject = new EnterableStub();
        with($enterObject, function () {
            $this->fail('callable must not be called');
        });
    }

    /**
     * This will test to make sure with() successfully runs on a object having __enter() and __exit().
     */
    public function testItShouldCallHookableMethods()
    {
        $callableMock = $this->createMock('Gnikyt\CallableStub');
        $callableMock->expects($this->once())
                     ->method('__invoke');

        $withObj = $this->createMock('Gnikyt\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter');
        $withObj->expects($this->once())
                ->method('__exit')
                ->will($this->returnValue(true));

        with($withObj, $callableMock);
    }

    /**
     * This will test to ensure __enter() passes its return to the callable.
     */
    public function testItShouldPassEnterReturnValueToCallable()
    {
        $callableMock = $this->createMock('Gnikyt\CallableStub');
        $callableMock->expects($this->once())
                     ->method('__invoke')
                     ->with(42);

        $withObj = $this->createMock('Gnikyt\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter')
                ->will($this->returnValue(42));
        $withObj->expects($this->once())
                ->method('__exit')
                ->will($this->returnValue(true));

        with($withObj, $callableMock);
    }

    /**
     * This will test to ensure __exit() gets a return value from __enter() via the callable.
     */
    public function testItShouldPassExitGetReturnValueFromEnter()
    {
        $callableMock = $this->createMock('Gnikyt\CallableStub');
        $callableMock->expects($this->once())
                     ->method('__invoke')
                     ->with(42);

        $withObj = $this->createMock('Gnikyt\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter')
                ->will($this->returnValue(42));
        $withObj->expects($this->once())
                ->method('__exit')
                ->with(42, null)
                ->will($this->returnValue(true));

        with($withObj, $callableMock);
    }

    /**
     * This will test to make sure the exception will be re-thrown if __exit() returns as false.
     */
    public function testItShouldPassExitGetReturnValueFromEnterAndMakeExceptionNotSuppressed()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('__exit() did not surpress me.');

        $e = new Exception('__exit() did not surpress me.');

        $callableMock = $this->createMock('Gnikyt\CallableStub');
        $callableMock->expects($this->once())
                     ->method('__invoke')
                     ->with(42)
                     ->will($this->throwException($e));

        $withObj = $this->createMock('Gnikyt\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter')
                ->will($this->returnValue(42));
        $withObj->expects($this->once())
                ->method('__exit')
                ->with(42, $e)
                ->will($this->returnValue(false));

        with($withObj, $callableMock);
    }

    /**
     * This will test to make sure the exception will NOT be re-thrown if __exit() returns as true.
     */
    public function testItShouldPassExitGetReturnValueFromEnterAndMakeExceptionAndSuppress()
    {
        $e = new Exception();

        $callableMock = $this->createMock('Gnikyt\CallableStub');
        $callableMock->expects($this->once())
                     ->method('__invoke')
                     ->with(42)
                     ->will($this->throwException($e));

        $withObj = $this->createMock('Gnikyt\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter')
                ->will($this->returnValue(42));
        $withObj->expects($this->once())
                ->method('__exit')
                ->with(42, $e)
                ->will($this->returnValue(true));

        with($withObj, $callableMock);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage __exit() method must return a boolean.
     *
     * This will test to make sure a boolean is returned from __exit() method.
     */
    public function testItShouldCheckForBooleanTypeReturnedFromExitAndThrowException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('__exit() method must return a boolean.');

        $e = new Exception();

        $callableMock = $this->createMock('Gnikyt\CallableStub');
        $callableMock->expects($this->once())
                     ->method('__invoke')
                     ->with(42)
                     ->will($this->throwException($e));

        $withObj = $this->createMock('Gnikyt\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter')
                ->will($this->returnValue(42));
        $withObj->expects($this->once())
                ->method('__exit')
                ->with(42, $e)
                ->will($this->returnValue(null));

        with($withObj, $callableMock);
    }

    /**
     * This will test to ensure __enter() will throw an exception, skip the callable
     * but still call __exit() and pass that exception to it.
     */
    public function testItShouldExceptionOnEnterAndSkipCallableButStillRunExit()
    {
        $e = new Exception();

        $callableMock = $this->createMock('Gnikyt\CallableStub');
        $callableMock->expects($this->never())
                     ->method('__invoke');

        $withObj = $this->createMock('Gnikyt\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter')
                ->will($this->throwException($e));
        $withObj->expects($this->once())
                ->method('__exit')
                ->with(null, $e)
                ->will($this->returnValue(true));

        with($withObj, $callableMock);
    }

    /**
     * This will test to ensure __enter() will throw an exception, skip the callable
     * but still call __exit() and pass that exception to it + rethrow it.
     */
    public function testItShouldExceptionOnEnterAndSkipCallableButStillRunExitAndReThrow()
    {
        $this->expectException(Exception::class);

        $e = new Exception();

        $callableMock = $this->createMock('Gnikyt\CallableStub');
        $callableMock->expects($this->never())
                     ->method('__invoke');

        $withObj = $this->createMock('Gnikyt\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter')
                ->will($this->throwException($e));
        $withObj->expects($this->once())
                ->method('__exit')
                ->with(null, $e)
                ->will($this->returnValue(false));

        with($withObj, $callableMock);
    }

    /**
     * Complete test.
     */
    public function testComplete()
    {
        $e = new Exception();

        $callableMock = $this->createMock('Gnikyt\CallableStub');
        $callableMock->expects($this->once())
                     ->method('__invoke')
                     ->with(42)
                     ->will($this->throwException($e));

        $withObj = $this->createMock('Gnikyt\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter')
                ->will($this->returnValue(42));
        $withObj->expects($this->once())
                ->method('__exit')
                ->with(42, $e)
                ->will($this->returnValue(true));

        with($withObj, $callableMock);
    }

    /**
     * Test implementation.
     */
    public function testItShouldBeAbleToImplementInterface()
    {
        $e = new Exception();

        $callableMock = $this->createMock('Gnikyt\CallableStub');
        $callableMock->expects($this->once())
                     ->method('__invoke')
                     ->with(42)
                     ->will($this->throwException($e));

        $withObj = $this->createMock('Gnikyt\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter')
                ->will($this->returnValue(42));
        $withObj->expects($this->once())
                ->method('__exit')
                ->with(42, $e)
                ->will($this->returnValue(true));

        with($withObj, $callableMock);
    }
}

class EnterableStub
{
    public function __enter()
    {
    }
}

class CallableStub
{
    public function __invoke()
    {
    }
}

class WithObjectStub
{
    public function __enter()
    {
    }

    public function __exit($enter, $error)
    {
    }
}

class InterfacedStub implements Withable
{
    public function __enter()
    {
    }

    public function __exit($enter, $error)
    {
    }
}
