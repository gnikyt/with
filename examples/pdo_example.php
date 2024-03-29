<?php

require __DIR__.'/../vendor/autoload.php';

use Gnikyt\Withable;

/**
 * In this example, with() will call Install()'s __enter method, which setups the PDO instance.
 * The PDO instance is then returned, where with() injects it into the callable function.
 * The callable function can then use PDO to run a transaction.
 * In this example, it will fail the transaction, where Install()'s __exit method automatically takes care of the rollback.
 **/
class Install // implements Withable
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = array_merge([
            'driver' => 'mysql',
            'host'   => 'localhost',
            'user'   => null,
            'pass'   => null,
            'db'     => null,
        ], $config);

        return $this;
    }

    protected function log($message)
    {
        echo $message.PHP_EOL;
    }

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

    public function __exit($object, $error = null)
    {
        if ($error instanceof Exception) {
            $this->log('We had en error; Rolling back.');

            $object->rollback();
        } else {
            $this->log('Code ran fine. Committed.');

            $object->commit();
        }

        // surpresses any exceptions.
        return true;
    }
}

$test = new Install(['user' => 'root', 'pass' => 'root', 'db' => 'test']);
Gnikyt\with($test, function ($pdo) {
    $pdo->beginTransaction();

    $id = 2;
    $sql = $pdo->prepare('INSERT INTO non_existant_table SET id = :id');
    $sql->bindParam('id', $id, PDO::PARAM_INT);
    $sql->execute();
});
