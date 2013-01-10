<?php

namespace TylerKing;

class WithTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage "something" must be a callable object.
     */
    function itShouldRejectForNoObject()
    {
        $something = 'something';
        with($something, function () {
            $this->fail('callable must not be called');
        });
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Class "stdClass" must have a public __enter() method.
     */
    function itShouldRejectObjectsWithoutEnterMethod()
    {
        $emptyObject = new \StdClass();
        with($emptyObject, function () {
            $this->fail('callable must not be called');
        });
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Class "TylerKing\EnterableStub" must have a public __exit() method.
     */
    function itShouldRejectObjectsWithoutExitMethod()
    {
        $enterObject = new EnterableStub();
        with($enterObject, function () {
            $this->fail('callable must not be called');
        });
    }

    /**
     * @test
     */
    function itShouldCallHookableMethods()
    {
        $callableMock = $this->getMock('TylerKing\CallableStub');
        $callableMock->expects($this->once())
                     ->method('__invoke');

        $withObj = $this->getMock('TylerKing\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter');
        $withObj->expects($this->once())
                ->method('__exit');

        with($withObj, $callableMock);
   }

    /**
     * @test
     */
    function itShouldPassEnterReturnValueToCallable()
    {
        $callableMock = $this->getMock('TylerKing\CallableStub');
        $callableMock->expects($this->once())
                     ->method('__invoke')
                     ->with(42);

        $withObj = $this->getMock('TylerKing\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter')
                ->will($this->returnValue(42));

        with($withObj, $callableMock);
    }

    /**
     * @test
     */
    function itShouldPassExitGetReturnValueFromEnter()
    {
        $callableMock = $this->getMock('TylerKing\CallableStub');
        $callableMock->expects($this->once())
                     ->method('__invoke')
                     ->with(42);

        $withObj = $this->getMock('TylerKing\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter')
                ->will($this->returnValue(42));
        $withObj->expects($this->once())
                ->method('__exit')
                ->with(42, null);

        with($withObj, $callableMock); 
    }

    /**
     * @test
     */
    function itShouldPassExitGetReturnValueFromEnterAndMakeException()
    {
        $e = new \Exception;

        $callableMock = $this->getMock('TylerKing\CallableStub');
        $callableMock->expects($this->once())
                     ->method('__invoke')
                     ->with(42)
                     ->will($this->throwException($e));

        $withObj = $this->getMock('TylerKing\WithObjectStub');
        $withObj->expects($this->once())
                ->method('__enter')
                ->will($this->returnValue(42));
        $withObj->expects($this->once())
                ->method('__exit')
                ->with(42, $e);

        with($withObj, $callableMock); 
    }
}

class EnterableStub
{
    public function __enter() { }
}

class CallableStub
{
    public function __invoke() { }
}

class WithObjectStub
{
    public function __enter() { }

    public function __exit() { }
}