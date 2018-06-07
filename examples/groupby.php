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

$selectBuilder = $connection->selectFrom("b_iblock_element_property");

$result = $selectBuilder
    ->addSelectField("IBLOCK_PROPERTY_ID")
    ->addSelectField("IBLOCK_PROPERTY_ID")
    ->count("ID", false, "COUNT")
    ->addGroupBy("IBLOCK_PROPERTY_ID")
    ->addGroupBy("IBLOCK_ELEMENT_ID")
    ->addOrderbyAlias("COUNT", false)
    ->setLimit(10)
    ->fetchAll();

echo '<pre><=== \$result ===></pre><pre>' . print_r($result, 1) . '</pre><pre><\=== \$result ===></pre>';
