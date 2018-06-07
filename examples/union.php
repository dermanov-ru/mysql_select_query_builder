<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 03.06.2018
 * Time: 19:31
 *
 *
 */


require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/init.php";

$connection = \Core\Db\DbManager::getConnection();

$selectBuilder = $connection->selectFrom("b_iblock_element");

$catalogIbockId = 2;

$result = $selectBuilder
    ->addSelectField("SORT")
    ->whereEqual("iblock_id", $catalogIbockId)
    ->whereEqual("ACTIVE", "Y")
    ->setLimit(1)
    ->union(
        $selectBuilder->unionQuery()
            ->whereEqual("iblock_id", $catalogIbockId)
            ->whereEqual("ACTIVE", "N")
            ->setLimit(2)
    )
    ->union(
        $selectBuilder->unionQuery()
            ->whereEqual("iblock_id", $catalogIbockId)
            ->whereEqual("ACTIVE", "N")
            ->whereEqual("SORT", 500)
            ->setLimit(3)
    )
    ->fetchAll();

echo '<pre><=== \$result ===></pre><pre>' . print_r($result, 1) . '</pre><pre><\=== \$result ===></pre>';