# With

This function simply mocks Python's [with statement](http://docs.python.org/release/2.5.3/ref/with.html) for PHP.

![Tests](https://github.com/gnikyt/with/workflows/Package%20Test/badge.svg?branch=master)
[![codecov](https://codecov.io/gh/gnikyt/with/branch/master/graph/badge.svg?token=qqUuLItqJj)](https://codecov.io/gh/gnikyt/with)
[![License](https://poser.pugx.org/gnikyt/with/license)](https://packagist.org/packages/gnikyt/with)

## Installation

The recommended way to install is [through composer](http://packagist.org).

    composer require gnikyt/with

## Usage

The with statement is used to wrap the execution of code with methods defined by a object. This allows common try...except...finally usage patterns to be encapsulated for convenient reuse.

A with statement is defined as followed: `with([object], [callback]);`.

The executation of a with statement is done as followed:

1. The `[object]`'s `__enter()` method is invoked
2. The return value from `__enter()` is assigned to the first argument of the `[callback]`
> Note: The with statement guarantees that if the `__enter()` method returns without an error, then `__exit()` will always be called. Thus, if an error occurs during `__enter()`, it will be treated the same as an error occurring within the `[callback]` would be. See step 4 below
3. The `[callback]` is executed
4. The `[object]`'s `__exit()` method is invoked. If an exception caused the `[callback]` to be exited, the return value from `__enter()` and the `[callback]`'s exception are passed as arguments to `__exit()`. Otherwise, only the return value from `__enter()` is passed and the exception is set to null
> If the `[callback]` was exited due to an exception, and the return value from the `__exit()` method was false, the exception is rethrown. If the return value was true, the exception is suppressed, and execution continues with the statement following the with statement.

Here is a sample object and a with-statement:

```php
class Foo
{
    private $db;

    //.. other code ..//

    public function __enter()
    {
        $db = new PDO(
                sprintf('%s:host=%s;dbname=%s', $this->config['driver'], $this->config['host'], $this->config['db']),
                $this->config['user'],
                $this->config['pass']
        );

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $db;
    }

    public function __exit($db, $error = null)
    {
        if ($error instanceof Exception) {
            $this->log('We had en error; Rolling back.');

            $db->rollback();
        } else {
            $this->log('Code ran fine. Committed.');

            $db->commit();
        }

        return true;
    }
}

$foo = new Foo;
Gnikyt\with($foo, function($db) {
    $db->beginTransaction();

    $foo = 'osiset';
    $sql = $db->prepare("INSERT INTO non_existant_table SET name = :foo");
    $sql->bindParam('foo', $foo, PDO::PARAM_STR);
    $sql->execute();
});
```

You're also free to implement the interface provided to ensure you're classes are compatible:

```php
use Gnikyt\Withable;

class Foo implements Withable
{
    // ...
}
```

The above example is processed as follows:

+ `with` will call `$foo->__enter()`
+ `$foo->__enter()` will setup the database, and return the PDO object
+ `with` will now pass the PDO object to the callback as `$db` for use within the closure
+ `with` now executes the callback closure
+ The callback will throw an exception because the table does not exist
+ `with` now calls `$foo->__exit()` and passed the `$db` object and the exception from the callback to it
+ `$foo->__exit()` now checks for a exception and rollsback the changes. It returns `true` to suppress re-throwing the
exception
+ `with` now checks the return from `$foo->__exit()`, it sees it returns a `true` value, and does not re-throw the
exception

## Requirements

- [PHP](http://php.net) >= 8

## Usage

See `examples/` for some basic usage code or this README.