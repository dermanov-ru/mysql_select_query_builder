<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 31.05.2018
 * Time: 17:27
 *
 *
 */


namespace Core\Db;


use Core\Db\Query\SelectQueryBuilder;

class DbConnection
{
    protected $connection;
    
    /**
     * DbConnection constructor.
     *
     * @param $connection
     */
    public function __construct( $user, $pass, $db, $host = "localhost", $charset = "utf8" )
    {
    
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $opt = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $this->connection = new \PDO($dsn, $user, $pass, $opt);
    }
    
    public function insert($table, $object){
        $converter = new \Core\Db\ModelConverter();
        $source = $converter->convertToDbArray($object);
    
        $fields_str = $this->implodeAndEscape( array_keys( $source ), "`" );
        $values_str = $this->implodePlaceholders(array_keys($source));
        $sql = "INSERT INTO `$table` ($fields_str) VALUES ($values_str)";
        
        $stm = $this->connection->prepare($sql);
        $res = $stm->execute($source);
        
        //if (!$res)
        //    throw new \Exception ( "" );
        
        $insertId = $this->connection->lastInsertId();
        
        return $insertId;
    }
    
    public function selectFrom( $table )
    {
        $result = new SelectQueryBuilder($table);
        $result->setConnection($this);
        
        return $result;
    }
    
    public function query( $sql )
    {
        // just for simple view final sql and elapsed time
        // TODO remove from here to debug zone
        echo '<pre><=== \$sql ===></pre><pre>' . print_r($sql, 1) . '</pre><pre><\=== \$sql ===></pre>';
    
        $timer_start = microtime(TRUE);
        
        $result = $this->connection->query($sql)->fetchAll();
    
        $timer_end = microtime(TRUE);
        $queryTime = round($timer_end - $timer_start, 6);
        echo '<pre><=== \$queryTime ===></pre><pre>' . print_r($queryTime, 1) . '</pre><pre><\=== \$queryTime ===></pre>';
        
        return $result;
    }
}