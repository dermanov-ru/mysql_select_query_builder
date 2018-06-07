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

$selectBuilder = $connection->selectFrom("b_iblock");

$result = $selectBuilder
    ->addSelectField("ID")
    ->addSelectField("sort")
    ->addSelectField("NAME")
    ->addSelectField("CODE")
    ->addOrderbyField("sort", false)
    ->addOrderbyField("name")
    ->fetchAll();

echo '<pre><=== \$result ===></pre><pre>' . print_r($result, 1) . '</pre><pre><\=== \$result ===></pre>';
