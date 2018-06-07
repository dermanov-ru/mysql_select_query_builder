<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 06.06.2018
 * Time: 14:59
 *
 *
 */


require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/init.php";

$connection = \Core\Db\DbManager::getConnection();

$selectBuilder = $connection->selectFrom("b_iblock_element");

$result = $selectBuilder
    ->min("sort")
    ->max("sort")
    ->avg("sort")
    ->count("sort")
    ->fetchAll();

echo '<pre><=== \$result ===></pre><pre>' . print_r($result, 1) . '</pre><pre><\=== \$result ===></pre>';


$selectBuilder = $connection->selectFrom("b_iblock_element");

$result = $selectBuilder
    ->min("sort", true)
    ->max("sort", true)
    ->avg("sort", true)
    ->count("sort", true)
    ->fetchAll();

echo '<pre><=== \$result ===></pre><pre>' . print_r($result, 1) . '</pre><pre><\=== \$result ===></pre>';
